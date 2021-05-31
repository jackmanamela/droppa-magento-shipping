<?php

namespace Droppa\DroppaShipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Cart;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ProductRepository;
use Droppa\DroppaShipping\Model\Parcel;
use Droppa\DroppaShipping\Model\Curl;

class BookingsObserver extends Template implements ObserverInterface
{
    public $_booking_endpoint = 'https://www.droppa.co.za/droppa/services/plugins/book';
    protected $_logger;
    protected $_storeManager;
    protected $_scopeConfig;
    protected $_customer;
    protected $_request;
    protected $_objectManager;
    protected $_productRepository;
    public $_getWeight;
    public $_getHeight;
    public $_getWidth;
    public $_getLength;
    public $_resources;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Session $customer,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Cart $cart,
        ObjectManagerInterface $objectManager,
        ProductRepository $productRepository
    ) {
        $this->_logger = $logger;
        $this->_customer = $customer;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_cart = $cart;
        $this->_objectManager = $objectManager;
        $this->_productRepository = $productRepository;
    }

    /**
     * @Description         Gets an Array of the product's attributes
     * @param $_length      Item Length
     * @param $_breadth     Item Width
     * @param $_height      Item Height
     * @param $_mass        Item Weight
     */
    public function _booking_plugin_attributes($_length, $_breadth, $_height, $_mass)
    {
        $parcels = new Parcel($_mass, $_breadth, $_height, $_length);

        $_dimensions = [
            "parcel_length" => $parcels->get_length(),
            "parcel_breadth" => $parcels->get_width(),
            "parcel_height" => $parcels->get_height(),
            "parcel_mass" => $parcels->get_itemMass()
        ];

        if (is_array($_dimensions)) {
            return $_dimensions;
        }
    }

    public function execute(Observer $observer)
    {
        $order              = $observer->getEvent()->getOrder();
        $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession    = $objectManager->get('\Magento\Customer\Model\Session');
        $customerName       = $customerSession->getCustomer()->getName();
        $customerEmail      = $customerSession->getCustomer()->getEmail();
        $customerCompany    = $order->getBillingAddress()->getCompany();

        $customerPhone  = ($order->getShippingAddress()->getTelephone() ? $order->getShippingAddress()->getTelephone() : $order->getBillingAddress()->getTelephone());
        $dropOffpincode = ($order->getShippingAddress()->getPostcode() ? $order->getShippingAddress()->getPostcode() : $order->getBillingAddress()->getPostcode());
        $dropOffcity    = ($order->getShippingAddress()->getCity() ? $order->getShippingAddress()->getCity() : $order->getBillingAddress()->getCity());
        $dropOffRegion  = ($order->getShippingAddress()->getRegion() ? $order->getShippingAddress()->getRegion() : $order->getBillingAddress()->getRegion());
        $dropOffAddress = ($order->getShippingAddress()->getStreet() ? $order->getShippingAddress()->getStreet() : $order->getBillingAddress()->getStreet());

        /* Admin Store Data */
        $adminStoreName = $this->_scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_WEBSITES);
        $adminStoreRegion = $this->_scopeConfig->getValue('general/store_information/region_id', ScopeInterface::SCOPE_WEBSITES);
        $adminStorePostcode = $this->_scopeConfig->getValue('general/store_information/postcode', ScopeInterface::SCOPE_WEBSITES);
        $adminStoreCity = $this->_scopeConfig->getValue('general/store_information/city', ScopeInterface::SCOPE_WEBSITES);
        $adminStoreStreet1 = $this->_scopeConfig->getValue('general/store_information/street_line1', ScopeInterface::SCOPE_WEBSITES);
        $adminStoreStreet2 = $this->_scopeConfig->getValue('general/store_information/street_line2', ScopeInterface::SCOPE_WEBSITES);

        $adminStoreAPIKey = $this->_scopeConfig->getValue('carriers/droppashipping/api_key', ScopeInterface::SCOPE_WEBSITES);
        $adminStoreServiceKey = $this->_scopeConfig->getValue('carriers/droppashipping/service_key', ScopeInterface::SCOPE_WEBSITES);

        foreach ($order as $item) {
            $base_shipping_amount = $item['base_shipping_amount'];
            $productWeight = $item['weight'];
            $_bookingDimensions[] = $this->_booking_plugin_attributes(0, 0, 0, $productWeight);
        }

        $_quote_body = [
            "serviceId" => $adminStoreServiceKey,
            "platform" => "Magento",
            "pickUpPCode" => $adminStorePostcode,
            "dropOffPCode" => $dropOffpincode,
            "fromSuburb" => $adminStoreCity,
            "toSuburb" => $dropOffcity,
            "province" => $adminStoreRegion,
            "destinationProvince" => $dropOffRegion,
            "pickUpAddress" => $adminStoreStreet1 . ', ' . $adminStoreCity . ', ' . $adminStorePostcode . ', ' . $adminStoreRegion,
            "dropOffAddress" => implode(',', $dropOffAddress) . ', ' . $dropOffpincode . ', ' . $dropOffRegion,
            "pickUpCompanyName" => $adminStoreName,
            "dropOffCompanyName" => $customerCompany,
            "pickUpUnitNo" => $adminStoreStreet2,
            "dropOffUnitNo" => '',
            "customerName" => $customerName,
            "customerPhone" => $customerPhone,
            "customerEmail" => $customerEmail,
            "instructions" => '',
            "price" => $base_shipping_amount,
            "parcelDimensions" => $_bookingDimensions,
            'storeName' => ''
        ];

        try {
            $useCurlObject = new Curl($adminStoreAPIKey, $adminStoreServiceKey);

            $response = $useCurlObject->curl_endpoint($this->_booking_endpoint, $_quote_body, 'POST');
            $responseResults = json_decode($response, true);

            if ((float) $responseResults['price'] >= '05.00') {

                $this->_resources = $objectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
                $connection = $this->_resources->getConnection();

                $installCustomTable     = $this->_resources->getTableName('droppa_booking_object');
                $insertBookingObjectId  = "INSERT INTO " . $installCustomTable . "(booking_id) VALUES ('" . $responseResults['oid'] . "')";
                return $connection->query($insertBookingObjectId);
            }
        } catch (\Exception $exception) {
            $this->_logger->critical($exception);
        }
    }
}