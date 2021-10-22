<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListItemInterface;

interface BuyListItemRepositoryInterface
{
    public function getById(int $id): BuyListItemInterface;

    public function save(BuyListItemInterface $buyListItem): BuyListItemInterface;

    public function delete(BuyListItemInterface $buyListItem): bool;

    public function deleteById(int $id): bool;
}
