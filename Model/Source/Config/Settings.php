<?php

namespace Magezil\BuyList\Model\Source\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Settings
{
    /* General settings */
    private const BUY_LIST_MODULE_ENABLE = 'buy_list/general/enable';
    private const BUY_LIST_MODULE_TITLE = 'buy_list/general/title';
    private const BUY_LIST_AVAILABLE_CUSTOMER_GROUPS = 'buy_list/general/available_customer_groups';
    private const BUY_LIST_MODULE_DELETE_LISTS = 'buy_list/general/delete_lists';

    /* Customer settings */
    private const BUY_LIST_MAX_QTY_LISTS = 'buy_list/customer/max_qty_lists';
    private const BUY_LIST_MAX_QTY_ITEMS = 'buy_list/customer/max_qty_items';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isModuleEnable(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BUY_LIST_MODULE_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getModuleTitle(): string
    {
        return $this->scopeConfig->getValue(
            self::BUY_LIST_MODULE_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAvailableCustomerGroups(): array
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                self::BUY_LIST_AVAILABLE_CUSTOMER_GROUPS,
                ScopeInterface::SCOPE_STORE
            )
        );
    }

    public function isDeleteLists(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BUY_LIST_MODULE_DELETE_LISTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMaxQtyLists(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::BUY_LIST_MAX_QTY_LISTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMaxQtyItems(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::BUY_LIST_MAX_QTY_ITEMS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isCustomerGroupIdAvailable(?int $customerGroupId): bool
    {
        if (is_null($customerGroupId)) {
            return false;
        }

        return in_array($customerGroupId, $this->getAvailableCustomerGroups());
    }

    public function canCreateListToCustomer(int $customerId): bool
    {
        if ($this->getMaxQtyLists() === 0) {
            return true;
        }

        $multipleWishlistCollection = $this->multipleWishlistCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);

        if ($multipleWishlistCollection->getSize() >= $this->getMaxQtyLists()) {
            return false;
        }

        return true;
    }

    public function canCreateItemToList(int $wishlistId): bool
    {
        if ($this->getMaxQtyItems() === 0) {
            return true;
        }

        $multipleWishlistItemCollection = $this->multipleWishlistItemCollectionFactory->create()
            ->addFieldToFilter('wishlist_id', $wishlistId);

        if ($multipleWishlistItemCollection->getSize() >= $this->getMaxQtyItems()) {
            return false;
        }

        return true;
    }
}
