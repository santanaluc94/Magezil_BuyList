<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\Collection as BuyListItemCollection;

interface BuyListItemRepositoryInterface
{
    public function getById(int $id): BuyListItemInterface;

    public function save(BuyListItemInterface $buyListItem): BuyListItemInterface;

    public function delete(BuyListItemInterface $buyListItem): bool;

    public function deleteById(int $id): bool;

    public function getByBuyListId(int $buyListId): ?BuyListItemCollection;

    public function getItemByBuyListId(int $buyListId, int $productId): ?BuyListItemInterface;
}
