<?php

namespace Magezil\BuyList\Model;

use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Model\BuyListFactory;
use Magezil\BuyList\Model\ResourceModel\BuyList as ResourceModelBuyList;
use Magezil\BuyList\Model\Source\Config\Settings;
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
        ResourceModelBuyList $resourceModelBuyList,
        Settings $buyListSettings
    ) {
        $this->buyListFactory = $buyListFactory;
        $this->resourceModelBuyList = $resourceModelBuyList;
        $this->buyListSettings = $buyListSettings;
    }

    /**
     * @param integer $id
     * @return BuyListInterface
     */
    public function getById(int $id): BuyListInterface
    {
        /** @var BuyList $buyList */
        $buyList = $this->buyListFactory->create();
        $this->resourceModelBuyList->load($buyList, $id);

        if (!$buyList->getId()) {
            throw NoSuchEntityException::singleField(BuyList::ID, $id);
        }

        return $buyList;
    }

    /**
     * @param BuyListInterface $buyList
     * @return BuyListInterface
     */
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

    /**
     * @param BuyListInterface $buyList
     * @return boolean
     */
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

    /**
     * @param integer $id
     * @return boolean
     */
    public function deleteById(int $id): bool
    {
        $buyList = $this->getById($id);
        return $this->delete($buyList);
    }

    /**
     * @param BuyListInterface $buyList
     * @return BuyListInterface
     */
    public function update(BuyListInterface $buyList): BuyListInterface
    {
        if (!$buyList->getId()) {
            throw new NoSuchEntityException(
                __('Unable to update an object without the field "%1"', BuyListInterface::ID)
            );
        }

        $buyListUpdated = $this->save($buyList);
        return $this->getById($buyListUpdated->getId());
    }

    /**
     * @param integer $id
     * @return string
     */
    public function remove(int $id): string
    {
        if ($this->buyListSettings->isDeleteLists()) {
            $this->deleteById($id);
            return 'The buy list with ID %1 was removed.';
        }

        $buyList = $this->getById($id);
        $buyList->setIsActive(false);
        $this->save($buyList);
        return 'The buy list with ID %1 is disabled.';
    }
}
