<?php

namespace Droppa\DroppaShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Droppa\DroppaShipping\Model\Curl;

class PaymentsObserver implements ObserverInterface
{
    public $_resources;
    public $_bookingObjectID;
    protected $_logger;
    protected $_scopeConfig;
    protected $PROD_CONFIRM_PAYMENT_SERVEICE = 'https://www.droppa.co.za/droppa/services/plugins/confirm/';

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $adminStoreAPIKey = $this->_scopeConfig->getValue('carriers/droppashipping/api_key', ScopeInterface::SCOPE_WEBSITES);
        $adminStoreServiceKey = $this->_scopeConfig->getValue('carriers/droppashipping/service_key', ScopeInterface::SCOPE_WEBSITES);

        $useCurlObject = new Curl($adminStoreAPIKey, $adminStoreServiceKey);

        $objectManager      = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_resources   = $objectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection         = $this->_resources->getConnection();

        $installCustomTable = $this->_resources->getTableName('droppa_booking_object');
        $LastSavedOID       = "SELECT booking_id FROM $installCustomTable ORDER BY id DESC LIMIT 1";
        $_collect           = $connection->fetchAll($LastSavedOID);
        $connection->query($LastSavedOID);

        foreach ($_collect as $valueBookingId) {
            $this->_bookingObjectID = $valueBookingId['booking_id'];
        }

        return $useCurlObject->curl_endpoint($this->PROD_CONFIRM_PAYMENT_SERVEICE . $this->_bookingObjectID, '', 'POST');
    }
}