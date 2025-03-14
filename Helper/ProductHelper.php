<?php namespace ChannelEngine\Magento2\Helper;

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

        // If the attribute doesn't exist, treat it as not updated recently
        if (!$existingCeUpdatedAt || !$existingCeUpdatedAt->getValue()) {
            return false;
        }

        $lastUpdatedAt = $existingCeUpdatedAt->getValue();
        $currentTime = $this->dateTime->gmtTimestamp();

        $productUpdatedAtTime = strtotime($lastUpdatedAt);

        if ($productUpdatedAtTime === false) {
            return false;
        }

        return ($currentTime - $productUpdatedAtTime) <= self::UPDATE_THRESHOLD_SECONDS;
    }
}
