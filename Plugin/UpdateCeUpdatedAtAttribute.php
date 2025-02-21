<?php

namespace ChannelEngine\Magento2\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class ProductRepositorySaveInterceptor
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
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
    public function beforeSave(ProductRepositoryInterface $subject, ProductInterface $product, $saveOptions = false) {
        $date = date('Y-m-d H:i:s');

        try {
            $product->setData('ce_updated_at', $date);
            $product->setCustomAttribute('ce_updated_at', $date);
            return [$product, $saveOptions];
        } catch (\Exception $e) {
            $this->logger->error(
                'Error occurred while updating ce_updated_at attribute for product ID ' . $product->getId(),
                ['exception' => $e]
            );

            return [$product, $saveOptions];
        }
    }
}
