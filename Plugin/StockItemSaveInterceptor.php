<?php

namespace ChannelEngine\Magento2\Plugin;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class StockItemSaveInterceptor
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(ProductRepository $productRepository, LoggerInterface $logger)
    {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * After save plugin to update custom product attribute
     *
     * @param StockItemRepositoryInterface $subject
     * @param StockItemInterface $result
     * @return StockItemInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function afterSave(
        StockItemRepositoryInterface $subject,
        StockItemInterface $result
    ) {
        $this->logger->debug('Called StockItem afterSave...');

        $productId = $result->getProductId();
        $date = date('Y-m-d H:i:s');

        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId);

                $product->setData('ce_updated_at', $date);
                $product->setCustomAttribute('ce_updated_at', $date);

                $this->productRepository->save($product);
            } catch (NoSuchEntityException $e) {
                throw new NoSuchEntityException(__('Product not found: %1', $productId));
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('Could not save product: %1', $e->getMessage()));
            }
        }

        return $result;
    }
}
