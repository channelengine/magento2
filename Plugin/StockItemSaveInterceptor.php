<?php

namespace ChannelEngine\Magento2\Plugin;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime\DateTime;
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
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
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

//    /**
//     * After save plugin to update custom product attribute
//     *
//     * @param StockItemRepositoryInterface $subject
//     * @param StockItemInterface $result
//     * @return StockItemInterface
//     * @throws NoSuchEntityException
//     * @throws CouldNotSaveException
//     */
//    public function afterSave(
//        StockItemRepositoryInterface $subject,
//        StockItemInterface $result
//    ) {
//        $this->logger->debug('Called StockItem afterSave...');
//
//        $productId = $result->getProductId();
//
//
//        if ($productId) {
//            try {
//                $product = $this->productRepository->getById($productId);
//
//                $minutesDiff = $this->getLastUpdatedAtDifference($product);
//
//                if ($minutesDiff <= 5) {
//                    return $result; // Do nothing
//                }
//
//                $date = $this->dateTime->gmtDate();
//
//                $product->setData('ce_updated_at', $date);
//                $product->setCustomAttribute('ce_updated_at', $date);
//
//                // Save only the attribute, to prevent cyclic events (when already performing a product save)
//                $product->getResource()->saveAttribute($product, 'ce_updated_at');
//            } catch (NoSuchEntityException $e) {
//                // TODO: log instead and return $result to do nothing
//                throw new NoSuchEntityException(__('Product not found: %1', $productId));
//            } catch (\Exception $e) {
//                // TODO: just log and do nothing
//                throw new CouldNotSaveException(__('Could not save product: %1', $e->getMessage()));
//            }
//        }
//
//        return $result;
//    }

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
