<?php

namespace Droppa\DroppaShipping\Model;

class Quotes
{
    public $pickUpPCode;
    public $dropOffPCode;
    public $weight;

    public function __construct($pickUpPCode, $dropOffPCode, $weight)
    {
        $this->pickUpPCode      = $pickUpPCode;
        $this->dropOffPCode     = $dropOffPCode;
        $this->weight           = $weight;
    }

    function get_pickUpCode()
    {
        return $this->pickUpPCode;
    }

    function get_dropOffCode()
    {
        return $this->dropOffPCode;
    }

    function get_weight()
    {
        return $this->weight;
    }
}
