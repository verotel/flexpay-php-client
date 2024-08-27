<?php

require_once __DIR__ . '/../src/Verotel/FlexPay/Client.php';

class VerotelFlexPayClientTest extends PHPUnit\Framework\TestCase {
    private $params = array(
        'saleID' => '433456',
        'priceAmount' => '0.00',
        'referenceID' => 'reference1234',
        'priceCurrency' => 'USD',
        'custom1' => 'My',
        'description' => 'My Dščřčřřěřě&?=blah123',
        'subscriptionType' => 'RECURRING',
        'period' => 'P1M',
        'name' => 'My name',
        'trialAmount' => '0.01',
        'trialPeriod' => 'P3D',
        'successURL' => 'http://backURL.test',
        'declineURL' => 'http://declineURL.test',
        'cancelDiscountPercentage' => '30',
        'blah' => 'something',
    );
    private $baseUrl = 'https://secure.bitsafepay.com/';
    /**
     * @var \Verotel\FlexPay\Client
     */
    private $client;
    private $shopId = '68849';
    private $secret = "zpXwe2D77g4P7ysGJcr3rY87TBYs6J";
    private $protocolVersion = '4';
    /**
     * @var string
     */
    private $commonURLParams
        = "blah=something"
        . "&cancelDiscountPercentage=30"
        . "&custom1=My"
        . "&declineURL=http%3A%2F%2FdeclineURL.test"
        . "&description="
        . "My+D%C5%A1%C4%8D%C5%99%C4%8D%C5%99%C5%99"
        . "%C4%9B%C5%99%C4%9B%26%3F%3Dblah123"
        . "&name=My+name"
        . "&period=P1M"
        . "&priceAmount=0.00"
        . "&priceCurrency=USD"
        . "&referenceID=reference1234"
        . "&saleID=433456"
        . "&shopID=68849"
        . "&subscriptionType=RECURRING"
        . "&successURL=http%3A%2F%2FbackURL.test"
        . "&trialAmount=0.01"
        . "&trialPeriod=P3D";

    function setUp(): void {
        $brand = Verotel\FlexPay\Brand::create_from_name('BitsafePay');
        $this->client = new Verotel\FlexPay\Client($this->shopId, $this->secret, $brand);
    }

    function test_get_signature_and_validate() {
        $signatureSource = [$this->secret];
        $flexpayParams = [
            'cancelDiscountPercentage' => '30',
            'custom1' => 'custom1',
            'declineURL' => 'http://declineURL.test',
            'description' => 'My Dščřčřřěřě&?=blah123',
            'name' => 'My name',
            'period' => 'P1M',
            'priceAmount' => '7.00',
            'priceCurrency' => 'USD',
            'referenceID' => 'reference1234',
            'shopID' => $this->shopId,
            'subscriptionType' => 'RECURRING',
            'successURL' => 'http://backURL.test',
            'trialAmount' => '0.01',
            'trialPeriod' => 'P3D',
        ];

        foreach ($flexpayParams as $key => $value) {
            $signatureSource[] = "$key=$value";
        }

        $expectedSignature = $this->client->get_signature(array_merge($flexpayParams, ["foo" => "bar"]));
        $this->assertEquals(
            strtolower(hash("sha256", join(":", $signatureSource))),
            $expectedSignature
        );

        $this->assertTrue(
            $this->client->validate_signature(
                array_merge($flexpayParams, ["signature" => $expectedSignature])
            )
        );
    }

    function test_get_signature_takes_also_success_url_into_account() {
        $paramsWithoutSuccessUrl = $this->params;
        unset($paramsWithoutSuccessUrl["successURL"]);
        $this->assertNotEquals(
            $this->client->get_signature($this->params),
            $this->client->get_signature($paramsWithoutSuccessUrl)
        );
    }

    function test_validate_signature__returns_true_for_old_sha1_signature() {
        $signedParams = array_merge($this->params, ['signature' => "fc63f38e2722d2e5bc4f2044ad3ffb2051e89643"]);

        $this->assertTrue($this->client->validate_signature($signedParams));
    }

    function test_validate_signature__returns_false_if_incorrect() {
        $signedParams = array_merge($this->params, ['custom2' => 'Your', 'signature' => "foo"]);

        $this->assertFalse($this->client->validate_signature($signedParams));
    }

    function test_constructor__raises_if_no_secret() {
        try {
            new Verotel\FlexPay\Client($this->shopId, '');
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no secret given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_constructor__raises_if_no_shopID() {
        try {
            new Verotel\FlexPay\Client('', $this->secret);
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no shopID given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_purchase_URL__raises_if_no_params() {
        try {
            $this->client->get_purchase_URL([]);
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no params given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_purchase_URL__raises_if_invalid_params() {
        try {
            $this->client->get_purchase_URL('bla');
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("invalid params", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_purchase_URL__returns_correct_url() {
        $signedParams = array_merge($this->params, ['type' => 'purchase', 'version' => $this->protocolVersion]);

        $signature = $this->client->get_signature($signedParams);

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
            . '&type=purchase'
            . '&version=' . $this->protocolVersion
            . '&signature=' . $signature,
            $this->client->get_purchase_URL($this->params)
        );
    }

    function test_get_purchase_URL__with_deprecated_backURL_works() {
        $base = [
            'description' => 'foo-desc',
            'priceAmount' => '7.00',
            'priceCurrency' => 'USD'
        ];

        $backUrl = $this->client->get_purchase_URL(array_merge($base, ['backURL' => 'http://backURL.test']));
        $successUrl = $this->client->get_purchase_URL(array_merge($base, ['successURL' => 'http://backURL.test']));

        $this->assertEquals($backUrl, $successUrl);
    }

    /**
     * @dataProvider methodsWithBackUrlCompatibility
     */
    function test_empty_backURL_does_not_override_successURL($method_name) {
        $base = [
            'description' => 'foo-desc',
            'priceAmount' => '7.00',
            'priceCurrency' => 'USD'
        ];

        $withEmptyBackUrl = $this->client->$method_name(
            array_merge($base, ['backURL' => '', 'successURL' => 'https://successURL.test'])
        );

        $onlySuccessUrl = $this->client->$method_name(
            array_merge($base, ['successURL' => 'https://successURL.test'])
        );

        $this->assertEquals($onlySuccessUrl, $withEmptyBackUrl);
    }

    function test_get_purchase_URL__removes_empty_parameters() {
        $signedParams = array_merge($this->params, ['type' => 'purchase', 'version' => $this->protocolVersion]);
        $signature = $this->client->get_signature($signedParams);
        $inputParams = array_merge(
            $this->params,
            ['custom2' => '', 'custom3' => null, 'unsigned1' => '', 'unsigned2' => null]
        );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
            . '&type=purchase'
            . '&version=' . $this->protocolVersion
            . '&signature=' . $signature,
            $this->client->get_purchase_URL($inputParams)
        );
    }

    function test_get_purchase_URL__parameter_with_zero_is_not_removed() {
        $signedParams = array_merge($this->params, ['type' => 'purchase', 'version' => $this->protocolVersion]);
        $signature = $this->client->get_signature($signedParams);
        $inputParams = array_merge($this->params, array('zero' => 0));

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
            . '&type=purchase'
            . '&version=' . $this->protocolVersion
            . '&zero=0'
            . '&signature=' . $signature,
            $this->client->get_purchase_URL($inputParams)
        );
    }

    function test_get_subscription_URL__raises_if_no_params() {
        try {
            $this->client->get_subscription_URL([]);
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no params given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_subscription_URL__raises_if_invalid_params() {
        try {
            $this->client->get_subscription_URL('bla');
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("invalid params", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_subscription_URL__returns_correct_url() {
        $signedParams = array_merge($this->params, ['type' => 'subscription', 'version' => $this->protocolVersion]);

        $signature = $this->client->get_signature($signedParams);

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
            . '&type=subscription'
            . '&version=' . $this->protocolVersion
            . '&signature=' . $signature,
            $this->client->get_subscription_URL($this->params)
        );
    }

    function test_get_status_URL__raises_if_no_params() {
        try {
            $this->client->get_status_URL([]);
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no params given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_status_URL_raises_if_invalid_params() {
        try {
            $this->client->get_status_URL('bla');
        } catch (Verotel\FlexPay\Exception $e) {
            $this->assertEquals("invalid params", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_status_URL__returns_correct_url() {
        $signedParams = array_merge($this->params, array('version' => $this->protocolVersion));

        $signature = $this->client->get_signature($signedParams);

        $this->assertEquals(
            $this->baseUrl . 'salestatus?' . $this->commonURLParams
            . '&version=' . $this->protocolVersion
            . '&signature=' . $signature,
            $this->client->get_status_URL($this->params)
        );
    }

    function test_get_upgrade_subscription_URL__returns_correct_url() {
        $signedParams = array_merge(
            $this->params,
            [
                'type' => 'upgradesubscription',
                'version' => $this->protocolVersion,
            ]
        );

        $signature = $this->client->get_signature($signedParams);

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
            . '&type=upgradesubscription'
            . '&version=' . $this->protocolVersion
            . '&signature=' . $signature,
            $this->client->get_upgrade_subscription_URL($this->params)
        );
    }

    function test_get_upgrade_subscription_URL__with_deprecated_backURL_works() {
        $base = [
            'description' => 'foo-desc',
            'priceAmount' => '7.00',
            'priceCurrency' => 'USD'
        ];

        $backUrl = $this->client->get_upgrade_subscription_URL(array_merge($base, ['backURL' => 'http://backURL.test']));
        $successUrl = $this->client->get_upgrade_subscription_URL(array_merge($base, ['successURL' => 'http://backURL.test']));

        $this->assertEquals($backUrl, $successUrl);
    }


    function test_get_cancel_subscription_URL__returns_correct_url() {
        $signedParams = array_merge($this->params, ['version' => $this->protocolVersion]);

        $signature = $this->client->get_signature($signedParams);

        $this->assertEquals(
            $this->baseUrl . 'cancel-subscription?' . $this->commonURLParams
            . '&version=' . $this->protocolVersion
            . '&signature=' . $signature,
            $this->client->get_cancel_subscription_URL($this->params)
        );
    }

    function test_get_subscription_URL__with_deprecated_backURL_works() {
        $base = [
            'description' => 'foo-desc',
            'priceAmount' => '7.00',
            'priceCurrency' => 'USD'
        ];

        $backUrl = $this->client->get_subscription_URL(array_merge($base, ['backURL' => 'http://backURL.test']));
        $successUrl = $this->client->get_subscription_URL(array_merge($base, ['successURL' => 'http://backURL.test']));

        $this->assertEquals($backUrl, $successUrl);
    }

    function test_get_purchase_URL__with_cpsp_fields_works() {
        $purchase_URL = $this->client->get_purchase_URL(array_merge([
            'description' => 'foo-desc',
            'priceAmount' => '7.00',
            'priceCurrency' => 'USD',
            'mcc' => '1234',
            'subCreditorName' => 'Foo sub merchant',
            'subCreditorId' => '123456',
            'subCreditorCountry' => 'NL',
            'paymentMethod' => 'IDEAL',
        ]));

        $this->assertStringContainsString("mcc", $purchase_URL);
        $this->assertStringContainsString("subCreditorName", $purchase_URL);
        $this->assertStringContainsString("subCreditorId", $purchase_URL);
        $this->assertStringContainsString("subCreditorCountry", $purchase_URL);
    }

    function test_get_purchase_URL_cpsp_fields_are_signed() {
        $baseParams = [
            'description' => 'foo-desc',
            'priceAmount' => '7.00',
            'priceCurrency' => 'USD',
        ];
        $extraParamItems = [
            ['mcc' => '1234'],
            ['subCreditorName' => 'Foo sub merchant'],
            ['subCreditorId' => '123456'],
            ['subCreditorCountry' => 'NL'],
        ];
        $baseSignature = $this->client->get_signature(
            array_merge($baseParams)
        );

        foreach ($extraParamItems as $item) {
            $cpspSignature = $this->client->get_signature(
                array_merge($baseParams, $item)
            );

            $this->assertNotEquals($cpspSignature, $baseSignature);
        }
    }

    public function methodsWithBackUrlCompatibility(): array
    {
        return array(
            "get_purchase_URL" => array("get_purchase_URL"),
            "get_subscription_URL" => array("get_subscription_URL"),
            "get_upgrade_subscription_URL" => array("get_upgrade_subscription_URL"),
        );
    }
}
