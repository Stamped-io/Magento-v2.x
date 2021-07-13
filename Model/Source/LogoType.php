<?php

namespace Dintero\Checkout\Model\Source;

use Dintero\Checkout\Model\Dintero;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Dintero Logo Type
 */
class LogoType implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Dintero::ACTION_AUTHORIZE,
                'label' => __('Authorize Only'),
            ],
            [
                'value' => Dintero::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}
