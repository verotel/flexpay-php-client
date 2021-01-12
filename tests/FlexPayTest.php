<?php

require_once __DIR__ . '/../FlexPay.php';

class FlexPayTest extends PHPUnit\Framework\TestCase {

    function setUp() : void {
        $this->protocolVersion = '3.5';
        $this->secret = "zpXwe2D77g4P7ysGJcr3rY87TBYs6J";
        $this->params = array(
            'shopID'            => '68849',
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
        $this->baseUrl = 'https://secure.verotel.com/';


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
            FlexPay::get_signature( $this->secret, $this->params )
        );
    }

    function test_validate_signature__returns_true_if_correct() {
        $signedParams = array_merge( $this->params,
            array( 'signature' => strtoupper($this->signOfAll) ) );

        $this->assertEquals(
            true,
            FlexPay::validate_signature( $this->secret, $signedParams )
        );
    }

    function test_validate_signature__returns_true_for_old_sha1_signature() {
        $signedParams = array_merge( $this->params,
            array( 'signature' => strtoupper($this->signOfAll_old_sha1) ) );

        $this->assertEquals(
            true,
            FlexPay::validate_signature( $this->secret, $signedParams )
        );
    }

    function test_validate_signature__returns_false_if_incorrect() {
        $signedParams = array_merge( $this->params,
            array( 'custom2' => 'Your', 'signature' => $this->signOfAll ) );

        $this->assertEquals(
            false,
            FlexPay::validate_signature( $this->secret, $signedParams )
        );
    }

    function test_get_purchase_URL__returns_correct_url() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'purchase', 'version' => $this->protocolVersion) );

        $signature = FlexPay::get_signature( $this->secret, $signedParams );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=purchase'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            FlexPay::get_purchase_URL( $this->secret, $this->params )
        );
    }

    function test_get_purchase_URL__removes_empty_parameters() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'purchase', 'version' => $this->protocolVersion) );

        $signature = FlexPay::get_signature( $this->secret, $signedParams );

        $inputParams = array_merge($this->params, array(
            'custom2' => '', 'custom3' => null, 'unsigned1' => '', 'unsigned2' => null) );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=purchase'
                . '&version=' . $this->protocolVersion
                . '&signature=' . $signature,
            FlexPay::get_purchase_URL( $this->secret, $inputParams )
        );
    }

    function test_get_purchase_URL__parameter_with_zero_is_not_removed() {
        $signedParams = array_merge( $this->params,
            array('type'   => 'purchase', 'version' => $this->protocolVersion) );

        $signature = FlexPay::get_signature( $this->secret, $signedParams );

        $inputParams = array_merge( $this->params, array('zero' => 0) );

        $this->assertEquals(
            $this->baseUrl . 'startorder?' . $this->commonURLParams
                . '&type=purchase'
                . '&version=' . $this->protocolVersion
                . '&zero=0'
                . '&signature=' . $signature,
            FlexPay::get_purchase_URL( $this->secret, $inputParams )
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
};
