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
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;

class UpdateStatusPost extends GeneralBuyList implements ActionInterface
{
    protected JsonFactory $resultJsonFactory;
    protected RequestInterface $request;
    protected StoreManagerInterface $storeManager;
    protected BuyListRepositoryInterface $buyListRepository;

    public function __construct(
        JsonFactory $resultJsonFactory,
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        BuyListRepositoryInterface $buyListRepository
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
    }

    public function execute(): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();
        $httpUnauthorizedRequestCode = 401;
        $httpBadRequestCode = 400;
        $httpSuccessRequestCode = 201;

        try {
            $this->isAllowed();

            if (!$this->request->isAjax() || !$this->request->isPost()) {
                $resultJson->setHttpResponseCode($httpUnauthorizedRequestCode);
                throw new AuthorizationException(__('You can\'t save buy list in this way.'));
            }

            $buyListId = (int) $this->request->getParam('id');
            $buyList = $this->buyListRepository->getById($buyListId);
            $customerId = (int) $this->getCustomerSession()->getCustomer()->getId();
            $storeId = (int) $this->storeManager->getStore()->getId();

            if ($buyList->getCustomerId() !== $customerId || $buyList->getStoreId() !== $storeId) {
                $resultJson->setHttpResponseCode($httpBadRequestCode);
                throw new AuthorizationException(__('You can\'t change the status of this buy list.'));
            }

            $buyList->getIsActive() ? $buyList->setIsActive(false) : $buyList->setIsActive(true);
            $buyList = $this->buyListRepository->save($buyList);
            $status = $buyList->getIsActive() ? 'active' : 'inactive';

            $response = [
                'message' => __('Buy list status has been changed to %1.', $status),
                'status' => $status,
                'isActive' => $buyList->getIsActive(),
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
