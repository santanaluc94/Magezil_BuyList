<?php

namespace Magezil\BuyList\Service;

use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;

abstract class AbstractBuyList
{
    protected Settings $buyListSettings;
    protected BuyListRepositoryInterface $buyListRepository;
    protected BuyListItemRepositoryInterface $buyListItemRepository;
    protected ProductRepositoryInterface $productRepository;
    protected CustomerRepositoryInterface $customerRepository;
    protected StoreRepositoryInterface $storeRepository;
    protected ManagerInterface $eventManager;

    public function __construct(
        Settings $buyListSettings,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemRepositoryInterface $buyListItemRepository,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        StoreRepositoryInterface $storeRepository,
        ManagerInterface $eventManager
    ) {
        $this->buyListSettings = $buyListSettings;
        $this->buyListRepository = $buyListRepository;
        $this->buyListItemRepository = $buyListItemRepository;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->storeRepository = $storeRepository;
        $this->eventManager = $eventManager;
    }

    protected function isValidProductId(int $productId): bool
    {
        $product = $this->productRepository->getById($productId);

        if (!$product->getId()) {
            return false;
        }

        return true;
    }

    protected function isBuyListEnabled(int $buyListId): bool
    {
        $buyList = $this->buyListRepository->getById($buyListId);

        if (!$buyList->getIsActive()) {
            return false;
        }

        return true;
    }

    protected function isListFull(int $buyListSize): bool
    {
        if (
            !empty($this->buyListSettings->getMaxQtyItems()) &&
            $this->buyListSettings->getMaxQtyItems() <= $buyListSize
        ) {
            return true;
        }

        return false;
    }

    protected function isValidCustomerId(int $customerId): bool
    {
        $customer = $this->customerRepository->getById($customerId);

        if (!$customer->getId()) {
            return false;
        }

        return true;
    }

    protected function isValidStoreId(int $storeId): bool
    {
        $store = $this->storeRepository->getById($storeId);

        if (!$store->getId()) {
            return false;
        }

        return true;
    }

    protected function isCustomerBelongsToBuyList(int $buyListId, int $customerId): bool
    {
        $buyList = $this->buyListRepository->getById($buyListId);

        if ($buyList->getCustomerId() !== $customerId) {
            return false;
        }

        return true;
    }
}
