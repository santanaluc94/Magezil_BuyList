<?php

namespace Magezil\BuyList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;

use Magezil\BuyList\Model\BuyList;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\Collection as BuyListItemCollection;

class View extends Template
{
    protected RequestInterface $request;
    protected BuyListRepositoryInterface $buyListRepository;
    protected BuyListItemRepositoryInterface $buyListItemRepository;

    public function __construct(
        Context $context,
        RequestInterface $request,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemRepositoryInterface $buyListItemRepository
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->buyListRepository = $buyListRepository;
        $this->buyListItemRepository = $buyListItemRepository;
    }

    public function getCurrentBuyList(): BuyList
    {
        $buyListId = (int) $this->request->getParam('id');
        return $this->buyListRepository->getById($buyListId);
    }

    public function getCurrentBuyListJsonData(): string
    {
        return json_encode($this->getCurrentBuyList()->getData());
    }

    public function getStatus(): string
    {
        return $this->getCurrentBuyList()->getIsActive() ?
            'Active' : 'Inactive';
    }

    public function getCreatedAtFormatted(): string
    {
        return $this->formatDate($this->getCurrentBuyList()->getCreatedAt());
    }

    public function getUpdatedAtFormatted(): string
    {
        return $this->formatDate($this->getCurrentBuyList()->getUpdatedAt());
    }

    public function getQtyItems(): int
    {
        return $this->buyListItemRepository->getByBuyListId(
            $this->getCurrentBuyList()->getId()
        )->getSize();
    }
}
