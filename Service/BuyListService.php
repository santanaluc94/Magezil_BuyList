<?php

namespace Magezil\BuyList\Service;

use Magezil\BuyList\Api\BuyListServiceInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Framework\Event\ManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magezil\BuyList\Api\Data\BuyListInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

class BuyListService implements BuyListServiceInterface
{
    protected BuyListRepositoryInterface $buyListRepository;
    protected Settings $buyListSettings;
    protected ManagerInterface $eventManager;
    protected CustomerRepositoryInterface $customerRepository;
    protected StoreRepositoryInterface $storeRepository;

    public function __construct(
        BuyListRepositoryInterface $buyListRepository,
        Settings $buyListSettings,
        ManagerInterface $eventManager,
        CustomerRepositoryInterface $customerRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->buyListRepository = $buyListRepository;
        $this->buyListSettings = $buyListSettings;
        $this->eventManager = $eventManager;
        $this->customerRepository = $customerRepository;
        $this->storeRepository = $storeRepository;
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

            return 'The buy list with ID %1 was removed.';
        }

        $buyList->setIsActive(false);
        $this->buyListRepository->save($buyList);
        $this->eventManager->dispatch('buy_list_api_remove_after');

        return 'The buy list with ID %1 is disabled.';
    }

    protected function prepareToCreateBuyListValid(
        BuyListInterface $buyList
    ): BuyListInterface {
        $this->isValidCustomerId($buyList->getCustomerId());
        $this->isValidStoreId($buyList->getStoreId());

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
            $this->isValidCustomerId($buyList->getCustomerId());
            $buyList->setCustomerId((int) $buyList->getCustomerId());
        }

        if (!is_null($buyList->setTitle($buyList->getTitle()))) {
            $titleSanitized = filter_var($buyList->getTitle(), FILTER_SANITIZE_STRING);
            $buyList->setTitle($titleSanitized);
        }

        if (!is_null($buyList->getIsActive())) {
            $buyList->setIsActive((bool) $buyList->getIsActive());
        }

        if (!is_null($buyList->getStoreId())) {
            $this->isValidStoreId($buyList->getStoreId());
            $buyList->setStoreId((int) $buyList->getStoreId());
        }

        return $buyList;
    }

    protected function isValidCustomerId(int $customerId): void
    {
        $customer = $this->customerRepository->getById($customerId);

        if (!$customer->getId()) {
            throw new ValidatorException(__('The customer with ID %1 does not exist.'));
        }
    }

    protected function isValidStoreId(int $storeId): void
    {
        $store = $this->storeRepository->getById($storeId);

        if (!$store->getId()) {
            throw new ValidatorException(__('The store with ID %1 does not exist.'));
        }
    }
}
