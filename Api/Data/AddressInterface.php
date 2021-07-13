<?php

namespace Dintero\Checkout\Api\Data;

/**
 * Interface AddressInterface
 *
 * @package Dintero\Checkout\Api\Data
 */
interface AddressInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /*
     * First name
     */
    const FIRSTNAME = 'first_name';

    /*
     * Last name
     */
    const LASTNAME = 'last_name';

    /*
     * Address line
     */
    const ADDRESS_LINE = 'address_line';

    /*
     * Zip code
     */
    const ZIP = 'postal_code';

    /*
     * Postal place
     */
    const POSTAL_PLACE = 'postal_place';

    /*
     * ISO-2 Country code
     */
    const COUNTRY = 'country';

    /*
     * Phone number
     */
    const PHONE_NUMBER = 'phone_number';

    /*
     * Email
     */
    const EMAIL = 'email';

    /**
     * @param string $firstname
     * @return AddressInterface
     */
    public function setFirstName($firstname);

    /**
     * @param string $lastname
     * @return AddressInterface
     */
    public function setLastName($lastname);

    /**
     * @param string $address
     * @return AddressInterface
     */
    public function setAddressLine($address);

    /**
     * @param string $postalCode
     * @return AddressInterface
     */
    public function setPostalCode($postalCode);

    /**
     * @param string $postalPlace
     * @return AddressInterface
     */
    public function setPostalPlace($postalPlace);

    /**
     * @param string $countryCode
     * @return AddressInterface
     */
    public function setCountry($countryCode);

    /**
     * @param string $phone
     * @return AddressInterface
     */
    public function setPhoneNumber($phone);

    /**
     * @param string $email
     * @return AddressInterface
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @return string
     */
    public function getAddressLine();

    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @return string
     */
    public function getPostalPlace();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getPhoneNumber();

    /**
     * @return string
     */
    public function getEmail();
}
