<?php

namespace Magezil\BuyList\Controller\Lists;

use Magezil\BuyList\Controller\Lists\GeneralBuyList;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\RequestInterface;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magezil\BuyList\Api\BuyListRepositoryInterface;

class View extends GeneralBuyList implements ActionInterface
{
    protected PageFactory $resultPageFactory;
    protected RequestInterface $request;
    protected BuyListRepositoryInterface $buyListRepository;

    public function __construct(
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        PageFactory $resultPageFactory,
        RequestInterface $request,
        BuyListRepositoryInterface $buyListRepository
    ) {
        parent::__construct(
            $redirectResultFactory,
            $customerSession,
            $buyListSettings,
            $messageManager
        );
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->buyListRepository = $buyListRepository;
    }

    public function execute(): ResultInterface
    {
        try {
            $buyListId = (int) $this->request->getParam('id');

            if (!$buyListId) {
                throw new NoSuchEntityException(__('You must pass a valid ID to access this list.'));
            }

            $buyList = $this->buyListRepository->getById($buyListId);

            $customerId = (int) $this->getCustomerSession()
                ->getCustomer()
                ->getId();

            if (!$buyList->getId() || $customerId !== $buyList->getCustomerId()) {
                throw new NoSuchEntityException(__('You don\'t have access to this list.'));
            }

            $resultPage = $this->resultPageFactory->create();

            $this->isAllowed();

            $resultPage->getConfig()
                ->getTitle()
                ->set(__('Buy List #%1', $buyListId));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $redirectResult = $this->redirectResultFactory->create();
            $redirectResult->setPath('customer/account/login')
                ->setHttpResponseCode(301);

            return $redirectResult;
        }

        return $resultPage;
    }
}
