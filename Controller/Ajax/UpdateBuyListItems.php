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
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;

class UpdateBuyListItems extends GeneralBuyList implements ActionInterface
{
    protected JsonFactory $resultJsonFactory;
    protected RequestInterface $request;
    protected StoreManagerInterface $storeManager;
    protected BuyListRepositoryInterface $buyListRepository;
    protected BuyListItemRepositoryInterface $buyListItemRepository;
    protected ProductRepositoryInterface $productRepository;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemRepositoryInterface $buyListItemRepository,
        ProductRepositoryInterface $productRepository
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
        $this->buyListItemRepository = $buyListItemRepository;
        $this->productRepository = $productRepository;
    }

    public function execute(): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();
        $httpUnauthorizedRequestCode = 401;
        $httpSuccessRequestCode = 201;

        try {
            $this->isAllowed();

            if (!$this->request->isAjax() || !$this->request->isPost()) {
                $resultJson->setHttpResponseCode($httpUnauthorizedRequestCode);
                throw new AuthorizationException(__('You can\'t save buy list in this way.'));
            }

            $buyListId = $this->request->getParam('buyListId');
            $buyListItems = json_decode($this->request->getParam('buyListItems'));
            $customerId = (int) $this->getCustomerSession()->getCustomer()->getId();
            $storeId = (int) $this->storeManager->getStore()->getId();

            if (!$buyListId) {
                throw new AuthorizationException(__('Buy list id is not specified.'));
            }

            $buyList = $this->buyListRepository->getById($buyListId);

            if ($customerId !== $buyList->getCustomerId() || $storeId !== $buyList->getStoreId()) {
                throw new AuthorizationException(__('You can\'t save buy list in this way.'));
            }

            foreach ($buyListItems as $item) {
                $buyListItem = $this->buyListItemRepository->getById($item->entity_id);

                if ($item->qty <= 0) {
                    throw new CouldNotSaveException(__('Item with ID %1 has the invalid value.'));
                }

                $buyListItem->setQty($item->qty);
                $buyListItem = $this->buyListItemRepository->save($buyListItem);
            }

            $buyListItemCollection = $this->buyListItemRepository->getByBuyListId($buyListId)
                ->getData();

            foreach ($buyListItemCollection as &$buyListItem) {
                $currentProduct = $this->productRepository->getById($buyListItem['product_id']);
                $buyListItem['product_name'] = $currentProduct->getName();
                $buyListItem['sku'] = $currentProduct->getSku();
                $buyListItem['price'] = $currentProduct->getPrice();
                $buyListItem['qty'] = (int) $buyListItem['qty'];
                $buyListItem['subtotal'] = (int) $buyListItem['qty'] * $buyListItem['price'];
            }

            $response = [
                'items' => json_encode($buyListItemCollection),
                'message' => __('Buy list items were successfully updated.'),
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
}
