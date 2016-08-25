<?php

require_once __DIR__.'/../src/Verotel/FlexPay/Client.php';
require_once 'config.php';

$brand = Verotel\FlexPay\Brand::create_from_merchant_id($FLEXPAYCONFIG['merchantId']);
$flexpayClient = new Verotel\FlexPay\Client(
    $FLEXPAYCONFIG['shopId'],
    $FLEXPAYCONFIG['signatureKey'],
    $brand
);

$purchaseUrl = $flexpayClient->get_purchase_URL([
    "priceAmount" => 2.64,
    "priceCurrency" => "EUR",
    "description" => "Test purchase",
]);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <title>Verotel FlexPay - examples</title>
    </head>
    <body>
        <h1>Verotel FlexPay</h1>
        <h2>Purchase</h2>
        <p>
            <a href="<?php echo htmlspecialchars($purchaseUrl) ?>">Pay 2.64 EUR</a>
        </p>
    </body>
</html>
