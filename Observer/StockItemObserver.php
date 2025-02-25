<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use ChannelEngine\Magento2\Helper\ProductHelper;

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
    /**
     * @var DateTime
     */
    private DateTime $dateTime;
    /**
     * @var ProductHelper
     */
    private ProductHelper $productHelper;

    /**
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     * @param ProductHelper $productHelper
     */
    public function __construct(
        ProductRepository $productRepository,
        LoggerInterface $logger,
        DateTime $dateTime,
        ProductHelper $productHelper
    )
    {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->productHelper = $productHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $stockItem = $observer->getItem();
            $productId = $stockItem->getProductId();
            $product = $this->productRepository->getById($productId);

            if ($this->productHelper->wasUpdatedRecently($product)) {
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
}
