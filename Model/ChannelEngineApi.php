<?php namespace ChannelEngine\Magento2\Model;

use ChannelEngine\Magento2\NoAttributesException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\Serialize\SerializerInterface;
use ChannelEngine\Magento2\Api\ChannelEngineApiInterface;

class ChannelEngineApi implements ChannelEngineApiInterface
{
    private $orderRepo;
    private $quoteRepo;

    /**
     * @var SerializerInterface
     */
    private $_serializer;
    /**
     * @var QuoteIdMaskFactory $quoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepo
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepo
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepo,
        CartRepositoryInterface $quoteRepo,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        SerializerInterface $serializer
    ) {
        $this->orderRepo = $orderRepo;
        $this->quoteRepo = $quoteRepo;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->_serializer = $serializer;
    }

    /**
     * Updates the specified quote with the specified CE prices because the magento API does not support posting custom prices
     *
     * @api
     * @param string $cartId
     * @param mixed $prices
     * @return mixed $result
     */
    public function setQuotePrices($cartId, $prices = null)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->quoteRepo->get($quoteIdMask->getQuoteId());

        if(isset($prices['ce_id']))
        {
            $quote->setData('ce_id', $prices['ce_id']);
            $quote->setData('ce_channel_order_no', $prices['ce_channel_order_no']);
            $quote->setData('ce_channel_name', $prices['ce_channel_name']);
        }

        $cartItems = $quote->getAllItems();
        foreach($cartItems as $item) {
            if(isset($prices['items'][$item->getId()])) {
                $attrs = $prices['items'][$item->getId()];

                $price = $attrs['price'];

                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
            }
        }

        $shippingPrice = $prices['shipping_price'];

        $quote->setExtShippingInfo($this->_serializer->serialize($shippingPrice));

        $quote->save();

        return true;
    }

    /**
     * Updates the specified order with the specified CE attributes.
     *
     * @api
     * @param int $orderId
     * @param mixed $attributes
     * @return boolean
     */
    public function setOrderAttributes($orderId, $attributes = null)
    {
        if ($attributes === null) {
            throw new NoAttributesException();
        }

        $order = $this->orderRepo->get($orderId);

        $order->setData('ce_id', $attributes['ce_id']);
        $order->setData('ce_channel_order_no', $attributes['ce_channel_order_no']);
        $order->setData('ce_channel_name', $attributes['ce_channel_name']);
        $orderItems = $order->getAllItems();

        foreach($orderItems as $item) {
            if(isset($attributes['items'][$item->getId()])) {
                $attrs = $attributes['items'][$item->getId()];
                $item->setData('ce_id', $attrs['ce_id']);
            }
        }

        $order->save();

        return true;
    }
}
