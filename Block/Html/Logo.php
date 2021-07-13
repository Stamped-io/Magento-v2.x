<?php

namespace Dintero\Checkout\Block\Html;

use Dintero\Checkout\Helper\Config;
use Magento\Framework\View\Element\Template;

/**
 * Class Logo
 *
 * @package Dintero\Hp\Block
 */
class Logo extends Template
{

    /**
     * Config helper
     *
     * @var Config $configHelper
     */
    private $configHelper;

    /**
     * Logo constructor.
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
     * @inheritdoc
     */
    public function _toHtml()
    {
        if (!$this->configHelper->isActive()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Retrieving footer logo url
     *
     * @return string
     */
    public function getFooterLogoUrl()
    {
        return $this->configHelper->getFooterLogoUrl();
    }
}
