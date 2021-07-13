<?php

namespace Dintero\Checkout\Api\Data\Order;

/**
 * Interface ItemInterface
 *
 * @package Dintero\Checkout\Api\Data\Order
 */
interface ItemInterface
{
    /*
     * Item ID
     */
    const ID = 'id';

    /*
     * Line Id
     */
    const LINE_ID = 'line_id';

    /*
     * Quantity
     */
    const QTY = 'quantity';

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
     * Description
     */
    const DESCRIPTION = 'description';

    /**
     * @param string $id
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getId();


    /**
     * @param string $lineId
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setLineId($lineId);

    /**
     * @return string
     */
    public function getLineId();

    /**
     * @param float $qty
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setQuantity($qty);

    /**
     * @return float
     */
    public function getQuantity();

    /**
     * @param float $amount
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setAmount($amount);

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @param float $vat
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setVat($vat);

    /**
     * @return float
     */
    public function getVat();

    /**
     * @param float $amount
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setVatAmount($amount);

    /**
     * @return float
     */
    public function getVatAmount();

    /**
     * @param $descritpion
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setDescription($descritpion);

    /**
     * @return string
     */
    public function getDescription();
}
