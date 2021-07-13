<?php

namespace Dintero\Checkout\Block;

use Dintero\Checkout\Helper\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class PaymentLink
 *
 * @package Dintero\Checkout\Block
 */
class ProductPagePaymentLink extends Template
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @param Context           $context
     * @param Config              $configHelper
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Config $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->configHelper->isProductPagePaymentButtonEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getExpressCheckoutUrl()
    {
        return $this->getUrl('dintero/checkout/express');
    }

    /**
     * @return string
     */
    public function getButtonImageUrl()
    {
        return $this->getViewFileUrl($this->configHelper->getExpressButtonImage());
    }
}
