<?php namespace ChannelEngine\Magento2\Model\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Authorization\Model\UserContextInterface;

use Psr\Log\LoggerInterface;

class ChannelEngine extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'channelengine';

    /**
     * @var string
     */
    private $_name = 'ChannelEngine';

    protected $_logger;
    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $_rateMethodFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $_userContext;

    /**
     * @var SerializerInterface
     */
    private $_serializer;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        UserContextInterface $userContext,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_logger = $logger;

        $this->_userContext = $userContext;

        $this->_serializer = $serializer;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        $userType = $this->_userContext->getUserType();
        if($userType != UserContextInterface::USER_TYPE_INTEGRATION && $userType != UserContextInterface::USER_TYPE_ADMIN) return false;

        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('carrier_title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('method_title'));

        // Get the quote from the RateRequest so we can parse extShippingInfo, which contains the shipping price
        // as calculated by the external channel.
        $quoteItems = $request->getAllItems();
        if(count($quoteItems) == 0)
        {
            $shippingPrice = 0.00;
        }
        else
        {
            $quoteItem = $quoteItems[0];
            $quote = $quoteItem->getQuote();
            $shippingPrice = (!empty($quote->getExtShippingInfo())) ? $this->_serializer->unserialize($quote->getExtShippingInfo()) : 0.00;
        }

        $method->setPrice($shippingPrice); // Set CE Shipping Cost here
        $method->setCost(0);

        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('method_title')];
    }
}
