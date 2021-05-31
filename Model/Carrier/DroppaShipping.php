<?php

// 4. Shipping Model

namespace Droppa\DroppaShipping\Model\Carrier;

use Droppa\DroppaShipping\Model\Quotes;
use Droppa\DroppaShipping\Model\Curl;
use Exception;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;

if (!class_exists('DroppaShipping')) {

    class DroppaShipping extends AbstractCarrier implements CarrierInterface
    {
        protected $_code = 'droppashipping';
        protected $_rateResultFactory;
        protected $_rateMethodFactory;
        protected $_isFixed = true;
        protected $_request;
        protected $_storeManager;
        protected $_rateErrorFactory;
        protected $_scopeConfig;
        protected $_logger;
        public $_quote_endpoint = 'https://www.droppa.co.za/droppa/services/plugins/quotes';
        public $_quote_body = [];
        public $_total_amount;

        /**
         * Shipping constructor.
         *
         * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
         * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
         * @param \Psr\Log\LoggerInterface                                    $logger
         * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
         * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
         * @param array                                                       $data
         */
        public function __construct(ScopeConfigInterface $scopeConfig, ErrorFactory $rateErrorFactory, LoggerInterface $logger, ResultFactory $rateResultFactory, MethodFactory $rateMethodFactory, Cart $cart, array $data = array())
        {
            $this->_rateResultFactory = $rateResultFactory;
            $this->_rateMethodFactory = $rateMethodFactory;
            $this->_rateErrorFactory = $rateErrorFactory;
            $this->_cart = $cart;
            $this->_logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);

            parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        }

        /**
         * get allowed methods
         * @return array
         */
        public function getAllowedMethods()
        {
            # Retrieve information from carrier configuration
            return [$this->_code => $this->getConfigData('name')];
        }

        /**
         * @return float
         */
        private function getShippingPrice()
        {
            return $this->getFinalPriceWithHandlingFee($this->getConfigData('price'));
        }

        /**
         * @param RateRequest $request
         *
         * @return bool|Result
         */
        public function collectRates(RateRequest $request)
        {
            if (!$this->getConfigFlag('active')) {
                return false;
            }

            $result = $this->_rateResultFactory->create();

            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            # Dimensions
            $_fullDimensionContents = [
                'parcel_length' => (int) $request->getPackageDepth(),
                'parcel_breadth' => (int) $request->getPackageWidth(),
                'parcel_height' => (int) $request->getPackageHeight(),
                'parcel_mass' => (int) $request->getPackageWeight()
            ];

            $this->_quote_body = $this->_quotes_plugin_attributes($request->getOrigPostcode(), $request->getDestPostcode(), $request->getPackageWeight(), $_fullDimensionContents);

            $useCurlObject = new Curl($this->_scopeConfig->getValue('carriers/droppashipping/api_key', ScopeInterface::SCOPE_WEBSITES), $this->_scopeConfig->getValue('carriers/droppashipping/service_key', ScopeInterface::SCOPE_WEBSITES));
            $response = $useCurlObject->curl_endpoint($this->_quote_endpoint, $this->_quote_body, 'POST');

            if (isset($response) && $this->_cart->getQuote()->getId()) {
                $object = \Safe\json_decode($response, true);
            }

            if (!$this->_total_amount = $object['amount']) {
                return $this->_total_amount = $this->getShippingPrice();
            }

            $method->setPrice($this->_total_amount);
            $method->setCost($this->_total_amount);

            try {
                return $result->append($method);
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage(), $e->getCode());
            }
        }

        /**
         * @Description                             Gets the products attributes to generate a quotation
         * @param $_pickUpPCode                     Pick up postal code
         * @param $_dropOffPCode                    Drop off postal code
         * @param $_product_total_mass              Product weight
         * @return $calculate_distance_for_quotes   Array
         */
        public function _quotes_plugin_attributes($_pickUpPCode, $_dropOffPCode, $_product_total_mass, $_parcelDimensionsArrayHolder)
        {
            (array) $_get_product_attributes = new Quotes($_pickUpPCode, $_dropOffPCode, $_product_total_mass);

            $calculate_distance_for_quotes = [
                "pickUpCode" => $_get_product_attributes->get_pickUpCode(),
                "dropOffCode" => $_get_product_attributes->get_dropOffCode(),
                "mass" => $_get_product_attributes->get_weight(),
                "parcelDimensions" => [$_parcelDimensionsArrayHolder]
            ];

            if (is_array($calculate_distance_for_quotes)) {
                return $calculate_distance_for_quotes;
            }
        }
    }
}