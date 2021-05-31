<?php

/**
 * @author Jack Manamela
 * @copyright Copyright (c) 2010 Droppa Group (https://www.droppa.co.za/droppa)
 * @package Droppa_DroppaShipping
 */

use Magento\Framework\Component\ComponentRegistrar;

$name = implode('_', array_map(
    function ($part) {
        return implode(array_map('ucfirst', explode('-', $part)));
    },
    array_slice(explode(DIRECTORY_SEPARATOR, __DIR__), -2, 2)
));

ComponentRegistrar::register(ComponentRegistrar::MODULE, $name, __DIR__);