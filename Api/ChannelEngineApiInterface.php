<?php namespace ChannelEngine\Magento2\Api;

interface ChannelEngineApiInterface
{
    /**
     * Updates the specified quote with the specified CE prices because the magento API does not support posting custom prices
     *
     * @api
     * @param string $cartId
     * @param mixed $prices
     * @return boolean
     */
    public function setQuotePrices($cartId, $prices = null);

    /**
     * Updates the specified order with the specified CE attributes.
     *
     * @api
     * @param int $orderId
     * @param mixed $attributes
     * @return boolean
     */
    public function setOrderAttributes($orderId, $attributes = null);
}