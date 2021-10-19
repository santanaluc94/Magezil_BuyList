<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Model\BuyListItem;

interface BuyListItemRepositoryInterface
{
    public function getById(int $id): BuyListItem;

    public function save(BuyListItem $buyListItem): BuyListItem;

    public function delete(BuyListItem $buyListItem): bool;

    public function deleteById(int $id): bool;
}
