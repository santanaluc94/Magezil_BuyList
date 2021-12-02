<?php

namespace Magezil\BuyList\Block\Catalog\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Model\ResourceModel\BuyList\CollectionFactory as BuyListCollectionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Registry;

use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magezil\BuyList\Model\ResourceModel\BuyList\Collection as BuyListCollection;
use Magento\Store\Api\Data\StoreInterface;

class AddToBuyList extends Template
{
    protected Settings $settings;
    protected BuyListCollectionFactory $buyListCollectionFactory;
    protected CustomerSessionFactory $customerSessionFactory;
    protected Registry $registry;
    protected StoreManagerInterface $storeManager;


    public function __construct(
        Context $context,
        Settings $settings
    ) {
        parent::__construct($context);
        $this->settings = $settings;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->buyListCollectionFactory = $this->objectManager->get(BuyListCollectionFactory::class);
        $this->customerSessionFactory = $this->objectManager->get(CustomerSessionFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    public function isBuyListEnabled(): bool
    {
        return $this->settings->isModuleEnable();
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
        return $this->registry->registry('current_product')->getId();
    }

    public function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }
}
