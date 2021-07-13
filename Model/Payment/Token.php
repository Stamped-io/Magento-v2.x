<?php

namespace Dintero\Checkout\Model\Payment;

/**
 * Class Token
 *
 * @package Dintero\Checkout\Model\Payment
 */
class Token
{
    /**
     * Access token
     *
     * @var $token string
     */
    private $token;

    /**
     * Token
     *
     * @var $expiration $token
     */
    private $expiration;

    /**
     * Token type
     *
     * @var $type string
     */
    private $type;

    /**
     * AuthToken constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->token = isset($data['access_token']) ? $data['access_token'] : null;
        $this->expiration = isset($data['expires_in']) ? $data['expires_in'] : null;
        $this->type = isset($data['token_type']) ? $data['token_type'] : null;
    }

    /**
     * Retrieving token
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Retrieving token type
     *
     * @return string|null
     */
    public function getTokenType()
    {
        return $this->type;
    }

    /**
     * Retrieving expires in value
     *
     * @return integer|null
     */
    public function getExpiresIn()
    {
        return $this->expiration;
    }
}
