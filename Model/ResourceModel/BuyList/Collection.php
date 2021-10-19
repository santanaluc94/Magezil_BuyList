<?php

namespace Magezil\BuyList\Model\ResourceModel\BuyList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezil\BuyList\Model\BuyList as ModelBuyList;
use Magezil\BuyList\Model\ResourceModel\BuyList as ResourceModelBuyList;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            ModelBuyList::class,
            ResourceModelBuyList::class
        );
    }
}
