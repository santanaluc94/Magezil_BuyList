<?php

namespace Magezil\BuyList\Service;

use Magezil\BuyList\Service\AbstractBuyList;
use Magezil\BuyList\Api\BuyListItemServiceInterface;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magezil\BuyList\Api\BuyListRepositoryInterface;
use Magezil\BuyList\Api\BuyListItemRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\CollectionFactory as BuyListItemCollectionFactory;
use Magezil\BuyList\Model\BuyListItemFactory;
use Magezil\BuyList\Api\Data\BuyListItemInterface;
use Magezil\BuyList\Model\ResourceModel\BuyListItem\Collection as BuyListItemCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

class BuyListItemService extends AbstractBuyList implements BuyListItemServiceInterface
{
    private BuyListItemCollectionFactory $buyListItemCollectionFactory;
    private BuyListItemFactory $buyListItemFactory;

    public function __construct(
        Settings $buyListSettings,
        BuyListRepositoryInterface $buyListRepository,
        BuyListItemRepositoryInterface $buyListItemRepository,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        StoreRepositoryInterface $storeRepository,
        ManagerInterface $eventManager,
        BuyListItemCollectionFactory $buyListItemCollectionFactory,
        BuyListItemFactory $buyListItemFactory
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
        $this->buyListItemCollectionFactory = $buyListItemCollectionFactory;
        $this->buyListItemFactory = $buyListItemFactory;
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
                __(
                    'This buy list is disabled, it is not possible to add or update items.'
                )
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

        $this->eventManager->dispatch(
            'buy_list_item_api_save_before',
            ['$item' => $item]
        );

        /** @var BuyListItemInterface $item */
        if ($item->getId()) {
            $buyListItem = $this->buyListItemRepository->getById($item->getId());

            if ($item->getProductId() && !$this->isValidProductId($item->getProductId())) {
                throw new NoSuchEntityException(__('The product with ID %1 does not exist.'));
            }
        } else {
            $buyListItem = $this->buyListItemFactory->create();
        }

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

        if ($buyListItem->getBuyListId() !== $buyListId) {
            throw new ValidatorException(__(
                'The item with ID %1 does not belong to the buy list with ID %2.',
                $id,
                $buyListId
            ));
        }

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
}
