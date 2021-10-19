<?php

namespace Magezil\BuyList\Model;

use Magento\Framework\Model\AbstractModel;
use Magezil\BuyList\Api\Data\BuyListInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magezil\BuyList\Model\ResourceModel\BuyList as ResourceModelBuyList;

class BuyList extends AbstractModel implements BuyListInterface
{
    protected $_cacheTag = 'magezil_buy_list';
    protected $_eventPrefix = 'magezil_buy_list';

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

    public function getCustomerId(): ?int
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId(?int $customerId): self
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle(?string $title): self
    {
        return $this->setData(self::TITLE, $title);
    }

    public function getIsActive(): ?bool
    {
        return $this->getData(self::IS_ACTIVE);
    }

    public function setIsActive(?bool $isActive): self
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
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
