<?php

namespace Magezil\BuyList\Api;

use Magezil\BuyList\Api\Data\BuyListItemInterface;

interface BuyListItemServiceInterface
{
    /**
     * @param integer $itemId
     * @return BuyListItemInterface
     */
    public function get(int $itemId): BuyListItemInterface;

    /**
     * @param integer $buyListId
     * @param BuyListItemInterface $item
     * @return BuyListItemInterface
     */
    public function createItem(
        int $buyListId,
        BuyListItemInterface $item
    ): BuyListItemInterface;

    /**
     * @param integer $buyListId
     * @param BuyListItemInterface $item
     * @return BuyListItemInterface
     */
    public function updateItem(
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
