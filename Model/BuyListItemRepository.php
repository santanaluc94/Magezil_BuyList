<?php

namespace Magezil\BuyList\Model;

use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magezil\BuyList\Model\BuyListItemFactory;
use Magezil\BuyList\Model\ResourceModel\BuyListItem as ResourceModelBuyListItem;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\CollectionFactory as BuyListItemCollectionFactory;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magezil\BuyList\Model\BuyListItem;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\Collection as BuyListItemCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class BuyListItemRepository implements BuyListItemRepositoryInterface
{
    private BuyListItemFactory $buyListItemFactory;
    private ResourceModelBuyListItem $resourceModelBuyListItem;
    private BuyListItemCollectionFactory $buyListItemCollectionFactory;

    public function __construct(
        BuyListItemFactory $buyListItemFactory,
        ResourceModelBuyListItem $resourceModelBuyListItem,
        BuyListItemCollectionFactory $buyListItemCollectionFactory
    ) {
        $this->buyListItemFactory = $buyListItemFactory;
        $this->resourceModelBuyListItem = $resourceModelBuyListItem;
        $this->buyListItemCollectionFactory = $buyListItemCollectionFactory;
    }

    public function getById(int $id): BuyListItemInterface
    {
        $buyListItem = $this->buyListItemFactory->create();
        $this->resourceModelBuyListItem->load($buyListItem, $id);

        if (!$buyListItem->getId()) {
            throw NoSuchEntityException::singleField(BuyListItemInterface::ID, $id);
        }

        return $buyListItem;
    }

    public function save(BuyListItemInterface $buyListItem): BuyListItemInterface
    {
        try {
            /** @var BuyListItem $buyListItem */
            $buyListItem->getResource()->save($buyListItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Unable to save object. Error: %1', $exception->getMessage())
            );
        }

        return $buyListItem;
    }

    public function delete(BuyListItemInterface $buyListItem): bool
    {
        try {
            /** @var BuyListItem $buyListItem */
            $this->resourceModelBuyListItem->delete($buyListItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Unable to remove object with ID %1. Error: %2',
                $buyListItem->getId(),
                $exception->getMessage()
            ));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $buyListItem = $this->getById($id);
        return $this->delete($buyListItem);
    }

    public function getByBuyListId(int $buyListId): ?BuyListItemCollection
    {
        $buyListItemCollection = $this->buyListItemCollectionFactory->create()
            ->addFieldToFilter(BuyListItemInterface::BUY_LIST_ID, $buyListId);

        if (!$buyListItemCollection->getSize()) {
            return null;
        }

        return $buyListItemCollection;
    }

    public function getItemByBuyListId(int $buyListId, int $productId): ?BuyListItemInterface
    {
        $buyListItemCollection = $this->buyListItemCollectionFactory->create()
            ->addFieldToFilter(BuyListItemInterface::BUY_LIST_ID, $buyListId)
            ->addFieldToFilter(BuyListItemInterface::PRODUCT_ID, $productId);

        if (!$buyListItemCollection->getSize()) {
            return null;
        }

        return $buyListItemCollection->getFirstItem();
    }
}
