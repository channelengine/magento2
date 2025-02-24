<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class ProductObserver implements ObserverInterface
{
    private DateTime $dateTime;
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        DateTime $dateTime
    )
    {
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getProduct();

            $minutesDiff = $this->getLastUpdatedAtDifference($product);

            if ($minutesDiff <= 1) {
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

    /**
     * @param $product
     * @return float|int
     * @throws \DateMalformedStringException
     */
    public function getLastUpdatedAtDifference($product)
    {
        $lastUpdatedAt = $product->getUpdatedAt();

        $currentTime = $this->dateTime->gmtDate();

        $productUpdatedAtTime = new \DateTime($lastUpdatedAt);
        $currentTimeObj = new \DateTime($currentTime);

        // Calculate the difference in minutes
        $interval = $productUpdatedAtTime->diff($currentTimeObj);
        $minutesDiff = $interval->i + ($interval->d * 24 * 60) + ($interval->h * 60);

        return $minutesDiff;
    }
}
