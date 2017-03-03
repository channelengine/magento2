<?php namespace ChannelEngine\Magento2\Model\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;

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
    protected $_rateResultFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
		$this->_logger = $logger;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        /*if (!$this->getConfigFlag('active')) {
            return false;
        }
        $shippingPrice = $this->getConfigData('price');
        */

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();
		
		$shippingPrice = 0.00;

		$method->setCarrier($this->_code);
		$method->setCarrierTitle($this->getConfigData('carrier_title'));
		$method->setMethod($this->_code);
		$method->setMethodTitle($this->getConfigData('method_title'));
		$method->setPrice($shippingPrice); // Set CE Shipping Cost here
		$method->setCost($shippingPrice); // Set CE Shipping Cost here

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
