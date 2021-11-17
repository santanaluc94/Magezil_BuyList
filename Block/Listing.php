<?php

namespace Magezil\BuyList\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Magezil\BuyList\Model\ResourceModel\BuyList\CollectionFactory as BuyListCollectionFactory;
use Magezil\BuyList\Model\ResourceModel\BuyList\Collection as BuyListCollection;
use Magento\Theme\Block\Html\Pager;

class Listing extends Template
{
    private const LIST_QTY = 4;
    private SessionFactory $customerSession;
    private BuyListCollectionFactory $buyListCollectionFactory;

    public function __construct(
        Context $context,
        SessionFactory $customerSession,
        BuyListCollectionFactory $buyListCollectionFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->buyListCollectionFactory = $buyListCollectionFactory;
    }

    public function hasBuyLists(): bool
    {
        return $this->getBuyLists()->getSize() > 0;
    }

    public function getBuyLists(): BuyListCollection
    {
        $pageSize = $this->getRequest()->getParam('limit') ?
            $this->getRequest()->getParam('limit') :
            self::LIST_QTY;
        $page = $this->getRequest()->getParam('p') ?
            $this->getRequest()->getParam('p') :
            1;
        $customerId = $this->customerSession->create()
            ->getCustomer()
            ->getId();

        return $this->buyListCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->setPageSize($pageSize)
            ->setCurPage($page);
    }

    public function formatActive(bool $isActive): string
    {
        return $isActive ? __('Active') : __('Inactive');
    }

    public function getUrlToView(int $buyListId): string
    {
        return $this->getUrl('buy_list/lists/view', ['id' => $buyListId]);
    }

    public function getUrlToReorder(int $buyListId): string
    {
        return $this->getUrl('buy_list/lists/reorderPost', ['id' => $buyListId]);
    }

    public function getMyBuyListsJsonData(): string
    {
        return json_encode($this->getBuyLists()->toArray()['items']);
    }

    protected function _prepareLayout(): self
    {
        $buyListCollection = $this->getBuyLists();

        if ($this->hasBuyLists()) {
            $pager = $this->getLayout()
                ->createBlock(Pager::class, 'buy.list.listing.pager')
                ->setAvailableLimit([
                    self::LIST_QTY => self::LIST_QTY,
                    self::LIST_QTY * 2 => self::LIST_QTY * 2,
                    self::LIST_QTY * 3 =>self::LIST_QTY * 3
                ])->setShowPerPage(true)
                ->setCollection($buyListCollection);
            $this->setChild('pager', $pager);
        }

        return $this;
    }

    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }
}
