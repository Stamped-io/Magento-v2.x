<?php

namespace Dintero\Checkout\Api;

/**
 * Interface ExpressCallbackInterface
 * @package Dintero\Checkout\Api
 */
interface EmbeddedCallbackInterface
{
    /**
     * @return mixed
     */
    public function execute();
}
