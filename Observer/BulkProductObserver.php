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

        //Wrong code. This code returns the array key value and not the product ID. Causing the wrong products to be updated or if the product does not exist in catalog_product_entity, an error is produced in the 
        //magento exeptions logs:
        // Updating ce_updated_at field was unsuccessful {"exception":"[object] (Zend_Db_Statement_Exception(code: 23000): SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`mydatabase`.`catalog_product_entity_datetime`, CONSTRAINT `CAT_PRD_ENTT_DTIME_ENTT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES
        
        //foreach ($entities as $key => $value) {
        //    $ids[] = $key;            
        //}

        //New code
        foreach ($entities as $product) {
            $ids[] = $product->getId();
        }

        return $ids;
    }

    private function updateCeAttribute(array $productIds, $storeId) 
    {
        $date = date('Y-m-d H:i:s');
        $this->massAction->updateAttributes($productIds, array('ce_updated_at' => $date), $storeId);
    }
}
