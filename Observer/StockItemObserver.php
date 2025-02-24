<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class StockItemObserver implements ObserverInterface
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    private DateTime $dateTime;

    /**
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     */
    public function __construct(
        ProductRepository $productRepository,
        LoggerInterface $logger,
        DateTime $dateTime)
    {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    public function execute(Observer $observer)
    {
        try {
            $stockItem = $observer->getItem();
            $productId = $stockItem->getProductId();
            $product = $this->productRepository->getById($productId);

            $minutesDiff = $this->getLastUpdatedAtDifference($product);

            if ($minutesDiff <= 1) {
                return;
            }

            $date = $this->dateTime->gmtDate();
            $attr = 'ce_updated_at';

            // Set both: https://magento.stackexchange.com/a/229280
            $product->setData($attr, $date);
            $product->setCustomAttribute($attr, $date);

            // Save only the attribute, to prevent cyclic events (when already performing a product save)
            $product->getResource()->saveAttribute($product, $attr);
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
