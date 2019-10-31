<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderObserver implements ObserverInterface
{
	public function __construct()
	{
		
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $ceId = $quote->getData('ce_id');

        if($ceId)
        {
            // Disable emails
            $order->setCanSendNewEmailFlag(false);
        }
	}
}