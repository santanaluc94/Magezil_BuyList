<?php

namespace Magezil\BuyList\Controller\Lists;

use Magezil\BuyList\Controller\Lists\GeneralBuyList;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\SessionFactory;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\CollectionFactory as BuyListItemCollectionFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\Collection as BuyListItemCollection;
use Magento\Quote\Model\Quote;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderPost extends GeneralBuyList implements ActionInterface
{
    protected RequestInterface $request;
    protected BuyListRepositoryInterface $buyListRepository;
    protected BuyListItemCollectionFactory $buyListItemCollectionFactory;
    protected RedirectInterface $redirect;
    protected CheckoutSession $checkoutSession;
    protected CartRepositoryInterface $cartRepository;
    protected ProductRepositoryInterface $productRepository;

    public function __construct(
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        RequestInterface $request,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemCollectionFactory $buyListItemCollectionFactory,
        RedirectInterface $redirect,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $redirectResultFactory,
            $customerSession,
            $buyListSettings,
            $messageManager
        );
        $this->request = $request;
        $this->buyListRepository = $buyListRepository;
        $this->buyListItemCollectionFactory = $buyListItemCollectionFactory;
        $this->redirect = $redirect;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    public function execute(): ResultInterface
    {
        $redirectResult = $this->redirectResultFactory->create();

        try {
            $this->isAllowed();

            $buyListId = (int) $this->request->getParam('id');

            if (!$buyListId) {
                throw new NoSuchEntityException(__('You must pass a valid ID to access this list.'));
            }

            $buyList = $this->buyListRepository->getById($buyListId);
            $customerId = (int) $this->getCustomerSession()
                ->getCustomer()
                ->getId();

            if (!$buyList->getId() || $customerId !== $buyList->getCustomerId() || !$buyList->getIsActive()) {
                throw new NoSuchEntityException(__('You don\'t have access to this list.'));
            }

            $buyListItemCollection = $this->buyListItemCollectionFactory->create()
                ->addFieldToFilter('buy_list_id', $buyListId);

            if (!$buyListItemCollection->getSize()) {
                throw new NoSuchEntityException(__('You don\'t have any items in this list.'));
            }

            $quote = $this->addProductsToCurrentQuote($buyListItemCollection);
            $quote->collectTotals();
            $this->cartRepository->save($quote);

            $this->messageManager->addSuccessMessage(__(
                'You have successfully added the items from buy list %1 to your cart.',
                $buyListId
            ));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $redirectResult->setRefererUrl($this->redirect->getRefererUrl());
        }

        $redirectResult->setPath('checkout/cart');
        return $redirectResult;
    }

    protected function addProductsToCurrentQuote(
        BuyListItemCollection $buyListItemCollection
    ): Quote {
        $quote = $this->checkoutSession->create()->getQuote();

        foreach ($buyListItemCollection->getItems() as $buyListItem) {
            $product = $this->productRepository->getById($buyListItem->getProductId());

            if (!$product->getId()) {
                $this->messageManager->addWarningMessage(__(
                    'Product with ID %1 was not added to cart because it was not found.',
                    $buyListItem->getProductId()
                ));
            }

            $quote->addProduct(
                $product,
                $buyListItem->getQty()
            );
        }

        return $quote;
    }
}
