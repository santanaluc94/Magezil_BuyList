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
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;

class RemoveItem extends GeneralBuyList implements ActionInterface
{
    protected JsonFactory $resultJsonFactory;
    protected RequestInterface $request;
    protected BuyListItemRepositoryInterface $buyListItemRepository;
    protected ProductRepositoryInterface $productRepository;

    public function __construct(
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
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
                throw new AuthorizationException(__('You can\'t remote item buy list in this way.'));
            }

            $buyListItemId = (int) $this->request->getParam('id');
            $buyListId = $this->buyListItemRepository->getById($buyListItemId)->getBuyListId();
            $this->buyListItemRepository->deleteById($buyListItemId);

            if (!is_null($this->buyListItemRepository->getByBuyListId($buyListId))) {
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
            } else {
                $buyListItemCollection = [];
            }

            $response = [
                'items' => json_encode($buyListItemCollection),
                'message' => __('The item with ID %1 has been successfully removed.', $buyListItemId),
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
