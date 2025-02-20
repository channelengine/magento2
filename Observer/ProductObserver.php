<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    public function __construct()
    {
        
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
        $dateTime = new \DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

        $product = $observer->getProduct();
        $date = $dateTime->format("Y-m-d H:i:s.u");
        $attr = 'ce_updated_at';

        // Set both: https://magento.stackexchange.com/a/229280
        $product->setData($attr, $date);
        $product->setCustomAttribute($attr, $date);
    }
}