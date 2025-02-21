<?php

namespace ChannelEngine\Magento2\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class UpdateCeUpdatedAtAttribute
{

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(DateTime $dateTime, LoggerInterface $logger)
    {
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    /**
     * Before save plugin for ProductRepositoryInterface save method
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @return ProductInterface|ProductInterface[]
     * @throws LocalizedException
     */
    public function beforeSave(ProductRepositoryInterface $subject, ProductInterface $product) {

//        $currentDate = $this->dateTime->gmtDate();

        $date = date('Y-m-d H:i:s');

        try {
            $product->setData('ce_updated_at', $date);
            $product->setCustomAttribute('ce_updated_at', $date);
            return [$product];
        } catch (\Exception $e) {
            $this->logger->error(
                'Error occurred while updating ce_updated_at attribute for product ID ' . $product->getId(),
                ['exception' => $e]
            );

            return [$product];
        }
    }
}
