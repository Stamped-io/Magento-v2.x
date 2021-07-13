<?php

namespace Dintero\Checkout\Model;

use Dintero\Checkout\Api\Data\SessionInterface;

/**
 * Class Session
 *
 * @package Dintero\Checkout\Model
 */
class Session implements SessionInterface
{
    /**
     * @var string|null $id
     */
    protected $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this|SessionInterface
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
