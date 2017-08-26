# AMP Product Example Module for Magento 2.x

## Synopsis

This Magento 2 module provides a template to output a product page in valid [AMP](https://www.ampproject.org) code. This module is not meant to be comprehensive for all Magento websites, but rather to demonstrate a proof-of-concept. This module was developed by [WompMobile](https://www.wompmobile.com).

## Motivation

To demonstrate...

1. how to create a module in Magento 2.x that loads a custom page template.
2. how to write a simple AMP page.
3. how to populate the template with basic product information.

## Installation

### Clone the repository
Clone the [AmpProductExample](https://github.com/wompmobile/Magento-Module-AmpProductExample) repository into `<magento_install_dir>/app/code/WompMobile/AmpProductExample`, where `<magento_install_dir>` should be replaced with the path to your Magento 2.x installation directory.

### Update the Magento database and schema
Run the following command to register the module:

    php <magento_install_dir>/bin/magento setup:upgrade

### Verify the module is installed
Enter the following command:

    php <magento_install_dir>/bin/magento module:status

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
