<?php

declare(strict_types=1);

namespace ChannelEngine\Magento2\Model\Payment;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Block\Form;
use Magento\Payment\Block\Info;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class ChannelEngine extends AbstractExtensibleModel implements
    MethodInterface,
    PaymentMethodInterface
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'channelengine';

    /**
     * @var string
     */
    protected $_formBlockType = Form::class;

    /**
     * @var string
     */
    protected $_infoBlockType = Info::class;

    /**
     * @var bool
     */
    protected $_isGateway = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var bool
     */
    protected $_canOrder = false;

    /**
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * @var bool
     */
    protected $_canCapture = false;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canCaptureOnce = false;

    /**
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * @var bool
     */
    protected $_canVoid = false;

    /**
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * Hides the method from the checkout process
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var bool
     */
    protected $_canFetchTransactionInfo = false;

    /**
     * @var bool
     */
    protected $_canReviewPayment = false;

    /**
     * TODO: whether a captured transaction may be voided by this gateway
     * This may happen when amount is captured, but not settled
     * @var bool
     */
    protected $_canCancelInvoice = false;

    /**
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = [];

    /**
     * @var Data
     */
    protected $_paymentData;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Logger
     */
    protected $logger;

    private $_userContext;

    /**
     * @var DirectoryHelper
     */
    private $directory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param UserContextInterface $userContext
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
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
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_paymentData = $paymentData;
        $this->_scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->_userContext = $userContext;
        $this->directory = $directory ?: ObjectManager::getInstance()->get(DirectoryHelper::class);
        $this->initializeData($data);
    }

    /**
     * Initializes injected data
     *
     * @param array $data
     * @return void
     */
    protected function initializeData($data = [])
    {
        if (!empty($data['formBlockType'])) {
            $this->_formBlockType = $data['formBlockType'];
        }
    }

    /**
     * @inheritdoc
     * @deprecated 100.2.0
     */
    public function setStore($storeId)
    {
        $this->setData('store', (int)$storeId);
    }

    /**
     * @inheritdoc
     * @deprecated 100.2.0
     */
    public function getStore()
    {
        return $this->getData('store');
    }

    /**
     * Check order availability
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canOrder()
    {
        return $this->_canOrder;
    }

    /**
     * Check authorize availability
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canAuthorize()
    {
        return $this->_canAuthorize;
    }

    /**
     * Check capture availability
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canCapture()
    {
        return $this->_canCapture;
    }

    /**
     * Check partial capture availability
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canCapturePartial()
    {
        return $this->_canCapturePartial;
    }

    /**
     * Check whether capture can be performed once and no further capture possible
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canCaptureOnce()
    {
        return $this->_canCaptureOnce;
    }

    /**
     * Check refund availability
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }

    /**
     * Check partial refund availability for invoice
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canRefundPartialPerInvoice()
    {
        return $this->_canRefundInvoicePartial;
    }

    /**
     * Check void availability.
     *
     * @return bool
     * @internal param \Magento\Framework\DataObject $payment
     * @deprecated 100.2.0
     */
    public function canVoid()
    {
        return $this->_canVoid;
    }

    /**
     * Using internal pages for input payment data.
     *
     * Can be used in admin.
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canUseInternal()
    {
        return $this->_canUseInternal;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canUseCheckout()
    {
        return $this->_canUseCheckout;
    }

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canEdit()
    {
        return true;
    }

    /**
     * Check fetch transaction info availability
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canFetchTransactionInfo()
    {
        return $this->_canFetchTransactionInfo;
    }

    /**
     * Fetch transaction info
     *
     * @param InfoInterface $payment
     * @param string $transactionId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        return [];
    }

    /**
     * Retrieve payment system relation flag
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function isGateway()
    {
        return $this->_isGateway;
    }

    /**
     * Retrieve payment method online/offline flag
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function isOffline()
    {
        return $this->_isOffline;
    }

    /**
     * Flag if we need to run payment initialize while order place
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function isInitializeNeeded()
    {
        return $this->_isInitializeNeeded;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     * @deprecated 100.2.0
     */
    public function canUseForCountry($country)
    {
        /*
        for specific country, the flag will set up as 1
        */
        if ($this->getConfigData('allowspecific') == 1) {
            $availableCountries = explode(',', $this->getConfigData('specificcountry') ?? '');
            if (!in_array($country, $availableCountries)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     * @throws LocalizedException
     * @deprecated 100.2.0
     */
    public function getCode()
    {
        if (empty($this->_code)) {
            throw new LocalizedException(
                __('We cannot retrieve the payment method code.')
            );
        }
        return $this->_code;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     * @deprecated 100.2.0
     */
    public function getFormBlockType()
    {
        return $this->_formBlockType;
    }

    /**
     * Retrieve block type for display method information
     *
     * @return string
     * @deprecated 100.2.0
     */
    public function getInfoBlockType()
    {
        return $this->_infoBlockType;
    }

    /**
     * Retrieve payment information model object
     *
     * @return InfoInterface
     * @throws LocalizedException
     * @deprecated 100.2.0
     */
    public function getInfoInstance()
    {
        $instance = $this->getData('info_instance');
        if (!$instance instanceof InfoInterface) {
            throw new LocalizedException(
                __('We cannot retrieve the payment information object instance.')
            );
        }
        return $instance;
    }

    /**
     * Retrieve payment information model object
     *
     * @param InfoInterface $info
     * @return void
     * @deprecated 100.2.0
     */
    public function setInfoInstance(InfoInterface $info)
    {
        $this->setData('info_instance', $info);
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws LocalizedException
     * @deprecated 100.2.0
     */
    public function validate()
    {
        /**
         * to validate payment method is allowed for billing country or not
         */
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } elseif (method_exists($paymentInfo, 'getQuote')) {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = null;
        }
        $billingCountry = $billingCountry ?: $this->directory->getDefaultCountry();

        if (!$this->canUseForCountry($billingCountry)) {
            throw new LocalizedException(
                __('You can\'t use the payment type you selected to make payments to the billing country.')
            );
        }

        return $this;
    }

    /**
     * Order payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function order(InfoInterface $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new LocalizedException(__('The order action is not available.'));
        }
        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new LocalizedException(__('The authorize action is not available.'));
        }
        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function capture(InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new LocalizedException(__('The capture action is not available.'));
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function refund(InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }
        return $this;
    }

    /**
     * Cancel payment abstract method
     *
     * @param InfoInterface $payment
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function cancel(InfoInterface $payment)
    {
        return $this;
    }

    /**
     * Void payment abstract method
     *
     * @param InfoInterface $payment
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function void(InfoInterface $payment)
    {
        if (!$this->canVoid()) {
            throw new LocalizedException(__('The void action is not available.'));
        }
        return $this;
    }

    /**
     * Whether this method can accept or deny payment.
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canReviewPayment()
    {
        return $this->_canReviewPayment;
    }

    /**
     * Attempt to accept a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function acceptPayment(InfoInterface $payment)
    {
        if (!$this->canReviewPayment()) {
            throw new LocalizedException(__('The payment review action is unavailable.'));
        }
        return false;
    }

    /**
     * Attempt to deny a payment that us under review
     *
     * @param InfoInterface $payment
     * @return false
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function denyPayment(InfoInterface $payment)
    {
        if (!$this->canReviewPayment()) {
            throw new LocalizedException(__('The payment review action is unavailable.'));
        }
        return false;
    }

    /**
     * Retrieve payment method title
     *
     * @return string
     * @deprecated 100.2.0
     */
    public function getTitle()
    {
        return $this->getConfigData('title');
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Store $storeId
     *
     * @return mixed
     * @deprecated 100.2.0
     */
    public function getConfigData($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            if (method_exists($this, 'getOrderPlaceRedirectUrl')) {
                return $this->getOrderPlaceRedirectUrl();
            }
            return null;
        }

        if (null === $storeId) {
            $storeId = $this->getStore();
        }

        $path = 'payment/' . $this->getCode() . '/' . $field;
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Assign data to info model instance
     *
     * @param DataObject $data
     * @return $this
     * @throws LocalizedException
     * @deprecated 100.2.0
     */
    public function assignData(DataObject $data)
    {
        $this->_eventManager->dispatch(
            'payment_method_assign_data_' . $this->getCode(),
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        $this->_eventManager->dispatch(
            'payment_method_assign_data',
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        return $this;
    }

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     * @deprecated 100.2.0
     */
    public function isAvailable(CartInterface $quote = null)
    {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        $checkResult = new DataObject();
        $checkResult->setData('is_available', true);

        // for future use in observers
        $this->_eventManager->dispatch(
            'payment_method_is_active',
            [
                'result' => $checkResult,
                'method_instance' => $this,
                'quote' => $quote
            ]
        );

        return $checkResult->getData('is_available');
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
        return ($userType == UserContextInterface::USER_TYPE_INTEGRATION || $userType == UserContextInterface::USER_TYPE_ADMIN);
    }

    /**
     * Method that will be executed instead of authorize or capture if flag isInitializeNeeded set to true.
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    /**
     * Get config payment action url.
     *
     * Used to universalize payment actions when processing payment place.
     *
     * @return string
     * @deprecated 100.2.0
     */
    public function getConfigPaymentAction()
    {
        return $this->getConfigData('payment_action');
    }

    /**
     * Log debug data to file
     *
     * @param array $debugData
     * @return void
     * @deprecated 100.2.0
     */
    protected function _debug($debugData)
    {
        $this->logger->debug(
            $debugData,
            $this->getDebugReplacePrivateDataKeys(),
            $this->getDebugFlag()
        );
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @deprecated 100.2.0
     */
    public function getDebugFlag()
    {
        return (bool)(int)$this->getConfigData('debug');
    }

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     * @return void
     * @deprecated 100.2.0
     */
    public function debugData($debugData)
    {
        $this->_debug($debugData);
    }

    /**
     * Return replace keys for debug data
     *
     * @return array
     * @deprecated 100.2.0
     */
    public function getDebugReplacePrivateDataKeys()
    {
        return (array) $this->_debugReplacePrivateDataKeys;
    }
}
