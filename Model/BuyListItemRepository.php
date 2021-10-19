<?php

namespace Magezil\BuyList\Model;

use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magezil\BuyList\Model\BuyListItemFactory;
use Magezil\BuyList\Model\ResourceModel\BuyListItem as ResourceModelBuyList;
use Magezil\BuyList\Model\BuyListItem;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class BuyListItemRepository implements BuyListItemRepositoryInterface
{
    private BuyListItemFactory $buyListItemFactory;
    private ResourceModelBuyList $resourceModelBuyListItem;

    public function __construct(
        BuyListItemFactory $buyListItemFactory,
        ResourceModelBuyList $resourceModelBuyListItem
    ) {
        $this->buyListItemFactory = $buyListItemFactory;
        $this->resourceModelBuyListItem = $resourceModelBuyListItem;
    }

    public function getById(int $id): BuyListItem
    {
        $buyListItem = $this->buyListItemFactory->create();
        $this->resourceModelBuyListItem->load($buyListItem, $id);

        if(!$buyListItem->getId()) {
            throw NoSuchEntityException::singleField(BuyListItem::ID, $id);
        }

        return $buyListItem;
    }

    public function save(BuyListItem $buyListItem): BuyListItem
    {
        try {
            $buyListItem->getResource()->save($buyListItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Unable to save object. Error: %1', $exception->getMessage())
            );
        }

        return $buyListItem;
    }

    public function delete(BuyListItem $buyListItem): bool
    {
        try {
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
}
