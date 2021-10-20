<?php

namespace Magezil\BuyList\Model;

use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Model\BuyListFactory;
use Magezil\BuyList\Model\ResourceModel\BuyList as ResourceModelBuyList;
use Magezil\BuyList\Api\Data\BuyListInterface;
use Magezil\BuyList\Model\BuyList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class BuyListRepository implements BuyListRepositoryInterface
{
    private BuyListFactory $buyListFactory;
    private ResourceModelBuyList $resourceModelBuyList;

    public function __construct(
        BuyListFactory $buyListFactory,
        ResourceModelBuyList $resourceModelBuyList
    ) {
        $this->buyListFactory = $buyListFactory;
        $this->resourceModelBuyList = $resourceModelBuyList;
    }

    public function getById(int $id): BuyListInterface
    {
        /** @var BuyList $buyList */
        $buyList = $this->buyListFactory->create();
        $this->resourceModelBuyList->load($buyList, $id);

        if (!$buyList->getId()) {
            throw NoSuchEntityException::singleField(BuyListInterface::ID, $id);
        }

        return $buyList;
    }

    public function save(BuyListInterface $buyList): BuyListInterface
    {
        try {
            /** @var BuyList $buyList */
            $buyList->getResource()->save($buyList);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Unable to save object. Error: %1', $exception->getMessage())
            );
        }

        return $buyList;
    }

    public function delete(BuyListInterface $buyList): bool
    {
        try {
            /** @var BuyList $buyList */
            $this->resourceModelBuyList->delete($buyList);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Unable to remove object with ID %1. Error: %2',
                $buyList->getId(),
                $exception->getMessage()
            ));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $buyList = $this->getById($id);
        return $this->delete($buyList);
    }
}
