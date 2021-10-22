<?php

namespace Magezil\BuyList\Service\Customer;

use Magezil\BuyList\Api\CustomerBuyListItemServiceInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\CollectionFactory as BuyListItemCollectionFactory;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magezil\BuyList\Model\BuyListItemFactory;
use Magento\Framework\Event\ManagerInterface;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\ValidatorException;

class BuyListItemService implements CustomerBuyListItemServiceInterface
{
    protected BuyListItemRepositoryInterface $buyListItemRepository;
    protected BuyListRepositoryInterface $buyListRepository;
    protected BuyListItemCollectionFactory $buyListItemCollectionFactory;
    protected Settings $buyListSettings;
    protected ProductRepositoryInterface $productRepository;
    protected BuyListItemFactory $buyListItemFactory;
    protected ManagerInterface $eventManager;

    public function __construct(
        BuyListItemRepositoryInterface $buyListItemRepository,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemCollectionFactory $buyListItemCollectionFactory,
        Settings $buyListSettings,
        ProductRepositoryInterface $productRepository,
        BuyListItemFactory $buyListItemFactory,
        ManagerInterface $eventManager
    ) {
        $this->buyListItemRepository = $buyListItemRepository;
        $this->buyListRepository = $buyListRepository;
        $this->buyListItemCollectionFactory = $buyListItemCollectionFactory;
        $this->buyListSettings = $buyListSettings;
        $this->productRepository = $productRepository;
        $this->buyListItemFactory = $buyListItemFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * @param integer $id
     * @param integer $customerId
     * @return BuyListItemInterface
     */
    public function get(int $id, int $customerId): BuyListItemInterface
    {
        if (!isset($customerId)) {
            throw new AuthorizationException(__('This API can only be accessed by a logged customer.'));
        }

        $buyListItem = $this->buyListItemRepository->getById($id);

        $buyList = $this->buyListRepository->getById($buyListItem->getBuyListId());

        if ($buyList->getCustomerId() !== $customerId) {
            throw new NoSuchEntityException(
                __(
                    'The buy list with ID %1 does not belong to the logged in customer.',
                    $buyListItem->getBuyListId()
                )
            );
        }

        if (!$this->isBuyListEnabled($buyList->getId())) {
            throw new ValidatorException(
                __('It is not possible to add items to the buy list because this buy list is disabled.')
            );
        }

        return $buyListItem;
    }

    /**
     * @param integer $buyListId
     * @param BuyListItemInterface $item
     * @param integer $customerId
     * @return BuyListItemInterface
     */
    public function saveItem(
        int $buyListId,
        BuyListItemInterface $item,
        int $customerId
    ): BuyListItemInterface {
        if (!$this->isBuyListEnabled($buyListId)) {
            throw new ValidatorException(
                __('It is not possible to add items to the buy list because this buy list is disabled.')
            );
        }

        // if ($buyList->getCustomerId() !== $customerId) {
        //     throw new NoSuchEntityException(
        //         __(
        //             'The buy list with ID %1 does not belong to the logged in customer.',
        //             $buyListItem->getBuyListId()
        //         )
        //     );
        // }

        /** @var BuyListItemCollection $buyListItemCollection */
        $buyListItemCollection = $this->buyListItemCollectionFactory->create()
            ->addFieldToFilter(BuyListItemInterface::BUY_LIST_ID, $buyListId);

        if ($this->isListFull($buyListItemCollection->getSize())) {
            throw new ValidatorException(__(
                "It is not possible to add more items to the buy list. The maximum quantity of items per list is %1. This list already has %2 items.",
                $this->buyListSettings->getMaxQtyItems(),
                $buyListItemCollection->getSize()
            ));
        }

        if (!$this->isValidProductId($item->getProductId())) {
            throw new NoSuchEntityException(__('The product with ID %1 does not exist.'));
        }

        $this->eventManager->dispatch(
            'buy_list_item_api_customer_save_before',
            ['$item' => $item]
        );

        /** @var BuyListItemInterface $item */
        $buyListItem = $item->getId() ?
            $this->buyListItemRepository->getById($item->getId()) :
            $this->buyListItemFactory->create();

        $buyListItem->setBuyListId($buyListId);
        $buyListItem->setQty($item->getQty());
        $buyListItem->setProductId($item->getProductId());
        $buyListItem = $this->buyListItemRepository->save($buyListItem);

        $this->eventManager->dispatch(
            'buy_list_item_api_customer_save_after',
            ['buyList' => $buyListItem]
        );

        return $buyListItem;
    }

    /**
     * @param integer $buyListId
     * @param integer $id
     * @param integer $customerId
     * @return string
     */
    public function remove(int $buyListId, int $id, int $customerId): string
    {
        $buyListItem = $this->buyListItemRepository->getById($id);

        $this->eventManager->dispatch(
            'buy_list_item_api_customer_remove_before',
            ['buyList' => $buyListItem]
        );

        $buyList = $this->buyListRepository->getById($buyListItem->getBuyListId());

        if ($buyList->getCustomerId() !== $customerId) {
            throw new NoSuchEntityException(
                __(
                    'The buy list with ID %1 does not belong to the logged in customer.',
                    $buyListItem->getBuyListId()
                )
            );
        }

        $this->buyListItemRepository->deleteById($id);

        $this->eventManager->dispatch('buy_list_item_api_customer_remove_after');

        return __(
            'The item with ID %1 has been removed from buy list with ID %2.',
            $id,
            $buyListId
        );
    }

    protected function isBuyListEnabled(int $buyListId): bool
    {
        $buyList = $this->buyListRepository->getById($buyListId);

        if (!$buyList->getIsActive()) {
            return false;
        }

        return true;
    }

    protected function isListFull(int $buyListSize): bool
    {
        if (
            !empty($this->buyListSettings->getMaxQtyItems()) &&
            $this->buyListSettings->getMaxQtyItems() <= $buyListSize
        ) {
            return true;
        }

        return false;
    }

    protected function isValidProductId(int $productId): bool
    {
        $product = $this->productRepository->getById($productId);

        if (!$product->getId()) {
            return false;
        }

        return true;
    }
}
