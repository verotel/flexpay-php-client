<?php

require_once __DIR__ . '/../FlexPay.php';

class FlexPayTest extends PHPUnit\Framework\TestCase {

    function setUp() : void {
        $this->protocolVersion = '4';
        $this->secret = "zpXwe2D77g4P7ysGJcr3rY87TBYs6J";
        $this->params = array(
            'shopID'            => '68849',
            'saleID'            => '433456',
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
            'successURL'        => 'http://backURL.test',
            'declineURL'        => 'http://declineURL.test',
            'cancelDiscountPercentage'  => '30',
            'blah'                      => 'something',
        );

        $this->signOfFiltered = 'ff5cf9bcc0497c8cb40087065e5016dcfbe2efd23327f6c67614fa23537c24b2';
        $this->signOfAll = 'e50b4e92fcd0a21114d3bf3a64f888edfbc784df170e70ee165c4350fe9ffc3c';
        $this->signOfAll_old_sha1 = '3650ddcc9360de60f4fc78604057c9f3246923cb';
        $this->baseUrl = 'https://secure.verotel.com/';


        $this->commonURLParams
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
    }

    function test_get_signature__returns_correct_signature() {
        $this->assertEquals(
            $this->signOfFiltered,
            FlexPay::get_signature( $this->secret, $this->params )
        );
    }

    function test_validate_signature__returns_true_if_correct() {
        $signedParams = array_merge( $this->params,
            array( 'signature' => strtoupper($this->signOfAll) ) );

        $this->assertTrue(
            FlexPay::validate_signature($this->secret, $signedParams)
        );
    }

    function test_get_cancel_subscription_URL_works() {
        $signedParams = array_merge($this->params, ['version' => $this->protocolVersion]);

        $signature = FlexPay::get_signature($this->secret, $signedParams);

        $this->assertEquals(
            $this->baseUrl . 'cancel-subscription?' . $this->commonURLParams
            . '&version=' . $this->protocolVersion
            . '&signature=' . $signature,
            FlexPay::get_cancel_subscription_URL( $this->secret, $this->params )
        );
    }

    function test_get_purchase_URL__returns_correct_url() {
        $signedParams = array_merge($this->params, ['type' => 'purchase', 'version' => $this->protocolVersion]);

        $signature = FlexPay::get_signature($this->secret, $signedParams);

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=purchase'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            FlexPay::get_purchase_URL( $this->secret, $this->params )
        );
    }

    function test_get_subscription_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'subscription', 'version' => $this->protocolVersion) );

        $signature = FlexPay::get_signature( $this->secret, $signedParams );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=subscription'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            FlexPay::get_subscription_URL( $this->secret, $this->params )
        );
    }

    function test_get_status_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
            array('version' => $this->protocolVersion) );

        $signature = FlexPay::get_signature( $this->secret, $signedParams );

        $this->assertEquals(
            $this->baseUrl . 'salestatus?' . $this->commonURLParams
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            FlexPay::get_status_URL( $this->secret, $this->params )
        );
    }

    function test_get_upgrade_subscription_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
                array('type'   => 'upgradesubscription', 'version' => $this->protocolVersion) );

        $signature = FlexPay::get_signature( $this->secret, $signedParams );

        $this->assertEquals(
                $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=upgradesubscription'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
                FlexPay::get_upgrade_subscription_URL( $this->secret, $this->params )
        );
    }
}
