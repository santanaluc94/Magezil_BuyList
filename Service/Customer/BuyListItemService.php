<?php

namespace Magezil\BuyList\Service\Customer;

use Magezil\BuyList\Service\AbstractBuyList;
use Magezil\BuyList\Api\CustomerBuyListItemServiceInterface;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\CollectionFactory as BuyListItemCollectionFactory;
use Magezil\BuyList\Model\BuyListItemFactory;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\ValidatorException;

class BuyListItemService extends AbstractBuyList implements CustomerBuyListItemServiceInterface
{
    protected BuyListItemCollectionFactory $buyListItemCollectionFactory;
    protected BuyListItemFactory $buyListItemFactory;

    public function __construct(
        Settings $buyListSettings,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemRepositoryInterface $buyListItemRepository,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        StoreRepositoryInterface $storeRepository,
        ManagerInterface $eventManager,
        BuyListItemCollectionFactory $buyListItemCollectionFactory,
        BuyListItemFactory $buyListItemFactory
    ) {
        parent::__construct(
            $buyListSettings,
            $buyListRepository,
            $buyListItemRepository,
            $productRepository,
            $customerRepository,
            $storeRepository,
            $eventManager
        );
        $this->buyListItemCollectionFactory = $buyListItemCollectionFactory;
        $this->buyListItemFactory = $buyListItemFactory;
    }

    /**
     * @param integer $id
     * @param integer $customerId
     * @return BuyListItemInterface
     */
    public function get(int $id, int $customerId): BuyListItemInterface
    {
        if (!isset($customerId)) {
            throw new AuthorizationException(__('This API can only be accessed by a logged customer.'));
        }

        $buyListItem = $this->buyListItemRepository->getById($id);

        if (!$this->isCustomerBelongsToBuyList($buyListItem->getBuyListId(), $customerId)) {
            throw new NoSuchEntityException(
                __(
                    'The buy list with ID %1 does not belong to the logged in customer.',
                    $buyListItem->getBuyListId()
                )
            );
        }

        return $buyListItem;
    }

    /**
     * @param integer $buyListId
     * @param BuyListItemInterface $item
     * @param integer $customerId
     * @return BuyListItemInterface
     */
    public function saveItem(
        int $buyListId,
        BuyListItemInterface $item,
        int $customerId
    ): BuyListItemInterface {
        if (!$this->isBuyListEnabled($buyListId)) {
            throw new ValidatorException(
                __(
                    'This buy list is disabled, it is not possible to add or update items.'
                )
            );
        }

        /** @var BuyListItemCollection $buyListItemCollection */
        $buyListItemCollection = $this->buyListItemCollectionFactory->create()
            ->addFieldToFilter(BuyListItemInterface::BUY_LIST_ID, $buyListId);

        if ($this->isListFull($buyListItemCollection->getSize())) {
            throw new ValidatorException(__(
                "It is not possible to add more items to the buy list. The maximum quantity of items per list is %1. This list already has %2 items.",
                $this->buyListSettings->getMaxQtyItems(),
                $buyListItemCollection->getSize()
            ));
        }

        $this->eventManager->dispatch(
            'buy_list_item_api_customer_save_before',
            ['$item' => $item]
        );

        /** @var BuyListItemInterface $item */
        if ($item->getId()) {
            $buyListItem = $this->buyListItemRepository->getById($item->getId());

            if ($item->getProductId() && !$this->isValidProductId($item->getProductId())) {
                throw new NoSuchEntityException(__('The product with ID %1 does not exist.'));
            }

            if (!$this->isCustomerBelongsToBuyList($buyListItem->getBuyListId(), $customerId)) {
                throw new NoSuchEntityException(
                    __(
                        'The buy list with ID %1 does not belong to the logged in customer.',
                        $buyListItem->getBuyListId()
                    )
                );
            }
        } else {
            $buyListItem = $this->buyListItemFactory->create();
        }

        $buyListItem->setBuyListId($buyListId);
        $buyListItem->setQty($item->getQty());
        $buyListItem->setProductId($item->getProductId());
        $buyListItem = $this->buyListItemRepository->save($buyListItem);

        $this->eventManager->dispatch(
            'buy_list_item_api_customer_save_after',
            ['buyList' => $buyListItem]
        );

        return $buyListItem;
    }

    /**
     * @param integer $buyListId
     * @param integer $id
     * @param integer $customerId
     * @return string
     */
    public function remove(int $buyListId, int $id, int $customerId): string
    {
        $buyListItem = $this->buyListItemRepository->getById($id);

        $this->eventManager->dispatch(
            'buy_list_item_api_customer_remove_before',
            ['buyList' => $buyListItem]
        );

        if (!$this->isCustomerBelongsToBuyList($buyListItem->getBuyListId(), $customerId)) {
            throw new NoSuchEntityException(
                __(
                    'The buy list with ID %1 does not belong to the logged in customer.',
                    $buyListItem->getBuyListId()
                )
            );
        }

        $this->buyListItemRepository->deleteById($id);

        $this->eventManager->dispatch('buy_list_item_api_customer_remove_after');

        return __(
            'The item with ID %1 has been removed from buy list with ID %2.',
            $id,
            $buyListId
        );
    }
}
