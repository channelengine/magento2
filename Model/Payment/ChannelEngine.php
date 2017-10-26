<?php namespace ChannelEngine\Magento2\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Authorization\Model\UserContextInterface;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

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
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $_userContext;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        UserContextInterface $userContext,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_userContext = $userContext;
    }

    /**
     * Make sure the method is always active, and not dependent on settings
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        $userType = $this->_userContext->getUserType();
        if($userType != UserContextInterface::USER_TYPE_INTEGRATION && $userType != UserContextInterface::USER_TYPE_ADMIN) return false;
    }
}
