<?php namespace ChannelEngine\Magento2\Model\Api;

use Exception;
use Magento\Sales\Api\OrderRepositoryInterface;
use ChannelEngine\Magento2\Api\OrderAttributeManagementInterface;
 
class OrderAttributeManagement implements OrderAttributeManagementInterface
{
    private $orderRepo;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderInterface
     */
    public function __construct(OrderRepositoryInterface $orderInterface)
    {
        $this->orderInterface = $orderInterface;
    }

    /**
     * Updates the specified order with the specified CE attributes.
     *
     * @api
     * @param int $orderId
     * @param mixed $attributes
     * @return boolean
     */
    public function setAttributes($orderId, $attributes = null)
    {
        if(is_null($attributes)) throw new Exception('No attributes found');

        $order = $this->orderInterface->get($orderId);

        $order->setData('ce_id', $attributes['ce_id']);
        $order->setData('ce_channel_order_no', $attributes['ce_channel_order_no']);
        $order->setData('ce_channel_name', $attributes['ce_channel_name']);
        $orderItems = $order->getAllItems();

        foreach($orderItems as $item) {
            if(isset($attributes['items'][$item->getId()])) {
                $attrs = $attributes['items'][$item->getId()];
                $item->setData('ce_id', $attributes['ce_id']);
            }
        }

        $order->save();

        return true;
    }
}