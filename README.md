# Magento 2 Droppa Shipping Plugin

```
Donate link: https://droppa.co.za/magento-shipping-plugin
Tags: Shipping, Courier
Tested up to: 4.0.1
Stable tag: 1.0.0
Requires PHP: 7.0
```

## Description

> The Droppa Shipping for Magento module or plugin allows you to ships goods everywhere across South Africa.
* This Shipping method uses Express Courier to deliver your parcels within 24 to 72 hours.
* Our Express service is available nationwide.
* No collections/deliveries on weekends

## System Requirements

* PHP 7.2 is required to run the module.
* PHP 5.6 - 7.0 can be supported on request.

## Before Installation

* All plugin users must be clients with Droppa Group and should have a Business account along with an API key and Service ID. Please contact Droppa Group administration to help set it up - itsupport@droppa.co.za
* Please make sure that Magento Module extension has been installed in your Magento platform.

## Installation

* Navigate to Magento Marketplace https://marketplace.magento.com/extensions/accounting-finance.html, search for Droppa Shipping Plugin and download the module.
* Open your hosting site on where your Magento platform is hosted and open up App > Code folder and save the Droppa Shipping Plugin in it.
* Open up your terminal pointing to the Magento project and run the following commands:

```
php ./bin/magento module:enable droppa_droppashipping
php ./bin/magento setup:upgrade
php ./bin/magento setup:di:compile
php ./bin/magento setup:static-content:deploy -f
php ./bin/magento cache:clean
php ./bin/magento cache:flush
```

* Navigate to your Magento Admin site > Stores > Configurations > Sales > Delivery Methods.
* Enable the Droppa Shipping module, place in your API and Service keys generated from the Dropp Group platform by our developers. itsupport@droppa.co.za

## Frequently Asked Questions

1. Does the Droppa Shipping plugin requires an authorization access key?

- Yes. In most cases, whenever working with Apps, Modules or plugins, an Oauth key is required to allow data integration with the application. The API and Service Keys are designated to the retail store owner and are attached to the information within.

2. How long does it take to activate the plugin.

- As soon as the plugin is downloaded and activated on the Magento dashboard, users can communicate with the support team itsupport@droppa.co.za for instructions on activating the plugin.

## Screenshots

1. Settings section for adding up API and Service keys.
![Settings section for adding up API and Service keys](https://user-images.githubusercontent.com/73278719/112615171-d2b85b00-8e2a-11eb-9edb-63cab4e29ee1.PNG)

2. The output at Checkout page.

![The output at Checkout page](https://user-images.githubusercontent.com/73278719/112615206-dea41d00-8e2a-11eb-8cde-55d04d4bd2a7.PNG)



## Support

* Should you encounter any issue with the plugin, please revert back to our support developer team itsupport@droppa.co.za

## Changelog

### 1.0.0

* This is the official release of this version.