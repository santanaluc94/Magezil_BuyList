<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListItemInterface;

interface CustomerBuyListItemServiceInterface
{
    /**
     * @param integer $id
     * @param integer $customerId
     * @return BuyListItemInterface
     */
    public function get(int $id, int $customerId): BuyListItemInterface;

    /**
     * @param integer $buyListId
     * @param BuyListItemInterface $item
     * @param integer $customerId
     * @return BuyListItemInterface
     */
    public function saveItem(
        int $buyListId,
        BuyListItemInterface $item,
        int $customerId
    ): BuyListItemInterface;

    /**
     * @param integer $buyListId
     * @param integer $id
     * @param integer $customerId
     * @return string
     */
    public function remove(int $buyListId, int $id, int $customerId): string;
}
