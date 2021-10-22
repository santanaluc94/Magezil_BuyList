<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListInterface;

interface CustomerBuyListServiceInterface
{
    /**
     * @param integer $id
     * @param integer $customerId
     * @return BuyListInterface
     */
    public function get(int $id, int $customerId): BuyListInterface;

    /**
     * @param BuyListInterface $buyList
     * @param integer $customerId
     * @return BuyListInterface
     */
    public function create(BuyListInterface $buyList, int $customerId): BuyListInterface;

    /**
     * @param BuyListInterface $buyList
     * @param integer $customerId
     * @return BuyListInterface
     */
    public function update(BuyListInterface $buyList, int $customerId): BuyListInterface;

    /**
     * @param integer $id
     * @param integer $customerId
     * @return string
     */
    public function remove(int $id, int $customerId): string;
}
