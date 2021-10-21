<?php

namespace Magezil\BuyList\Api\Data;

interface BuyListItemInterface
{
    public const ID = 'entity_id';
    public const BUY_LIST_ID = 'buy_list_id';
    public const PRODUCT_ID = 'product_id';
    public const QTY = 'qty';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return integer|null
     */
    public function getId(): ?int;

    /**
     * @param mixed $value
     * @return $this
     */
    public function setId($value);

    /**
     * @return integer|null
     */
    public function getBuyListId(): ?int;

    /**
     * @param integer $buyListId
     * @return self
     */
    public function setBuyListId(int $buyListId): self;

    /**
     * @return integer|null
     */
    public function getProductId(): ?int;

    /**
     * @param integer $productId
     * @return self
     */
    public function setProductId(int $productId): self;

    /**
     * @return float|null
     */
    public function getQty(): ?float;

    /**
     * @param float $qty
     * @return self
     */
    public function setQty(float $qty): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param string $createdAt
     * @return self
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param string $updatedAt
     * @return self
     */
    public function setUpdatedAt(string $updatedAt): self;
}
