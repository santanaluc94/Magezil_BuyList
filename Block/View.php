<?php

namespace Magezil\BuyList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magezil\BuyList\Model\BuyList;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\Collection as BuyListItemCollection;

class View extends Template
{
    protected RequestInterface $request;
    protected BuyListRepositoryInterface $buyListRepository;
    protected BuyListItemRepositoryInterface $buyListItemRepository;
    protected ProductRepositoryInterface $productRepository;

    public function __construct(
        Context $context,
        RequestInterface $request,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemRepositoryInterface $buyListItemRepository,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->buyListRepository = $buyListRepository;
        $this->buyListItemRepository = $buyListItemRepository;
        $this->productRepository = $productRepository;
    }

    public function getBuyListId(): int
    {
        return (int) $this->request->getParam('id');
    }

    public function getCurrentBuyList(): BuyList
    {
        $buyListId = $this->getBuyListId();
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

    public function getCurrentBuyListItems(): ?BuyListItemCollection
    {
        return $this->buyListItemRepository->getByBuyListId(
            $this->getCurrentBuyList()->getId()
        );
    }

    public function getQtyItems(): int
    {
        if (is_null($this->getCurrentBuyListItems())) {
            return 0;
        }

        return $this->getCurrentBuyListItems()->getSize();
    }

    public function hasQtyItems(): bool
    {
        return $this->getQtyItems() ? true : false;
    }

    public function getCurrentBuyListItemsJsonData(): string
    {
        $buyListItems = $this->getCurrentBuyListItems()->getData();

        foreach ($buyListItems as &$buyListItem) {
            $currentProduct = $this->productRepository->getById($buyListItem['product_id']);
            $buyListItem['product_name'] = $currentProduct->getName();
            $buyListItem['sku'] = $currentProduct->getSku();
            $buyListItem['price'] = $currentProduct->getPrice();
            $buyListItem['qty'] = (int) $buyListItem['qty'];
            $buyListItem['subtotal'] = (int) $buyListItem['qty'] * $buyListItem['price'];
        }

        return json_encode($buyListItems);
    }
}
