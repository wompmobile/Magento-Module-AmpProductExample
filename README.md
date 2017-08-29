# AMP Product Example Module for Magento 2.x

## Synopsis

This Magento 2 module provides a template to output a product page in valid [AMP](https://www.ampproject.org) code. This module is not meant to be comprehensive for all Magento websites, but rather to demonstrate a proof-of-concept. This module was developed by [WompMobile](https://www.wompmobile.com).

## Motivation

To demonstrate...

1. how to create a module in Magento 2.x that loads a custom page template.
2. how to write a simple AMP page.
3. how to populate the template with basic product information.

## Installation

### Option 1: Using composer

1. Update `composer.json` at the root of your Magento 2.x installation directory:

    a. Add the following to the `repositories` array:

        {
            "type": "vcs",
            "url":  "git@github.com:wompmobile/Magento-Module-AmpProductExample.git"
        }

    b. Add the following to the `require` object:

        "wompmobile/module-amp-product-example": "dev-master"

1. Fetch the module:

        composer update wompmobile/module-amp-product-example

1. Register the module:

        magento setup:upgrade

1. Verify the module is installed:

        magento module:status

    If installation was successful, `WompMobile_AmpProductExample` will appear under the `List of enabled modules`.

### Option 2: Manual installation

1. Clone [github.com/wompmobile/Magento-Module-AmpProductExample](https://github.com/wompmobile/Magento-Module-AmpProductExample) into `<magento_install_dir>/app/code/wompmobile/module-amp-product-example`, where `<magento_install_dir>` should be replaced with the path to your Magento 2.x installation directory.

1. Register the module:

        magento setup:upgrade

1. Verify the module is installed:

        magento module:status

    If installation was successful, `WompMobile_AmpProductExample` will appear under the `List of enabled modules`.

## Usage

Load a product AMP page by visiting `<your-domain>/amp/?sku=<product-sku>` where `<your-domain>` should be replaced with the domain of your website and `<product-sku>` should be replaced with a valid product SKU from your catalog.

## Tests

This module doesn't contain test units.

## Contributors

[WompMobile](https://www.wompmobile.com)

## Acknowledgments

Thanks to Alan Kent for discussions about this module.

## License

Copyright 2017 WompMobile, Inc.  
[Apache License Version 2.0](LICENSE)
