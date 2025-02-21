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
        $milliseconds = (int) (microtime(true) * 1000);
        $attr = 'ce_updated_at';

        // Set both: https://magento.stackexchange.com/a/229280
        $product->setData($attr, $milliseconds);
        $product->setCustomAttribute($attr, $milliseconds);
    }
}