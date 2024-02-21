# Verotel FlexPay library

![Tests Status](https://github.com/verotel/flexpay-php-client/actions/workflows/php.yml/badge.svg)

This library allows you to use Verotel payment gateway and accept credit cards and other payment methods on your website.

## Official Documentation

[Documentation for the library can be found on the Control Center website](https://controlcenter.verotel.com/flexpay-doc/).

## Installation via Composer

```
composer require verotel/flexpay-php-client
```

## Download manually

[**Download latest Release**](https://github.com/verotel/flexpay-php-client/releases/latest)

## Usage

### Composer
```php
require_once 'vendor/autoload.php';
```

### Direct require
```php
require_once '<path-to-flexpay-php-client>/src/Verotel/FlexPay/Client.php';
```

### Construction of client

```php
// get your brand instance
$brand = Verotel\FlexPay\Brand::create_from_merchant_id(/* Your customer ID */ '9804000000000000');

$flexpayClient = new Verotel\FlexPay\Client(/* shop ID */ 12345, "FlexPay Signature Key", $brand);
```

### Obtaining of purchase payment url

```php
$purchaseUrl = $flexpayClient->get_purchase_URL([
    "priceAmount" => 2.64,
    "priceCurrency" => "EUR",
    "description" => "Test purchase",
]);
```

### Obtaining of cancel subscription url

```php
$cancelUrl = $flexpayClient->get_cancel_subscription_URL([ "saleID" => 12345 ]);
```

### Validation of postback parameters

```php
if (!$flexpayClient->validate_signature($_GET)){
    http_response_code(500);
    echo "ERROR - Invalid signature!";
    exit;
}

// handle correct postback
...

echo "OK";
```

## License

The Verotel Flexpay PHP library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
