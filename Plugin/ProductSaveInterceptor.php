<?php

namespace ChannelEngine\Magento2\Plugin;

use Psr\Log\LoggerInterface;

class ProductSaveInterceptor
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

    public function beforeSave(\Magento\Catalog\Model\Product $subject)
    {
        // This beforeSave is being called when the Product is saved on the UI manually
        $this->logger->debug('Called Product beforeSave...');
        $date = date('Y-m-d H:i:s');

        try {
            $subject->setData('ce_updated_at', $date);
            $subject->setCustomAttribute('ce_updated_at', $date);

            return null; // Must return null for before plugins
        } catch (\Exception $e) {
            $this->logger->error(
                'Error occurred while updating ce_updated_at attribute for product ID ' . $subject->getId(),
                ['exception' => $e]
            );

            return null;
        }
    }
}
