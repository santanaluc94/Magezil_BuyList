<?php

namespace Magezil\BuyList\Service;

use Magezil\BuyList\Api\BuyListItemServiceInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\CollectionFactory as BuyListItemCollectionFactory;
use Magezil\BuyList\Model\BuyListItemFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\Collection as BuyListItemCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

class BuyListItemService implements BuyListItemServiceInterface
{
    protected BuyListItemRepositoryInterface $buyListItemRepository;
    protected Settings $buyListSettings;
    protected BuyListItemCollectionFactory $buyListItemCollectionFactory;
    protected BuyListItemFactory $buyListItemFactory;
    protected ProductRepositoryInterface $productRepository;
    protected BuyListRepositoryInterface $buyListRepository;
    protected ManagerInterface $eventManager;

    public function __construct(
        BuyListItemRepositoryInterface $buyListItemRepository,
        Settings $buyListSettings,
        BuyListItemCollectionFactory $buyListItemCollectionFactory,
        BuyListItemFactory $buyListItemFactory,
        ProductRepositoryInterface $productRepository,
        BuyListRepositoryInterface $buyListRepository,
        ManagerInterface $eventManager
    ) {
        $this->buyListItemRepository = $buyListItemRepository;
        $this->buyListSettings = $buyListSettings;
        $this->buyListItemCollectionFactory = $buyListItemCollectionFactory;
        $this->buyListItemFactory = $buyListItemFactory;
        $this->productRepository = $productRepository;
        $this->buyListRepository = $buyListRepository;
        $this->eventManager = $eventManager;
    }

    /**
     * @param integer $id
     * @return BuyListItemInterface
     */
    public function get(int $id): BuyListItemInterface
    {
        return $this->buyListItemRepository->getById($id);
    }

    /**
     * @param integer $buyListId
     * @param BuyListItemInterface $item
     * @return BuyListItemInterface
     */
    public function saveItem(
        int $buyListId,
        BuyListItemInterface $item
    ): BuyListItemInterface {
        if (!$this->isBuyListEnabled($buyListId)) {
            throw new ValidatorException(
                __('It is not possible to add items to the buy list because this buy list is disabled.')
            );
        }

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
            'buy_list_item_api_save_before',
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
            'buy_list_item_api_save_after',
            ['buyList' => $buyListItem]
        );

        return $buyListItem;
    }

    /**
     * @param integer $buyListId
     * @param integer $id
     * @return string
     */
    public function remove(int $buyListId, int $id): string
    {
        $buyListItem = $this->buyListItemRepository->getById($id);

        $this->eventManager->dispatch(
            'buy_list_item_api_remove_before',
            ['buyList' => $buyListItem]
        );

        $this->buyListItemRepository->deleteById($id);

        $this->eventManager->dispatch('buy_list_item_api_remove_after');

        return __(
            'The item with ID %1 has been removed from buy list with ID %2.',
            $id,
            $buyListId
        );
    }

    protected function isValidProductId(int $productId): bool
    {
        $product = $this->productRepository->getById($productId);

        if (!$product->getId()) {
            return false;
        }

        return true;
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
}
