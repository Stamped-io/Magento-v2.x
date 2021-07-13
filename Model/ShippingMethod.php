<?php

namespace Dintero\Checkout\Model;

use Dintero\Checkout\Api\Data\ShippingMethodInterface;

/**
 * Class ShippingMethod
 *
 * @package Dintero\Checkout\Model
 */
class ShippingMethod implements ShippingMethodInterface
{
    /**
     * @var \Magento\Framework\DataObject $dataObject
     */
    protected $dataObject;

    /**
     * ShippingMethod constructor.
     *
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(\Magento\Framework\DataObjectFactory $dataObjectFactory)
    {
        $this->dataObject = $dataObjectFactory->create();
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setData($key, $value)
    {
        $this->dataObject->setData($key, $value);
    }

    /**
     * @param string $key
     * @return array|mixed|null
     */
    public function getData($key)
    {
        return $this->dataObject->getData($key);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->dataObject->getData(self::AMOUNT);
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->getData(self::COUNTRIES);
    }

    /**
     * @return string
     */
    public function getDeliveryMethod()
    {
        return $this->getData(self::DELIVERY_METHOD);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getLineId()
    {
        return $this->getData(self::LINE_ID);
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->getData(self::OPERATOR);
    }

    /**
     * @return string
     */
    public function getOperatorProductId()
    {
        return $this->getData(self::OPERATOR_PRODUCT_ID);
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->getData(self::VAT);
    }

    /**
     * @return float
     */
    public function getVatAmount()
    {
        return $this->getData(self::VAT_AMOUNT);
    }

    /**
     * @param string $id
     * @return $this|ShippingMethodInterface
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * @param float $amount
     * @return $this|ShippingMethodInterface
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
        return $this;
    }

    /**
     * @param array $countries
     * @return $this|ShippingMethodInterface
     */
    public function setCountries(array $countries)
    {
        $this->setData(self::COUNTRIES, $countries);
        return $this;
    }

    /**
     * @param string $deliveryMethod
     * @return $this|ShippingMethodInterface
     */
    public function setDeliveryMethod($deliveryMethod)
    {
        $this->setData(self::DELIVERY_METHOD, $deliveryMethod);
        return $this;
    }

    /**
     * @param string $description
     * @return $this|ShippingMethodInterface
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
        return $this;
    }

    /**
     * @param string lineId
     * @return $this|ShippingMethodInterface
     */
    public function setLineId($lineId)
    {
        $this->setData(self::LINE_ID, $lineId);
        return $this;
    }

    /**
     * @param string $operator
     * @return $this|ShippingMethodInterface
     */
    public function setOperator($operator)
    {
        $this->setData(self::OPERATOR, $operator);
        return $this;
    }

    /**
     * @param string $operatorProductId
     * @return $this|ShippingMethodInterface
     */
    public function setOperatorProductId($operatorProductId)
    {
        $this->setData(self::OPERATOR_PRODUCT_ID, $operatorProductId);
        return $this;
    }

    /**
     * @param string $title
     * @return $this|ShippingMethodInterface
     */
    public function setTitle($title)
    {
        $this->setData(self::TITLE, $title);
        return $this;
    }

    /**
     * @param float $amount
     * @return $this|ShippingMethodInterface
     */
    public function setVat($amount)
    {
        $this->setData(self::VAT, $amount);
        return $this;
    }

    /**
     * @param float $amount
     * @return $this|ShippingMethodInterface
     */
    public function setVatAmount($amount)
    {
        $this->setData(self::VAT_AMOUNT, $amount);
        return $this;
    }
}
