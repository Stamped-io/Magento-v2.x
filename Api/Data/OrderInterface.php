<?php
namespace Dintero\Checkout\Api\Data;

/**
 * Interface OrderInterface
 *
 * @package Dintero\Checkout\Api\Data
 */
interface OrderInterface
{
    /*
     * Amount
     */
    const AMOUNT = 'amount';

    /*
     * Currency
     */
    const CURRENCY = 'currency';

    /*
     * VAT Amount
     */
    const VAT_AMOUNT = 'vat_amount';

    /*
     * Items
     */
    const ITEMS = 'items';

    /**
     * @param float $amount
     * @return \Dintero\Checkout\Api\Data\OrderInterface
     */
    public function setAmount($amount);

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @param string $currency
     * @return \Dintero\Checkout\Api\Data\OrderInterface
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param float $amount
     * @return \Dintero\Checkout\Api\Data\OrderInterface
     */
    public function setVatAmount($amount);

    /**
     * @return float
     */
    public function getVatAmount();

    /**
     * @param array $items
     * @return \Dintero\Checkout\Api\Data\OrderInterface
     */
    public function setItems($items);

    /**
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface[]
     */
    public function getItems();
}
