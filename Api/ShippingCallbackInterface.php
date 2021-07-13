<?php

namespace Dintero\Checkout\Api;

/**
 * Interface ShippingCallbackInterface
 *
 * @package Dintero\Checkout\Api
 */
interface ShippingCallbackInterface
{
    /**
     * @return \Dintero\Checkout\Api\Data\Shipping\ResponseInterface
     */
    public function getOptions();
}
