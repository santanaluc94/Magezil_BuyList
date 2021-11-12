<?php

namespace Magezil\BuyList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Create extends Template
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function getSaveUrl(): string
    {
        return $this->getUrl('buy_list/ajax/savePost');
    }

    public function getUrlNewBuyList(): string
    {
        return $this->getUrl('buy_list/lists/create');
    }
}
