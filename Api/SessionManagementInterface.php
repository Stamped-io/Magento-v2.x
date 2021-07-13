<?php

namespace Dintero\Checkout\Api;

/**
 * Interface SessionManagementInterface
 *
 * @package Dintero\Checkout\Api
 */
interface SessionManagementInterface
{
    /**
     * @return \Dintero\Checkout\Api\Data\SessionInterface
     */
    public function getSession();
}
