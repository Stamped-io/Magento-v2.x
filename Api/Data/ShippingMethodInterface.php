<?php

namespace Dintero\Checkout\Api\Data;

/**
 * Interface ShippingMethodInterface
 *
 * @package Dintero\Checkout\Api\Data
 */
interface ShippingMethodInterface
{
    /*
     * Delivery
     */
    const DELIVERY_METHOD_DELIVERY = 'delivery';

    /*
     * In-Store pickup
     */
    const DELIVERY_METHOD_PICKUP = 'pick_up';

    /*
     * None
     */
    const DELIVERY_METHOD_NONE = 'none';

    /*
     * ID
     */
    const ID = 'id';

    /*
     * Line Id
     */
    const LINE_ID = 'line_id';

    /*
     * Countries
     */
    const COUNTRIES = 'countries';

    /*
     * Amount
     */
    const AMOUNT = 'amount';

    /*
     * VAT amount
     */
    const VAT_AMOUNT = 'vat_amount';

    /*
     * VAT
     */
    const VAT = 'vat';

    /*
     * Title
     */
    const TITLE = 'title';

    /*
     * Description
     */
    const DESCRIPTION = 'description';

    /*
     * Delivery Method
     */
    const DELIVERY_METHOD = 'delivery_method';

    /*
     * Operator Product Id
     */
    const OPERATOR_PRODUCT_ID = 'operator_product_id';

    /*
     * Operator
     */
    const OPERATOR = 'operator';

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getLineId();

    /**
     * @return array
     */
    public function getCountries();

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @return float
     */
    public function getVatAmount();

    /**
     * @return float
     */
    public function getVat();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getDeliveryMethod();

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @return string
     */
    public function getOperatorProductId();

    /**
     * @param $id
     * @return ShippingMethodInterface
     */
    public function setId($id);

    /**
     * @param $lineId
     * @return ShippingMethodInterface
     */
    public function setLineId($lineId);

    /**
     * @param array $countries
     * @return ShippingMethodInterface
     */
    public function setCountries(array $countries);

    /**
     * @param float $amount
     * @return ShippingMethodInterface
     */
    public function setAmount($amount);

    /**
     * @param float $amount
     * @return ShippingMethodInterface
     */
    public function setVatAmount($amount);

    /**
     * @param float $amount
     * @return ShippingMethodInterface
     */
    public function setVat($amount);

    /**
     * @param string $title
     * @return ShippingMethodInterface
     */
    public function setTitle($title);

    /**
     * @param string $description
     * @return ShippingMethodInterface
     */
    public function setDescription($description);

    /**
     * @param string $deliveryMethod
     * @return ShippingMethodInterface
     */
    public function setDeliveryMethod($deliveryMethod);

    /**
     * @param string $operator
     * @return ShippingMethodInterface
     */
    public function setOperator($operator);

    /**
     * @param string $operatorProductId
     * @return ShippingMethodInterface
     */
    public function setOperatorProductId($operatorProductId);
}
