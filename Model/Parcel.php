<?php

namespace Droppa\DroppaShipping\Model;

class Parcel
{
    public $itemMass;
    public $width;
    public $height;
    public $length;
    public $description;

    function __construct($itemMass, $width, $height, $length)
    {
        $this->itemMass = $itemMass;
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
    }

    function get_itemMass()
    {
        return $this->itemMass;
    }

    function get_width()
    {
        return $this->width;
    }

    function get_height()
    {
        return $this->height;
    }

    function get_length()
    {
        return $this->length;
    }
}
