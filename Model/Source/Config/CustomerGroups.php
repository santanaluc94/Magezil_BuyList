<?php

namespace Magezil\BuyList\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;

class CustomerGroups implements OptionSourceInterface
{
    private CustomerGroupCollectionFactory $customerGroupCollectionFactory;

    public function __construct(
        CustomerGroupCollectionFactory $customerGroupCollectionFactory
    ) {
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
    }

    public function toOptionArray(): array
    {
        return $this->customerGroupCollectionFactory->create()
            ->toOptionArray();
    }
}
