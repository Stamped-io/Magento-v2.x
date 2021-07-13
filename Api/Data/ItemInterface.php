<?php

namespace Dintero\Checkout\Api\Data;

/**
 * Interface ItemInterface
 *
 * @package Dintero\Checkout\Api\Data
 */
interface ItemInterface
{
    /*
     * Id
     */
    const ID = 'id';

    /*
     * VAT
     */
    const VAT = 'vat';

    /*
     * Amount
     */
    const AMOUNT = 'amount';

    /*
     * Line ID
     */
    const LINE_ID = 'line_id';

    /*
     * Quantity
     */
    const QUANTITY = 'quantity';

    /*
     * VAT Amount
     */
    const VAT_AMOUNT = 'vat_amount';

    /*
     * Description
     */
    const DESCRIPTION = 'description';

    /**
     * @param string $id
     * @return ItemInterface
     */
    public function setId($id);

    /**
     * @param float $vat
     * @return ItemInterface
     */
    public function setVat($vat);

    /**
     * @param float $amount
     * @return ItemInterface
     */
    public function setAmount($amount);

    /**
     * @param string $lineId
     * @return ItemInterface
     */
    public function setLineId($lineId);

    /**
     * @param integer $qty
     * @return ItemInterface
     */
    public function setQuantity($qty);

    /**
     * @param float $amount
     * @return ItemInterface
     */
    public function setVatAmount($amount);

    /**
     * @param string $description
     * @return ItemInterface
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return float
     */
    public function getVat();

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @return string
     */
    public function getLineId();

    /**
     * @return integer
     */
    public function getQuantity();

    /**
     * @return float
     */
    public function getVatAmount();

    /**
     * @return string
     */
    public function getDescription();

}
