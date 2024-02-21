<?php

require_once __DIR__ . '/../src/Verotel/FlexPay/Brand.php';

use Verotel\FlexPay\Brand;

class VerotelFlexPayBrandTest extends PHPUnit\Framework\TestCase {
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

    function test_create_from_merchant_id__BitsafePay_brand() {
        $brand = Brand::create_from_merchant_id('9653000000000000');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\BitsafePay', $brand);
        $this->assertEquals(
            $brand->flexpay_URL(),
            'https://secure.bitsafepay.com/startorder'
        );
        $this->assertEquals(
            $brand->status_URL(),
            'https://secure.bitsafepay.com/salestatus'
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

    function test_create_from_merchant_id__PaintFests_brand() {
        $brand = Brand::create_from_merchant_id('9444000000000001');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\PaintFest', $brand);
        $this->assertEquals(
                $brand->flexpay_URL(),
                'https://secure.paintfestpayments.com/startorder'
        );
        $this->assertEquals(
                $brand->status_URL(),
                'https://secure.paintfestpayments.com/salestatus'
        );
    }

    function test_create_from_merchant_id__YoursafeDirect_brand() {
        $brand = Brand::create_from_merchant_id('9001000000000001');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\YoursafeDirect', $brand);
        $this->assertEquals(
                $brand->flexpay_URL(),
                'https://secure.yoursafedirect.com/startorder'
        );
        $this->assertEquals(
                $brand->status_URL(),
                'https://secure.yoursafedirect.com/salestatus'
        );
    }

    function test_create_from_merchant_id__GayCharge_brand() {
        $brand = Brand::create_from_merchant_id('9388000000000001');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\GayCharge', $brand);
        $this->assertEquals(
                $brand->flexpay_URL(),
                'https://secure.gaycharge.com/startorder'
        );
        $this->assertEquals(
                $brand->status_URL(),
                'https://secure.gaycharge.com/salestatus'
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

    function test_create_from_name__BitsafePay_brand() {
        $brand = Brand::create_from_name('BitsafePay');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\BitsafePay', $brand);
    }

    function test_create_from_name__Bill_brand() {
        $brand = Brand::create_from_name('Bill');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\Bill', $brand);
    }

    function test_create_from_name__PiantFest_brand() {
        $brand = Brand::create_from_name('PaintFest');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\PaintFest', $brand);
    }

    function test_create_from_name__YoursafeDirect_brand() {
        $brand = Brand::create_from_name('YoursafeDirect');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\YoursafeDirect', $brand);
    }

    function test_create_from_name__GayCharge_brand() {
        $brand = Brand::create_from_name('GayCharge');
        $this->assertInstanceOf('Verotel\FlexPay\Brand\GayCharge', $brand);
    }

    function test_create_from_name__unknown_brand() {
        try {
            Brand::create_from_name('UnknownBrand');
        } catch(Verotel\FlexPay\Exception $e) {
            $this->assertEquals("Invalid brand name", $e->getMessage());
            return;
        }

        $this->fail("Expected exception has not been raised");
    }
}
