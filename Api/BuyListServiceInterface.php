<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListInterface;

interface BuyListServiceInterface
{
    /**
     * @param integer $id
     * @return BuyListInterface
     */
    public function get(int $id): BuyListInterface;

    /**
     * @param BuyListInterface $buyList
     * @return BuyListInterface
     */
    public function create(BuyListInterface $buyList): BuyListInterface;

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
