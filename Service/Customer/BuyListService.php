<?php

namespace Magezil\BuyList\Service\Customer;

use Magezil\BuyList\Api\CustomerBuyListServiceInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Api\Data\BuyListInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;

class BuyListService implements CustomerBuyListServiceInterface
{
    protected BuyListRepositoryInterface $buyListRepository;
    protected ManagerInterface $eventManager;
    protected StoreRepositoryInterface $storeRepository;
    protected Settings $buyListSettings;

    public function __construct(
        BuyListRepositoryInterface $buyListRepository,
        ManagerInterface $eventManager,
        StoreRepositoryInterface $storeRepository,
        Settings $buyListSettings
    ) {
        $this->buyListRepository = $buyListRepository;
        $this->eventManager = $eventManager;
        $this->storeRepository = $storeRepository;
        $this->buyListSettings = $buyListSettings;
    }

    /**
     * @param integer $id
     * @param integer $customerId
     * @return BuyListInterface
     */
    public function get(int $id, int $customerId): BuyListInterface
    {
        if (!isset($customerId)) {
            throw new AuthorizationException(__('This API can only be accessed by a logged customer.'));
        }

        $buyList = $this->buyListRepository->getById($id);

        if ($buyList->getCustomerId() !== $customerId) {
            throw new NoSuchEntityException(
                __(
                    'The buy list with ID %1 does not belong to the logged in customer.',
                    BuyListInterface::ID
                )
            );
        }

        return $buyList;
    }

    /**
     * @param BuyListInterface $buyList
     * @param integer $customerId
     * @return BuyListInterface
     */
    public function create(BuyListInterface $buyList, int $customerId): BuyListInterface
    {
        if (!isset($customerId)) {
            throw new AuthorizationException(__('This API can only be accessed by a logged customer.'));
        }

        $this->eventManager->dispatch(
            'buy_list_api_customer_create_before',
            ['buyList' => $buyList]
        );

        $buyList = $this->prepareToCreateBuyListValid($buyList, $customerId);

        $buyListCreated = $this->buyListRepository->save($buyList);

        $this->eventManager->dispatch(
            'buy_list_api_customer_create_after',
            ['buyList' => $buyListCreated]
        );

        return $buyListCreated;
    }

    /**
     * @param BuyListInterface $buyList
     * @param integer $customerId
     * @return BuyListInterface
     */
    public function update(BuyListInterface $buyList, int $customerId): BuyListInterface
    {
        if (!isset($customerId)) {
            throw new AuthorizationException(__('This API can only be accessed by a logged customer.'));
        }

        if (!$buyList->getId()) {
            throw new NoSuchEntityException(
                __('Unable to update an object without the field "%1"', BuyListInterface::ID)
            );
        }

        $oldBuyList = $this->get($buyList->getId(), $customerId);

        if ($oldBuyList->getCustomerId() !== $customerId) {
            throw new NoSuchEntityException(
                __(
                    'The buy list with ID %1 does not belong to the logged in customer.',
                    BuyListInterface::ID
                )
            );
        }

        $this->eventManager->dispatch(
            'buy_list_api_customer_update_before',
            ['buyList' => $buyList]
        );

        $buyList = $this->prepareToUpdateBuyListValid($buyList);

        $buyListUpdated = $this->buyListRepository->save($buyList);

        $this->eventManager->dispatch(
            'buy_list_api_customer_update_after',
            ['buyList' => $buyListUpdated]
        );

        return $this->buyListRepository->getById($buyListUpdated->getId());
    }

    /**
     * @param integer $id
     * @param integer $customerId
     * @return string
     */
    public function remove(int $id, int $customerId): string
    {
        if (!isset($customerId)) {
            throw new AuthorizationException(__('This API can only be accessed by a logged customer.'));
        }

        $buyList = $this->buyListRepository->getById($id);

        if ($buyList->getCustomerId() !== $customerId) {
            throw new NoSuchEntityException(
                __(
                    'The buy list with ID %1 does not belong to the logged in customer.',
                    BuyListInterface::ID
                )
            );
        }

        $this->eventManager->dispatch(
            'buy_list_api_customer_remove_before',
            ['buyList' => $buyList]
        );

        if ($this->buyListSettings->isDeleteLists()) {
            $this->buyListRepository->deleteById($id);
            $this->eventManager->dispatch('buy_list_api_customer_remove_after');

            return __('The buy list with ID %1 was removed.', $id);
        }

        $buyList->setIsActive(false);
        $this->buyListRepository->save($buyList);
        $this->eventManager->dispatch('buy_list_api_customer_remove_after');

        return __('The buy list with ID %1 is disabled.', $id);
    }

    protected function prepareToCreateBuyListValid(
        BuyListInterface $buyList,
        int $customerId
    ): BuyListInterface {
        if (!$this->isValidStoreId($buyList->getStoreId())) {
            throw new ValidatorException(__('The store with ID %1 does not exist.'));
        }

        $titleSanitized = filter_var($buyList->getTitle(), FILTER_SANITIZE_STRING);

        $buyList->setCustomerId((int) $customerId);
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

    protected function isValidStoreId(int $storeId): bool
    {
        $store = $this->storeRepository->getById($storeId);

        if (!$store->getId()) {
            return false;
        }

        return true;
    }
}
