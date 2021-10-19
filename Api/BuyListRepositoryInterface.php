<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Model\BuyList;

interface BuyListRepositoryInterface
{
    public function getById(int $id): BuyList;

    public function save(BuyList $buyList): BuyList;

    public function delete(BuyList $buyList): bool;

    public function deleteById(int $id): bool;
}
