<?php

require_once __DIR__ . '/../src/Verotel/FlexPay/Brand.php';

use Verotel\FlexPay\Brand;

class VerotelFlexPayBrandTest extends PHPUnit_Framework_TestCase {
    function test_create_from_merchant_id__Verotel_brand() {
        $brand = Brand::create_from_merchant_id('9804000000000000');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\Verotel', $brand);
        $this->assertEquals(
            $brand->flexpay_URL(),
            'https://secure.verotel.com/startorder'
        );
        $this->assertEquals(
            $brand->status_URL(),
            'https://secure.verotel.com/salestatus'
        );
    }

    function test_create_from_merchant_id__CardBilling_brand() {
        $brand = Brand::create_from_merchant_id('9762000000000000');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\CardBilling', $brand);
        $this->assertEquals(
            $brand->flexpay_URL(),
            'https://secure.billing.creditcard/startorder'
        );
        $this->assertEquals(
            $brand->status_URL(),
            'https://secure.billing.creditcard/salestatus'
        );
    }

    function test_create_from_merchant_id__FreenomPay_brand() {
        $brand = Brand::create_from_merchant_id('9653000000000000');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\FreenomPay', $brand);
        $this->assertEquals(
            $brand->flexpay_URL(),
            'https://secure.freenompay.com/startorder'
        );
        $this->assertEquals(
            $brand->status_URL(),
            'https://secure.freenompay.com/salestatus'
        );
    }

    function test_create_from_merchant_id__Bill_brand() {
        $brand = Brand::create_from_merchant_id('9511000000004236');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\Bill', $brand);
        $this->assertEquals(
                $brand->flexpay_URL(),
                'https://secure.bill.creditcard/startorder'
        );
        $this->assertEquals(
                $brand->status_URL(),
                'https://secure.bill.creditcard/salestatus'
        );
    }

    function test_create_from_merchant_id__unknown_brand() {
        try {
            $brand = Brand::create_from_merchant_id('1234000000000000');
        }
        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("Invalid merchant ID", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }

    function test_create_from_name__Verotel_brand() {
        $brand = Brand::create_from_name('Verotel');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\Verotel', $brand);
    }

    function test_create_from_name__CardBilling_brand() {
        $brand = Brand::create_from_name('CardBilling');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\CardBilling', $brand);
    }

    function test_create_from_name__FreenomPay_brand() {
        $brand = Brand::create_from_name('FreenomPay');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\FreenomPay', $brand);
    }

    function test_create_from_name__Bill_brand() {
        $brand = Brand::create_from_name('Bill');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\Bill', $brand);
    }

    function test_create_from_name__unknown_brand() {
        try {
            $brand = Brand::create_from_name('UnknownBrand');
        }
        catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("Invalid brand name", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }
};

