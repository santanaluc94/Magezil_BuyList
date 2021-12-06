<?php

namespace Magezil\BuyList\ViewModel\View;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;

class Create implements ArgumentInterface
{
    private UrlInterface $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function getSaveUrl(): string
    {
        return $this->urlBuilder->getUrl('buy_list/ajax/savePost');
    }

    public function getUrlNewBuyList(): string
    {
        return $this->urlBuilder->getUrl('buy_list/lists/create');
    }
}
