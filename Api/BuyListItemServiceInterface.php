<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListItemInterface;

interface BuyListItemServiceInterface
{
    /**
     * @param integer $id
     * @return BuyListItemInterface
     */
    public function get(int $id): BuyListItemInterface;

    /**
     * @param integer $buyListId
     * @param BuyListItemInterface $item
     * @return BuyListItemInterface
     */
    public function saveItem(
        int $buyListId,
        BuyListItemInterface $item
    ): BuyListItemInterface;

    /**
     * @param integer $buyListId
     * @param integer $id
     * @return string
     */
    public function remove(int $buyListId, int $id): string;
}
