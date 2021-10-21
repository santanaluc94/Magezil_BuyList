<?php

namespace Magezil\BuyList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BuyListItem extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('magezil_buy_list_item', 'entity_id');
    }
}
