<?php

namespace Magezil\BuyList\Controller\Lists;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\SessionFactory;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\AuthorizationException;

abstract class GeneralBuyList
{
    protected RedirectFactory $redirectResultFactory;
    protected SessionFactory $customerSession;
    protected Settings $buyListSettings;
    protected ManagerInterface $messageManager;

    public function __construct(
        RedirectFactory $redirectResultFactory,
        SessionFactory $customerSession,
        Settings $buyListSettings,
        ManagerInterface $messageManager
    ) {
        $this->redirectResultFactory = $redirectResultFactory;
        $this->customerSession = $customerSession;
        $this->buyListSettings = $buyListSettings;
        $this->messageManager = $messageManager;
    }

    abstract public function execute(): ResultInterface;

    protected function isAllowed(): void
    {
        if (!$this->buyListSettings->isModuleEnable()) {
            throw new AuthorizationException(__('This module is not allowed.'));
        }

        if (!$this->getCustomerSession()->isLoggedIn()) {
            throw new AuthorizationException(__('You must logged in to have access to your buy lists.'));
        }
    }

    protected function getCustomerSession(): Session
    {
        return $this->customerSession->create();
    }
}
