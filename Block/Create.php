<?php

namespace Magezil\BuyList\Block;

use Magento\Framework\View\Element\Template;

class Create extends Template
{
    public function getSaveUrl(): string
    {
        return $this->getUrl('buy_list/ajax/savePost');
    }

    public function getUrlNewBuyList(): string
    {
        return $this->getUrl('buy_list/lists/create');
    }
}
