<?php

namespace Dintero\Checkout\Model;

/**
 * Class AddressMapper
 *
 * @package Dintero\Checkout\Model
 */
class AddressMapper
{

    /**
     * @var \Magento\Quote\Model\Quote\Address $address
     */
    protected $address;

    /**
     * @var \Magento\Framework\DataObject $dataObject
     */
    protected $dataObject;

    /**
     * @var string[]
     */
    protected $fieldsMap = [
        'first_name' => 'firstname',
        'last_name'  => 'lastname',
        'email'  => 'email',
        'country'   => 'country_id',
        'postal_place'   => 'city',
        'address_line'   => 'street',
        'postal_code'   => 'postcode',
        'phone_number'   => 'telephone',
    ];

    /**
     * AddressMapper constructor.
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Framework\DataObject $dataObject
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Framework\DataObject $dataObject
    ) {
        $this->address = $address;
        $this->dataObject = $dataObject;
    }

    /**
     * Mapping dintero address fields to magento address fields
     */
    public function map()
    {
        $type = $this->resolveType();
        foreach ($this->fieldsMap as $dinteroField => $magentoField) {
            if ($value = $this->dataObject->getDataByPath(sprintf('%s/%s', $type, $dinteroField))) {
                $this->address->setData($magentoField, $value);
            }
        }
    }

    /**
     * @return string
     */
    protected function resolveType()
    {
        return $this->address->getAddressType() == \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING ?
            'shipping_address' : 'billing_address';
    }
}
