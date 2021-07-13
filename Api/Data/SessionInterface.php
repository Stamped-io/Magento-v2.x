<?php

namespace Dintero\Checkout\Api\Data;

/**
 * Interface SessionInterface
 *
 * @package Dintero\Checkout\Api
 */
interface SessionInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     * @return \Dintero\Checkout\Api\Data\SessionInterface
     */
    public function setId($id);
}
