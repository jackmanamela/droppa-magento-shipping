<?php

namespace Droppa\DroppaShipping\Model;

class Curl
{
    protected $_scopeConfig;
    protected $_adminStoreAPIKey;
    protected $_adminStoreServiceKey;

    public function __construct($adminStoreAPIKey, $adminStoreServiceKey) {
        $this->_adminStoreAPIKey       = $adminStoreAPIKey;
        $this->_adminStoreServiceKey   = $adminStoreServiceKey;
    }

    public function curl_endpoint($endpoint, $body, $method = 'GET')
    {
        $ch = curl_init($endpoint);
        
        \curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, true);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, 0);
        \curl_setopt($ch, \CURLOPT_HEADER, false);
        \curl_setopt($ch, \CURLOPT_POST, 1);
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, $method);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, \json_encode($body));
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, [ "Content-Type: application/json", "Connection: Keep-Alive", "Accept: application/json", "Authorization: Bearer {$this->_adminStoreAPIKey}:{$this->_adminStoreServiceKey}" ]);

        $response_json = curl_exec($ch);

        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status >= 200 && $status <= 308) {
            return $response_json;
        } else {
            return trigger_error("CURL Status Error: {$status}, {$curl_error}, {$curl_errno} => {$response_json}", E_USER_ERROR);
        }

        curl_close($ch);
    }
}
