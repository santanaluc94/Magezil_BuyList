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
use Magezil\BuyList\Model\ResourceModel\BuyList\CollectionFactory as BuyListCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magezil\BuyList\Api\Data\BuyListInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;

class UpdateBuyListTable extends GeneralBuyList implements ActionInterface
{
    protected JsonFactory $resultJsonFactory;
    protected RequestInterface $request;
    protected BuyListCollectionFactory $buyListCollectionFactory;
    protected StoreManagerInterface $storeManager;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        RequestInterface $request,
        BuyListCollectionFactory $buyListCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $redirectResultFactory,
            $customerSession,
            $buyListSettings,
            $messageManager
        );
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->buyListCollectionFactory = $buyListCollectionFactory;
        $this->storeManager = $storeManager;
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

            $customerId = (int) $this->getCustomerSession()->getCustomer()->getId();
            $storeId = (int) $this->storeManager->getStore()->getId();
            $page = $this->request->getParam('p');
            $pageSize = $this->request->getParam('limit');

            $buyListCollection = $this->buyListCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('store_id', $storeId)
                ->setCurPage($page)
                ->setPageSize($pageSize);

            $response = [
                'totals' => $buyListCollection->getSize(),
                'pageSize' => $pageSize,
                'currentPage' => $page,
                'errors' => false
            ];
            /** @var BuyListInterface $buyList */
            foreach ($buyListCollection->getItems() as $buyList) {
                $response['items'][] = [
                    BuyListInterface::ID => $buyList->getId(),
                    BuyListInterface::CUSTOMER_ID => $buyList->getCustomerId(),
                    BuyListInterface::TITLE => $buyList->getTitle(),
                    BuyListInterface::IS_ACTIVE => $buyList->getIsActive(),
                    BuyListInterface::STORE_ID => $buyList->getStoreId(),
                    BuyListInterface::CREATED_AT => $buyList->getCreatedAt(),
                    BuyListInterface::UPDATED_AT => $buyList->getUpdatedAt()
                ];
            }

            $response['errors'] = false;
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
