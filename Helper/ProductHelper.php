<?php

declare(strict_types=1);

namespace ChannelEngine\Magento2\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;

class ProductHelper
{
    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    private const UPDATE_THRESHOLD_SECONDS = 10;

    /**
     * @param DateTime $dateTime
     */
    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Check if the product was updated recently (within the last 10 seconds).
     *
     * @param $product
     * @return bool
     * @throws \Exception
     */
    public function wasUpdatedRecently($product): bool
    {
        $existingCeUpdatedAt = $product->getCustomAttribute('ce_updated_at');

        // Try to read the attribute from the product data first, then fall back to the custom attribute object.
        $lastUpdatedAt = $product->getData('ce_updated_at');
        if (empty($lastUpdatedAt) && $existingCeUpdatedAt && $existingCeUpdatedAt->getValue()) {
            $lastUpdatedAt = $existingCeUpdatedAt->getValue();
        }

        // If we still don't have a value, treat it as not updated recently
        if (empty($lastUpdatedAt)) {
            return false;
        }

        $currentTime = $this->dateTime->gmtTimestamp();
        $productUpdatedAtTime = strtotime((string) $lastUpdatedAt);

        if ($productUpdatedAtTime === false) {
            return false;
        }

        return ($currentTime - $productUpdatedAtTime) <= self::UPDATE_THRESHOLD_SECONDS;
    }
}
