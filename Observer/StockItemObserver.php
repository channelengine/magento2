<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;

class StockItemObserver implements ObserverInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $stockItem = $observer->getItem();
        $productId = $stockItem->getProductId();
        $product = $this->productRepository->getById($productId);
        $date = date('Y-m-d H:i:s');
        $attr = 'ce_updated_at';

        // Set both: https://magento.stackexchange.com/a/229280
        $product->setData($attr, $date);
        $product->setCustomAttribute($attr, $date);

        // Save only the attribute, to prevent cyclic events (when already performing a product save)
        $product->getResource()->saveAttribute($product, $attr);
    }
}