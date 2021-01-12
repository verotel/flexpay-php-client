<?php

require_once __DIR__ . '/../src/Verotel/FlexPay/Client.php';

class VerotelFlexPayClientTest extends PHPUnit\Framework\TestCase {

    function setUp() : void {
        $this->protocolVersion = '3.5';
        $this->secret = "zpXwe2D77g4P7ysGJcr3rY87TBYs6J";
        $this->shopId = '68849';
        $this->brand = Verotel\FlexPay\Brand::create_from_name('BitsafePay');

        $this->client = new Verotel\FlexPay\Client(
                                $this->shopId, $this->secret, $this->brand );

        $this->params = array(
            'saleID'            => '433456',
            'referenceID'       => '5566',
            'priceAmount'       => '0.00',
            'referenceID'       => 'reference1234',
            'priceCurrency'     => 'USD',
            'custom1'           => 'My',
            'description'       => 'My Dščřčřřěřě&?=blah123',
            'subscriptionType'  => 'RECURRING',
            'period'            => 'P1M',
            'name'              => 'My name',
            'trialAmount'       => '0.01',
            'trialPeriod'       => 'P3D',
            'backURL'           => 'http://backURL.test',
            'declineURL'        => 'http://declineURL.test',
            'cancelDiscountPercentage'  => '30',
            'blah'                      => 'something',
        );

        $this->signOfFiltered = 'c32809a80e3a97d4be5c05b8e241d32141b169c9d7d74294ce50ba313d6817b3';
        $this->signOfAll = 'a8c18e900fad7af686c3b6dc9f00f197f9d6ea210566ef0d81fb07555f23504d';
        $this->signOfAll_old_sha1 = '3650ddcc9360de60f4fc78604057c9f3246923cb';
        $this->baseUrl = 'https://secure.bitsafepay.com/';


        $this->commonURLParams
            = "backURL=http%3A%2F%2FbackURL.test"
            . "&blah=something"
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
            . "&trialAmount=0.01"
            . "&trialPeriod=P3D";
    }

    function test_get_signature__returns_correct_signature() {
        $this->assertEquals(
            $this->signOfFiltered,
            $this->client->get_signature( $this->params )
        );
    }

    function test_validate_signature__returns_true_if_correct() {
        $signedParams = array_merge( $this->params,
            array( 'signature' => strtoupper($this->signOfAll) ) );

        $this->assertEquals(
            true,
            $this->client->validate_signature( $signedParams )
        );
    }

    function test_validate_signature__returns_true_for_old_sha1_signature() {
        $signedParams = array_merge( $this->params,
            array( 'signature' => strtoupper($this->signOfAll_old_sha1) ) );

        $this->assertEquals(
            true,
            $this->client->validate_signature( $signedParams )
        );
    }

    function test_validate_signature__returns_false_if_incorrect() {
        $signedParams = array_merge( $this->params,
            array( 'custom2' => 'Your', 'signature' => $this->signOfAll ) );

        $this->assertEquals(
            false,
            $this->client->validate_signature( $signedParams )
        );
    }

    function test_constructor__raises_if_no_secret() {
        try {
            $client = new Verotel\FlexPay\Client( $this->shopId, '' );
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no secret given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_constructor__raises_if_no_shopID() {
        try {
            $client = new Verotel\FlexPay\Client( '', $this->secret );
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no shopID given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_purchase_URL__raises_if_no_params() {
        try {
            $this->client->get_purchase_URL('');
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no params given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_purchase_URL__raises_if_invalid_params() {
        try {
            $this->client->get_purchase_URL('bla');
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("invalid params", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_purchase_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'purchase', 'version' => $this->protocolVersion) );

        $signature = $this->client->get_signature( $signedParams );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=purchase'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            $this->client->get_purchase_URL( $this->params )
        );
    }

    function test_get_purchase_URL__removes_empty_parameters() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'purchase', 'version' => $this->protocolVersion) );

        $signature = $this->client->get_signature( $signedParams );

        $inputParams = array_merge($this->params, array(
            'custom2' => '', 'custom3' => null, 'unsigned1' => '', 'unsigned2' => null) );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=purchase'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            $this->client->get_purchase_URL( $inputParams )
        );
    }

    function test_get_purchase_URL__parameter_with_zero_is_not_removed() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'purchase', 'version' => $this->protocolVersion) );

        $signature = $this->client->get_signature( $signedParams );

        $inputParams = array_merge($this->params, array('zero' => 0) );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=purchase'
                . '&version=' . $this->protocolVersion
                . '&zero=0'
                . '&signature=' . $signature,
            $this->client->get_purchase_URL( $inputParams )
        );
    }

    function test_get_subscription_URL__raises_if_no_params() {
        try {
            $this->client->get_subscription_URL('');
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no params given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_subscription_URL__raises_if_invalid_params() {
        try {
            $this->client->get_subscription_URL('bla');
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("invalid params", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_subscription_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'subscription', 'version' => $this->protocolVersion) );

        $signature = $this->client->get_signature( $signedParams );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=subscription'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            $this->client->get_subscription_URL( $this->params )
        );
    }

    function test_get_status_URL__raises_if_no_params() {
        try {
            $this->client->get_status_URL('');
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("no params given", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_status_URL_raises_if_invalid_params() {
        try {
            $this->client->get_status_URL('bla');
        }

        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("invalid params", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_get_status_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
            array('version' => $this->protocolVersion) );

        $signature = $this->client->get_signature( $signedParams );

        $this->assertEquals(
            $this->baseUrl . 'salestatus?' . $this->commonURLParams
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            $this->client->get_status_URL( $this->params )
        );
    }

    function test_get_upgrade_subscription_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
                array(
                        'type' => 'upgradesubscription',
                        'version' => $this->protocolVersion,
                ));

        $signature = $this->client->get_signature( $signedParams );

        $this->assertEquals(
                $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=upgradesubscription'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
                $this->client->get_upgrade_subscription_URL( $this->params )
        );
    }

    function test_get_cancel_subscription_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
            array('version' => $this->protocolVersion) );

        $signature = $this->client->get_signature( $signedParams );

        $this->assertEquals(
            $this->baseUrl . 'cancel-subscription?' . $this->commonURLParams
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            $this->client->get_cancel_subscription_URL( $this->params )
        );
    }
};
