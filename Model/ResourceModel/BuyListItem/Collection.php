<?php

namespace Magezil\BuyList\Model\ResourceModel\BuyListItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezil\BuyList\Model\BuyListItem as ModelBuyListItem;
use Magezil\BuyList\Model\ResourceModel\BuyListItem as ResourceModelBuyListItem;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            ModelBuyListItem::class,
            ResourceModelBuyListItem::class
        );
    }
}
