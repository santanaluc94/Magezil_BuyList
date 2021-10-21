<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListInterface;

interface BuyListRepositoryInterface
{
    public function getById(int $id): BuyListInterface;

    public function save(BuyListInterface $buyList): BuyListInterface;

    public function delete(BuyListInterface $buyList): bool;

    public function deleteById(int $id): bool;
}
