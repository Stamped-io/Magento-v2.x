<?php

namespace Dintero\Checkout\Api\Data\Shipping;

/**
 * Interface ShippingOptionsResponse
 *
 * @package Dintero\Checkout\Api\Data
 */
interface ResponseInterface
{
    /**
     * @return \Dintero\Checkout\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingOptions();

    /**
     * @return \Dintero\Checkout\Api\Data\OrderInterface
     */
    public function getOrder();
}
