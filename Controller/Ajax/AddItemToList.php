<?php

namespace Magezil\BuyList\Controller\Ajax;

use Magezil\BuyList\Controller\Lists\GeneralBuyList;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\SessionFactory;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magezil\BuyList\Model\BuyListItemFactory;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;

class AddItemToList extends GeneralBuyList implements ActionInterface
{
    protected JsonFactory $resultJsonFactory;
    protected RequestInterface $request;
    protected StoreManagerInterface $storeManager;
    protected BuyListRepositoryInterface $buyListRepository;
    protected ProductRepositoryInterface $productRepository;
    protected BuyListItemFactory $buyListItemFactory;
    protected BuyListItemRepositoryInterface $buyListItemRepository;

    public function __construct(
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        BuyListRepositoryInterface $buyListRepository,
        ProductRepositoryInterface $productRepository,
        BuyListItemFactory $buyListItemFactory,
        BuyListItemRepositoryInterface $buyListItemRepository
    ) {
        parent::__construct(
            $redirectResultFactory,
            $customerSession,
            $buyListSettings,
            $messageManager
        );
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->buyListRepository = $buyListRepository;
        $this->productRepository = $productRepository;
        $this->buyListItemFactory = $buyListItemFactory;
        $this->buyListItemRepository = $buyListItemRepository;
    }

    public function execute(): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();
        $httpUnauthorizedRequestCode = 401;
        $httpBadRequestCode = 400;
        $httpSuccessRequestCode = 201;
        $customerId = (int) $this->getCustomerSession()->getCustomer()->getId();
        $storeId = (int) $this->storeManager->getStore()->getId();

        try {
            $this->isAllowed();

            if (!$this->request->isAjax() || !$this->request->isPost()) {
                $resultJson->setHttpResponseCode($httpUnauthorizedRequestCode);
                throw new AuthorizationException(__('You can\'t save buy list in this way.'));
            }

            $qty = (int) $this->request->getParam('qty');
            $buyListId = (int) $this->request->getParam('buyListId');
            $buyList = $this->buyListRepository->getById($buyListId);

                        if (
                $buyList->getCustomerId() !== $customerId ||
                $buyList->getStoreId() !== $storeId ||
                !!!$buyList->getIsActive()
            ) {
                $resultJson->setHttpResponseCode($httpBadRequestCode);
                throw new CouldNotSaveException(__('You can\'t add products to this buy list.'));
            }

            $productId = (int) $this->request->getParam('productId');
            $product = $this->productRepository->getById($productId);

            if (!$product->getId()) {
                $resultJson->setHttpResponseCode($httpBadRequestCode);
                throw new CouldNotSaveException(__('You can\'t add product to this buy list.'));
            }

            if ($qty <= 0) {
                $resultJson->setHttpResponseCode($httpBadRequestCode);
                throw new CouldNotSaveException(__('Please enter a valid quantity.'));
            }

            $buyListItem = $this->saveItemToBuyList($buyListId, $productId, $qty);

            $response = [
                'message' => __('This product was added to buy list with ID %1.', $buyListItem->getId()),
                'errors' => false
            ];
            $resultJson->setHttpResponseCode($httpSuccessRequestCode);
        } catch (\Exception $exception) {
            $response = [
                'errors' => true,
                'message' => $exception->getMessage()
            ];
        }

        return $resultJson->setData($response);
    }

    protected function saveItemToBuyList(
        int $buyListId,
        int $productId,
        int $qty
    ): BuyListItemInterface {
        if (is_null($this->buyListItemRepository->getItemByBuyListId($buyListId, $productId))) {
            $buyListItem = $this->buyListItemFactory->create()
                ->setBuyListId($buyListId)
                ->setProductId($productId)
                ->setQty($qty);
        } else {
            $buyListItem = $this->buyListItemRepository->getItemByBuyListId($buyListId, $productId);
            $buyListItem->setBuyListId($buyListId)
                ->setProductId($productId)
                ->setQty($buyListItem->getQty() + $qty);
        }

        return $this->buyListItemRepository->save($buyListItem);
    }
}
