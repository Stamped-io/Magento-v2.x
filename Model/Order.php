<?php

namespace Dintero\Checkout\Model;

/**
 * Class Order
 *
 * @package Dintero\Checkout\Model
 */
class Order
    extends \Magento\Framework\DataObject
    implements \Dintero\Checkout\Api\Data\OrderInterface
{
    /**
     * @param float $amount
     * @return \Dintero\Checkout\Api\Data\OrderInterface|Order
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @param float $amount
     * @return \Dintero\Checkout\Api\Data\OrderInterface|Order
     */
    public function setVatAmount($amount)
    {
        return $this->setData(self::VAT_AMOUNT, $amount);
    }

    /**
     * @return float
     */
    public function getVatAmount()
    {
        return $this->getData(self::VAT_AMOUNT);
    }

    /**
     * @param string $currency
     * @return \Dintero\Checkout\Api\Data\OrderInterface|Order
     */
    public function setCurrency($currency)
    {
        return $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * @param array $items
     * @return \Dintero\Checkout\Api\Data\OrderInterface|Order
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface[]
     */
    public function getItems()
    {
        return $this->getData(self::ITEMS);
    }
}
