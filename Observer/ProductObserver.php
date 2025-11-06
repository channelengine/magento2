<?php

declare(strict_types=1);

namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use ChannelEngine\Magento2\Helper\ProductHelper;

class ProductObserver implements ObserverInterface
{
    /**
     * @var DateTime
     */
    private DateTime $dateTime;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var ProductHelper
     */
    private ProductHelper $productHelper;

    /**
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     * @param ProductHelper $productHelper
     */
    public function __construct(
        LoggerInterface $logger,
        DateTime $dateTime,
        ProductHelper $productHelper
    )
    {
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->productHelper = $productHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getProduct();

            if ($this->productHelper->wasUpdatedRecently($product)) {
                return;
            }

            $date = $this->dateTime->gmtDate();
            $attr = 'ce_updated_at';

            // Set both: https://magento.stackexchange.com/a/229280
            $product->setData($attr, $date);
            $product->setCustomAttribute($attr, $date);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
