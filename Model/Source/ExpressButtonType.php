<?php

namespace Dintero\Checkout\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Dintero Payment Action Dropdown source
 */
class ExpressButtonType implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'Dintero_Checkout::images/checkout-express-dark-round.svg',
                'label' => __('Dark Round'),
            ],
            [
                'value' => 'Dintero_Checkout::images/express-btn-dark-round-single-line.svg',
                'label' => __('Dark Round Single Line')
            ]
        ];
    }
}
