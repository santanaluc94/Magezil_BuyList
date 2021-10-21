<?php

namespace Magezil\BuyList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BuyList extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('magezil_buy_list', 'entity_id');
    }
}
