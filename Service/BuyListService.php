<?php

namespace Magezil\BuyList\Service;

use Magezil\BuyList\Service\AbstractBuyList;
use Magezil\BuyList\Api\BuyListServiceInterface;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magezil\BuyList\Api\Data\BuyListInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

class BuyListService extends AbstractBuyList implements BuyListServiceInterface
{
    public function __construct(
        Settings $buyListSettings,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemRepositoryInterface $buyListItemRepository,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        StoreRepositoryInterface $storeRepository,
        ManagerInterface $eventManager
    ) {
        parent::__construct(
            $buyListSettings,
            $buyListRepository,
            $buyListItemRepository,
            $productRepository,
            $customerRepository,
            $storeRepository,
            $eventManager
        );
    }

    /**
     * @param integer $id
     * @return BuyListInterface
     */
    public function get(int $id): BuyListInterface
    {
        return $this->buyListRepository->getById($id);
    }

    /**
     * @param BuyListInterface $buyList
     * @return BuyListInterface
     */
    public function create(BuyListInterface $buyList): BuyListInterface
    {
        $this->eventManager->dispatch(
            'buy_list_api_create_before',
            ['buyList' => $buyList]
        );

        $buyList = $this->prepareToCreateBuyListValid($buyList);

        $buyListCreated = $this->buyListRepository->save($buyList);

        $this->eventManager->dispatch(
            'buy_list_api_create_after',
            ['buyList' => $buyListCreated]
        );

        return $buyListCreated;
    }

    /**
     * @param BuyListInterface $buyList
     * @return BuyListInterface
     */
    public function update(BuyListInterface $buyList): BuyListInterface
    {
        $this->eventManager->dispatch(
            'buy_list_api_update_before',
            ['buyList' => $buyList]
        );

        $buyList = $this->prepareToUpdateBuyListValid($buyList);

        $buyListUpdated = $this->buyListRepository->save($buyList);

        $this->eventManager->dispatch(
            'buy_list_api_update_after',
            ['buyList' => $buyListUpdated]
        );

        return $this->buyListRepository->getById($buyListUpdated->getId());
    }

    /**
     * @param integer $id
     * @return string
     */
    public function remove(int $id): string
    {
        $buyList = $this->buyListRepository->getById($id);

        $this->eventManager->dispatch(
            'buy_list_api_remove_before',
            ['buyList' => $buyList]
        );

        if ($this->buyListSettings->isDeleteLists()) {
            $this->buyListRepository->deleteById($id);
            $this->eventManager->dispatch('buy_list_api_remove_after');

            return __('The buy list with ID %1 was removed.', $id);
        }

        $buyList->setIsActive(false);
        $this->buyListRepository->save($buyList);
        $this->eventManager->dispatch('buy_list_api_remove_after');

        return __('The buy list with ID %1 is disabled.', $id);
    }

    protected function prepareToCreateBuyListValid(
        BuyListInterface $buyList
    ): BuyListInterface {
        if (!$this->isValidCustomerId($buyList->getCustomerId())) {
            throw new ValidatorException(__('The customer with ID %1 does not exist.'));
        }

        if (!$this->isValidStoreId($buyList->getStoreId())) {
            throw new ValidatorException(__('The store with ID %1 does not exist.'));
        }

        $titleSanitized = filter_var($buyList->getTitle(), FILTER_SANITIZE_STRING);

        $buyList->setCustomerId((int) $buyList->getCustomerId());
        $buyList->setTitle($titleSanitized);
        $buyList->setIsActive((bool) $buyList->getIsActive());
        $buyList->setStoreId((int) $buyList->getStoreId());

        return $buyList;
    }

    protected function prepareToUpdateBuyListValid(
        BuyListInterface $buyList
    ): BuyListInterface {
        if (!$buyList->getId()) {
            throw new NoSuchEntityException(
                __('Unable to update an object without the field "%1"', BuyListInterface::ID)
            );
        }

        if (!is_null($buyList->getCustomerId())) {
            if (!$this->isValidCustomerId($buyList->getCustomerId())) {
                throw new ValidatorException(__('The customer with ID %1 does not exist.'));
            }

            $buyList->setCustomerId((int) $buyList->getCustomerId());
        }

        if (!is_null($buyList->getTitle())) {
            $titleSanitized = filter_var($buyList->getTitle(), FILTER_SANITIZE_STRING);
            $buyList->setTitle($titleSanitized);
        }

        if (!is_null($buyList->getIsActive())) {
            $buyList->setIsActive((bool) $buyList->getIsActive());
        }

        if (!is_null($buyList->getStoreId())) {
            if (!$this->isValidStoreId($buyList->getStoreId())) {
                throw new ValidatorException(__('The store with ID %1 does not exist.'));
            }

            $buyList->setStoreId((int) $buyList->getStoreId());
        }

        return $buyList;
    }
}
