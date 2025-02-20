<?php namespace ChannelEngine\Magento2\Observer;

use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;

class BulkProductObserver implements ObserverInterface
{
    private $logger;
    private $massAction;
    private ProductRepository $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private $storeManager;

    public function __construct(
        LoggerInterface $logger,
        Action $massAction,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager)
    {
        $this->logger = $logger;
        $this->massAction = $massAction;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        try {
            $products = $observer->getEvent()->getBunch();
            $skus = $this->getSkusFromProducts($products);
            $productIds = $this->getProductIds($skus);
            $storeId = $this->storeManager->getStore()->getId();

            $this->updateCeAttribute($productIds, $storeId);
            
            $this->logger->info('Updated ce_updated_at field for the following product ids:' . json_encode($productIds));
        } catch (\Exception $e) {
            $this->logger->error('Updating ce_updated_at field was unsuccessful', ['exception' => $e]);
        }
    }

    private function getSkusFromProducts(array $products)
    {
        $skus = [];
        
        if (!is_array($products))
            return $skus;

        foreach ($products as $product) {
            if (isset($product['sku'])) {
                $skus[] = $product['sku'];
            }
        }

        return $skus;
    }

    private function getProductIds(array $skus) 
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', $skus, 'in')->create();
        $entities = $this->productRepository->getList($searchCriteria)->getItems();
        
        $ids = [];
        
        if (!is_array($entities))
            return $ids;

        foreach ($entities as $product) {
            $ids[] = $product->getId();
        }

        return $ids;
    }

    private function updateCeAttribute(array $productIds, $storeId) 
    {
        $now = \DateTime::createFromFormat('U.u', microtime(true));

        $date = $now->format("m-d-Y H:i:s.u");
        $this->massAction->updateAttributes($productIds, array('ce_updated_at' => $date), $storeId);
    }
}