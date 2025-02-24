<?php

namespace ChannelEngine\Magento2\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class ProductSaveInterceptor
{

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger,
        DateTime  $dateTime)
    {
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    public function beforeSave(Product $subject)
    {
        // This beforeSave is being called when the Product is saved on the UI manually

        $this->logger->debug('Called Product beforeSave...');

        try {
            $minutesDiff = $this->getLastUpdatedAtDifference($subject);

            if ($minutesDiff <= 5) {
                return null;
            }

            $date = $this->dateTime->gmtDate();

            $subject->setData('ce_updated_at', $date);
            $subject->setCustomAttribute('ce_updated_at', $date);

            return null; // Must return null
        } catch (\Exception $e) {
            $this->logger->error(
                'Error occurred while updating ce_updated_at attribute for product ID ' . $subject->getId(),
                ['exception' => $e]
            );

            return null;
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

//    public function afterSave(Product $subject, Product $result)
//    {
//        try {
//            // Example: Log the product ID and update a custom datetime attribute
//            $productId = $result->getId();
//            $this->logger->info("Product {$productId} was saved.");
//
//            $date = date('Y-m-d H:i:s');
//
//            $subject->setData('ce_updated_at', $date);
//            $subject->setCustomAttribute('ce_updated_at', $date);
//
////            // Save the product again (optional, but might trigger another plugin execution)
////            $result->save();
//
//        } catch (\Exception $e) {
//            $this->logger->error("Error in ProductPlugin afterSave: " . $e->getMessage());
//        }
//
//        return $result;
//    }
}
