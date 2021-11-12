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
        $customerId = $this->customerSession->create()
            ->getCustomer()
            ->getId();

        return $this->buyListCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);
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

    protected function _prepareLayout(): self
    {
        $pageSize = $this->getRequest()->getParam('limit') ?
            $this->getRequest()->getParam('limit') :
            1;
        $page = $this->getRequest()->getParam('p') ?
            $this->getRequest()->getParam('p') :
            1;

        $buyListCollection = $this->getBuyLists()
            ->setPageSize($pageSize)
            ->setCurPage($page);

        if ($this->hasBuyLists()) {
            $this->setChild(
                'pager',
                $this->getLayout()
                    ->createBlock(Pager::class, 'buy_list_listing_pager')
                    ->setAvailableLimit([5 => 5, 10 => 10, 15 => 15])
                    ->setShowPerPage(true)
                    ->setCollection($buyListCollection)
            );
        }

        return $this;
    }

    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }
}
