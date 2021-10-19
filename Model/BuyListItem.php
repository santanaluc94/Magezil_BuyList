<?php

namespace Magezil\BuyList\Model;

use Magento\Framework\Model\AbstractModel;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magezil\BuyList\Model\ResourceModel\BuyList as ResourceModelBuyList;

class BuyListItem extends AbstractModel implements BuyListItemInterface
{
    protected $_cacheTag = 'magezil_buy_list_item';
    protected $_eventPrefix = 'magezil_buy_list_item';

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context, $registry);
    }

    protected function _construct(): void
    {
        $this->_init(ResourceModelBuyList::class);
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID);
    }

    public function getBuyListId(): ?int
    {
        return $this->getData(self::BUY_LIST_ID);
    }

    public function setBuyListId(?int $buyListId): self
    {
        return $this->setData(self::BUY_LIST_ID, $buyListId);
    }

    public function getProductId(): ?int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId(?int $productId): self
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getQty(): ?float
    {
        return $this->getData(self::QTY);
    }

    public function setQty(?float $qty): self
    {
        return $this->setData(self::QTY, $qty);
    }

    public function getStoreId(): ?int
    {
        return $this->getData(self::STORE_ID);
    }

    public function setStoreId(?int $storeId): self
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(?string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getupdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setupdatedAt(?string $updatedAt): self
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
