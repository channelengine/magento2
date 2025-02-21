<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Psr\Log\LoggerInterface;

class ProductObserver implements ObserverInterface
{
    protected $productRepository;
    protected $logger;

    public function __construct(
        ProductRepository $productRepository,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        $product = $observer->getEvent()->getProduct();
//        $productId = $product->getId();
//
//        try {
//            // Load the product again to avoid conflicts
//            $loadedProduct = $this->productRepository->getById($productId);
//
//            $date = date('Y-m-d H:i:s');
//            $attr = 'ce_updated_at';
//
//            // Set the attribute value (Replace 'your_attribute_code' with the actual attribute code)
//            $loadedProduct->setCustomAttribute($attr, $date);
//            $loadedProduct->setData($attr, $date); // Alternative way
//
//            // Save the updated product
//            $this->productRepository->save($loadedProduct);
//
//            $this->logger->info('Updated product attribute for Product ID: ' . $productId);
//        } catch (\Exception $e) {
//            $this->logger->error('Error updating product attribute: ' . $e->getMessage());
//        }
    }
}