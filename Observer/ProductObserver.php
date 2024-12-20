<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    public function __construct()
    {

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $date = date('Y-m-d H:i:s');
        $attr = 'ce_updated_at';

        // Set both: https://magento.stackexchange.com/a/229280
        // $product->setData($attr, $date);
        $product->setCustomAttribute($attr, $date);
        $product->setCustomAttribute("test", "test" . $date);
    }
}