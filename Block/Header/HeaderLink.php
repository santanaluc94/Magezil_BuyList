<?php

namespace Magezil\BuyList\Block\Header;

use Magento\Framework\View\Element\Html\Link;
use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\View\Element\Template\Context;
use Magezil\BuyList\Model\Source\Config\Settings;
use Magento\Customer\Model\SessionFactory;

class HeaderLink extends Link implements SortLinkInterface
{
    protected $_template = 'Magezil_BuyList::header/link.phtml';
    private SessionFactory $sessionFactory;
    private Settings $settings;

    public function __construct(
        Context $context,
        SessionFactory $sessionFactory,
        Settings $settings,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sessionFactory = $sessionFactory;
        $this->settings = $settings;
    }

    protected function getCustomerGroupId(): ?int
    {
        return $this->sessionFactory->create()
            ->getCustomer()
            ->getGroupId();
    }

    protected function _toHtml(): string
    {
        if (
            !$this->settings->isModuleEnable() ||
            !$this->settings->isCustomerGroupIdAvailable($this->getCustomerGroupId())
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getHref(): string
    {
        return $this->getUrl('buy_list/lists/listing');
    }

    public function getLabel(): string
    {
        return __('My Buy Lists');
    }

    public function getSortOrder(): int
    {
        return $this->getData(self::SORT_ORDER);
    }
}
