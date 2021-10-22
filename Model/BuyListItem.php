<?php

namespace Magezil\BuyList\Model;

use Magento\Framework\Model\AbstractModel;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem as ResourceModelBuyListItem;

class BuyListItem extends AbstractModel implements BuyListItemInterface
{
    protected $_cacheTag = 'magezil_buy_list_item';
    protected $_eventPrefix = 'magezil_buy_list_item';

    protected function _construct(): void
    {
        $this->_init(ResourceModelBuyListItem::class);
    }

    /**
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::ID);
    }

    /**
     * @return integer|null
     */
    public function getBuyListId(): ?int
    {
        return $this->getData(self::BUY_LIST_ID);
    }

    /**
     * @param integer $buyListId
     * @return self
     */
    public function setBuyListId(?int $buyListId): self
    {
        return $this->setData(self::BUY_LIST_ID, $buyListId);
    }

    /**
     * @return integer|null
     */
    public function getProductId(): ?int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param integer $productId
     * @return self
     */
    public function setProductId(?int $productId): self
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return float|null
     */
    public function getQty(): ?float
    {
        return $this->getData(self::QTY);
    }

    /**
     * @param float $qty
     * @return self
     */
    public function setQty(?float $qty): self
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return self
     */
    public function setCreatedAt(?string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return self
     */
    public function setUpdatedAt(?string $updatedAt): self
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
