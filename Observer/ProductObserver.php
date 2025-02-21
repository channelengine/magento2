<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    public function __construct()
    {
        
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $now = \DateTime::createFromFormat('U.u', sprintf('%.6f', microtime(true)));

        // $product = $observer->getProduct();
        // $date = $now->format("m-d-Y H:i:s.u");
        // $attr = 'ce_updated_at';

        // try commenting out the lines below to find out the error is coming from there or not
        // // Set both: https://magento.stackexchange.com/a/229280
        // $product->setData($attr, $date);
        // $product->setCustomAttribute($attr, $date);
    }
}