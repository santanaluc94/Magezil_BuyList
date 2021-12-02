<?php

namespace Magezil\BuyList\Block\Catalog\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Model\ResourceModel\BuyList\CollectionFactory as BuyListCollectionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Customer;
use Magezil\BuyList\Model\ResourceModel\BuyList\Collection as BuyListCollection;
use Magento\Store\Api\Data\StoreInterface;

class AddToBuyList extends Template
{
    protected Settings $settings;
    protected BuyListCollectionFactory $buyListCollectionFactory;
    protected CustomerSessionFactory $customerSessionFactory;
    protected StoreManagerInterface $storeManager;

    public function __construct(
        Context $context,
        Settings $settings,
        BuyListCollectionFactory $buyListCollectionFactory,
        CustomerSessionFactory $customerSessionFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->settings = $settings;
        $this->buyListCollectionFactory = $buyListCollectionFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->storeManager = $storeManager;
    }

    public function isBuyListEnabled(): bool
    {
        $customerSession = $this->customerSessionFactory->create();

        return $this->settings->isModuleEnable() &&
            $customerSession->isLoggedIn() &&
            $this->settings->isCustomerGroupIdAvailable($customerSession->getCustomer()->getGroupId());
    }

    public function getCustomer(): Customer
    {
        return $this->customerSessionFactory->create()
            ->getCustomer();
    }

    public function getBuyLists(): BuyListCollection
    {
        $buyListCollection = $this->buyListCollectionFactory->create();
        return $buyListCollection->addFieldToFilter('customer_id', $this->getCustomer()->getId())
            ->addFieldToFilter('store_id', $this->getStore()->getId())
            ->addFieldToFilter('is_active', true);
    }

    public function getBuyListsJsonData(): string
    {
        return json_encode($this->getBuyLists()->toArray()['items']);
    }

    public function getProductId(): int
    {
        return $this->getRequest()->getParam('id');
    }

    public function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }
}
