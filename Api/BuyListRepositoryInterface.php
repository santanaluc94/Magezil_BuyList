<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListInterface;

interface BuyListRepositoryInterface
{
    /**
     * @param integer $id
     * @return BuyListInterface
     */
    public function getById(int $id): BuyListInterface;

    /**
     * @param BuyListInterface $buyList
     * @return BuyListInterface
     */
    public function save(BuyListInterface $buyList): BuyListInterface;

    /**
     * @param BuyListInterface $buyList
     * @return boolean
     */
    public function delete(BuyListInterface $buyList): bool;

    /**
     * @param integer $id
     * @return boolean
     */
    public function deleteById(int $id): bool;

    /**
     * @param BuyListInterface $buyList
     * @return BuyListInterface
     */
    public function update(BuyListInterface $buyList): BuyListInterface;

    /**
     * @param integer $id
     * @return string
     */
    public function remove(int $id): string;
}
