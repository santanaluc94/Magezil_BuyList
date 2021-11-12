<?php

namespace Magezil\BuyList\Controller\Lists;

use Magezil\BuyList\Controller\Lists\GeneralBuyList;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\SessionFactory;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;

class Create extends GeneralBuyList implements ActionInterface
{
    protected PageFactory $resultPageFactory;

    public function __construct(
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager,
        PageFactory $resultPageFactory
    ) {
        parent::__construct(
            $redirectResultFactory,
            $customerSession,
            $buyListSettings,
            $messageManager
        );
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute(): ResultInterface
    {
        try {
            $resultPage = $this->resultPageFactory->create();

            $this->isAllowed();

            $resultPage->getConfig()
                ->getTitle()
                ->set(__('Add new Buy Lists'));
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
