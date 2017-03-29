<?php namespace ChannelEngine\Magento2\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Pay In Store payment method model
 */
class ChannelEngine extends AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'channelengine';

    /**
     * Availability option
     *
     * @var bool
     */
     protected $_isOffline = true;

    /**
     * Hides the method from the checkout process
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Make sure the method is always active, and not dependent on settings
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return true;
    }
}
