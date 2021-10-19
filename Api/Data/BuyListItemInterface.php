<?php

namespace Magezil\BuyList\Api\Data;

interface BuyListItemInterface
{
    public const ID = 'entity_id';
    public const BUY_LIST_ID = 'buy_list_id';
    public const PRODUCT_ID = 'product_id';
    public const QTY = 'qty';
    public const STORE_ID = 'store_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public function getId(): ?int;

    public function getBuyListId(): ?int;

    public function setBuyListId(?int $buyListId): self;

    public function getProductId(): ?int;

    public function setProductId(?int $productId): self;

    public function getQty(): ?float;

    public function setQty(?float $qty): self;

    public function getStoreId(): ?int;

    public function setStoreId(?int $storeId): self;

    public function getCreatedAt(): ?string;

    public function setCreatedAt(?string $createdAt): self;

    public function getUpdatedAt(): ?string;

    public function setUpdatedAt(?string $updatedAt): self;
}
