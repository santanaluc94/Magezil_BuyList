<?php

namespace Magezil\BuyList\Api\Data;

interface BuyListInterface
{
    public const ID = 'entity_id';
    public const CUSTOMER_ID = 'customer_id';
    public const TITLE = 'title';
    public const IS_ACTIVE = 'is_active';
    public const STORE_ID = 'store_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public function getId(): ?int;

    public function getCustomerId(): ?int;

    public function setCustomerId(?int $customerId): self;

    public function getTitle(): ?string;

    public function setTitle(?string $title): self;

    public function getIsActive(): ?bool;

    public function setIsActive(?bool $isActive): self;

    public function getStoreId(): ?int;

    public function setStoreId(?int $storeId): self;

    public function getCreatedAt(): ?string;

    public function setCreatedAt(?string $createdAt): self;

    public function getupdatedAt(): ?string;

    public function setupdatedAt(?string $updatedAt): self;
}
