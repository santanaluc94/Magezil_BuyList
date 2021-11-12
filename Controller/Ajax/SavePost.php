<?php

namespace Magezil\BuyList\Controller\Ajax;

use Magezil\BuyList\Controller\Lists\GeneralBuyList;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\SessionFactory;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\RequestInterface;
use Magezil\BuyList\Model\BuyListFactory;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magezil\BuyList\Model\BuyList;
use Magento\Framework\Exception\NoSuchEntityException;

class SavePost extends GeneralBuyList implements ActionInterface
{
    protected JsonFactory $resultJsonFactory;
    protected Validator $formKeyValidator;
    protected RequestInterface $request;
    protected BuyListFactory $buyListFactory;
    protected BuyListRepositoryInterface $buyListRepository;
    protected StoreManagerInterface $storeManager;
    protected RedirectInterface $redirect;
    protected LoggerInterface $logger;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Validator $formKeyValidator,
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        RequestInterface $request,
        BuyListFactory $buyListFactory,
        BuyListRepositoryInterface $buyListRepository,
        StoreManagerInterface $storeManager,
        RedirectInterface $redirect,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $redirectResultFactory,
            $customerSession,
            $buyListSettings,
            $messageManager
        );
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
        $this->buyListFactory = $buyListFactory;
        $this->buyListRepository = $buyListRepository;
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->logger = $logger;
    }

    public function execute(): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();
        $httpUnauthorizedRequestCode = 401;
        $httpBadRequestCode = 400;
        $httpSuccessRequestCode = 201;

        try {
            $this->isAllowed();

            if (!$this->formKeyValidator->validate($this->request)) {
                $resultJson->setHttpResponseCode($httpUnauthorizedRequestCode);
                throw new AuthorizationException(__('Form key is not valid.'));
            }

            if (!$this->request->isAjax() || !$this->request->isPost()) {
                $resultJson->setHttpResponseCode($httpUnauthorizedRequestCode);
                throw new AuthorizationException(__('You can\'t save buy list in this way.'));
            }

            if (!$this->request->getParam('title') || $this->request->getParam('isActive')) {
                $resultJson->setHttpResponseCode($httpBadRequestCode);
                throw new NoSuchEntityException(__('You must pass a tittle and a status to create a buy list.'));
            }

            $title = filter_var($this->request->getParam('title'), FILTER_SANITIZE_STRING);
            $isActive = (bool) $this->request->getParam('isActive');
            $customerId = (int) $this->getCustomerSession()->getCustomer()->getId();
            $storeId = (int) $this->storeManager->getStore()->getId();

            /** @var BuyList $buyList */
            $buyList = $this->buyListFactory->create();
            $buyList->setTitle($title)
                ->setIsActive($isActive)
                ->setCustomerId($customerId)
                ->setStoreId($storeId);
            $buyList = $this->buyListRepository->save($buyList);

            $successMessage = __('Buy list with ID %1 has been created successfully', $buyList->getId());
            $response = [
                'errors' => false,
                'message' => $successMessage
            ];

            $resultJson->setHttpResponseCode($httpSuccessRequestCode);
            $this->logger->info($successMessage);
        } catch (\Exception $exception) {
            $response = [
                'errors' => true,
                'message' => $exception->getMessage()
            ];
            $this->logger->error($exception->getMessage());
        }

        return $resultJson->setData($response);
    }
}
