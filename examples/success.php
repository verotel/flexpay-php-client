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

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <title>Verotel FlexPay - examples</title>
    </head>
    <body>
        <h1>Verotel FlexPay</h1>
        <h2>Success</h2>
    </body>
</html>
