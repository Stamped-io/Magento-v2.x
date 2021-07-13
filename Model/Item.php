<?php

namespace Dintero\Checkout\Model;

use Dintero\Checkout\Api\Data\ItemInterface;

/**
 * Class Item
 *
 * @package Dintero\Checkout\Model
 */
class Item implements ItemInterface
{

    /**
     * @var \Magento\Framework\DataObject $dataObject
     */
    protected $dataObject;

    /**
     * Item constructor.
     *
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObject = new \Magento\Framework\DataObject($data);
    }

    /**
     * @param $key
     * @param $value
     * @return \Magento\Framework\DataObject
     */
    protected function setData($key, $value)
    {
        return $this->dataObject->setData($key, $value);
    }

    /**
     * @param $key
     * @return array|mixed|null
     */
    protected function getData($key)
    {
        return $this->dataObject->getData($key);
    }

    /**
     * @param string $id
     * @return ItemInterface
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * @param float $vat
     * @return ItemInterface
     */
    public function setVat($vat)
    {
        $this->setData(self::VAT, $vat);
        return $this;
    }

    /**
     * @param float $amount
     * @return ItemInterface
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
        return $this;
    }

    /**
     * @param string $lineId
     * @return ItemInterface
     */
    public function setLineId($lineId)
    {
        $this->setData(self::LINE_ID, $lineId);
        return $this;
    }

    /**
     * @param integer $qty
     * @return ItemInterface
     */
    public function setQuantity($qty)
    {
        $this->setData(self::QUANTITY, $qty);
        return $this;
    }

    /**
     * @param float $amount
     * @return ItemInterface
     */
    public function setVatAmount($amount)
    {
        $this->setData(self::VAT_AMOUNT, $amount);
        return $this;
    }

    /**
     * @param string $description
     * @return ItemInterface
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
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
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @return string
     */
    public function getLineId()
    {
        return $this->getData(self::LINE_ID);
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        return $this->getData(self::QUANTITY);
    }

    /**
     * @return float
     */
    public function getVatAmount()
    {
        return $this->getData(self::VAT_AMOUNT);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }
}
