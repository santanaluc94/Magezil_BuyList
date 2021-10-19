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

    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * @param mixed $value
     * @return $this
     */
    public function setId($value);

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * @param int $customerId
     * @return self
     */
    public function setCustomerId(int $customerId): self;

    /**
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * @return bool|null
     */
    public function getIsActive(): ?bool;

    /**
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self;

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int $storeId
     * @return self
     */
    public function setStoreId(int $storeId): self;

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
