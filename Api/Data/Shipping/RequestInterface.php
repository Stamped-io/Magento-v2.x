<?php
namespace Dintero\Checkout\Api\Data\Shipping;

/**
 * Interface RequestInterface
 *
 * @package Dintero\Checkout\Api\Data
 */
interface RequestInterface
{
    /**
     * @param string $accountId
     * @return RequestInterface
     */
    public function setAccountId($accountId);

    /**
     * @param string $sessionId
     * @return RequestInterface
     */
    public function setId($sessionId);

    /**
     * @param string $referenceId
     * @return RequestInterface
     */
    public function setMerchantReference($referenceId);

    /**
     * @param string[] $shippingData
     * @return RequestInterface
     */
    public function setShippingAddress($shippingData);

    /**
     * @param array $items
     * @return RequestInterface
     */
    public function setItems(array $items);

    /**
     * @return string
     */
    public function getAccountId();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getMerchantReference();

    /**
     * @return \Dintero\Checkout\Api\Data\AddressInterface
     */
    public function getShippingAddress();

    /**
     * @return \Dintero\Checkout\Api\Data\ItemInterface[]
     */
    public function getItems();

    /**
     * @param string $sessionId
     * @return bool
     */
    public function isValid($sessionId): bool;
}
