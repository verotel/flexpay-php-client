<?php

require_once __DIR__.'/../src/Verotel/FlexPay/Client.php';
require_once 'config.php';

$brand = Verotel\FlexPay\Brand::create_from_merchant_id($FLEXPAYCONFIG['merchantId']);
$flexpayClient = new Verotel\FlexPay\Client(
    $FLEXPAYCONFIG['shopId'],
    $FLEXPAYCONFIG['signatureKey'],
    $brand
);

if (!$flexpayClient->validate_signature($_GET)){
    http_response_code(500);
    echo "ERROR - Invalid signature!";
    exit;
}

echo "OK";
