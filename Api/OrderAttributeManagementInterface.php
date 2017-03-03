<?php namespace ChannelEngine\Magento2\Api;

interface OrderAttributeManagementInterface
{
	/**
	 * Updates the specified order with the specified CE attributes.
	 *
	 * @api
	 * @param int $orderId
	 * @param mixed $attributes
	 * @return boolean
	 */
	public function setAttributes($orderId, $attributes = null);
}