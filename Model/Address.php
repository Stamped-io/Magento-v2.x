<?php

namespace Dintero\Checkout\Model;

/**
 * Class Address
 *
 * @package Dintero\Checkout\Model
 */
class Address implements \Dintero\Checkout\Api\Data\AddressInterface
{
    /**
     * @var \Magento\Framework\DataObject $dataObject
     */
    protected $dataObject;

    /**
     * Address constructor.
     *
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     */
    public function __construct(\Magento\Framework\DataObjectFactory $objectFactory)
    {
        $this->dataObject = $objectFactory->create();
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
     * @param string $key
     * @param mixed $value
     * @return \Magento\Framework\DataObject
     */
    public function setData($key, $value)
    {
        return $this->dataObject->setData($key, $value);
    }

    /**
     * @param string $firstname
     * @return $this|\Dintero\Checkout\Api\Data\AddressInterface
     */
    public function setFirstName($firstname)
    {
        $this->setData(self::FIRSTNAME, $firstname);
        return $this;
    }

    /**
     * @param string $lastname
     * @return $this|\Dintero\Checkout\Api\Data\AddressInterface
     */
    public function setLastName($lastname)
    {
        $this->setData(self::LASTNAME, $lastname);
        return $this;
    }

    /**
     * @param string $address
     * @return $this|\Dintero\Checkout\Api\Data\AddressInterface
     */
    public function setAddressLine($address)
    {
        $this->setData(self::ADDRESS_LINE, $address);
        return $this;
    }

    /**
     * @param string $countryCode
     * @return $this|\Dintero\Checkout\Api\Data\AddressInterface
     */
    public function setCountry($countryCode)
    {
        $this->setData(self::COUNTRY, $countryCode);
        return $this;
    }

    /**
     * @param string $postalCode
     * @return $this|\Dintero\Checkout\Api\Data\AddressInterface
     */
    public function setPostalCode($postalCode)
    {
        $this->setData(self::ZIP, $postalCode);
        return $this;
    }

    /**
     * @param string $postalPlace
     * @return $this|\Dintero\Checkout\Api\Data\AddressInterface
     */
    public function setPostalPlace($postalPlace)
    {
        $this->setData(self::POSTAL_PLACE, $postalPlace);
        return $this;
    }

    /**
     * @param string $phone
     * @return $this|\Dintero\Checkout\Api\Data\AddressInterface
     */
    public function setPhoneNumber($phone)
    {
        $this->setData(self::PHONE_NUMBER, $phone);
        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->setData(self::EMAIL, $email);
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * @return string
     */
    public function getAddressLine()
    {
        return $this->getData(self::ADDRESS_LINE);
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->getData(self::PHONE_NUMBER);
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->getData(self::ZIP);
    }

    /**
     * @return string
     */
    public function getPostalPlace()
    {
        return $this->getData(self::POSTAL_PLACE);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }
}
