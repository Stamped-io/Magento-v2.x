<?php

namespace Dintero\Checkout\Model\Order;

class Item
    extends \Magento\Framework\DataObject
    implements \Dintero\Checkout\Api\Data\Order\ItemInterface
{
    /**
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @param string $id
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface|Item
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @param string $lineId
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setLineId($lineId)
    {
        return $this->setData(self::LINE_ID, $lineId);
    }

    /**
     * @return string
     */
    public function getLineId()
    {
        return $this->getData(self::LINE_ID);
    }

    /**
     * @param float $qty
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface|Item
     */
    public function setQuantity($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->getData(self::QTY);
    }

    /**
     * @param float $vat
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface|Item
     */
    public function setVat($vat)
    {
        return $this->setData(self::VAT, $vat);
    }

    /**
     * @return float
     */
    public function getVat()
    {
        return $this->getData(self::VAT);
    }

    /**
     * @param $amount
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
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
     * @param float $amount
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface|Item
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
     * @param string $description
     * @return \Dintero\Checkout\Api\Data\Order\ItemInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }
}
