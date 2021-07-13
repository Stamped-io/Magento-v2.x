<?php

namespace Dintero\Checkout\Block;

use Dintero\Checkout\Helper\Config;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Express
 *
 * @package Dintero\Checkout\Block
 */
class Express
    extends Template
    implements ShortcutInterface
{
    /**
     * @var Config $configHelper
     */
    protected $configHelper;

    /**
     * Express constructor.
     *
     * @param Template\Context $context
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'dintero.minicart.express';
    }

    /**
     * @return string
     */
    public function getButtonImageUrl()
    {
        return $this->getViewFileUrl($this->configHelper->getExpressButtonImage());
    }

    /**
     * Retrieving express checkout url
     *
     * @return string
     */
    public function getExpressCheckoutUrl()
    {
        return $this->getUrl('dintero/checkout/express');
    }
}
