<?php
namespace Verotel\FlexPay;

require_once __DIR__."/Exception.php";

class Brand {
    private static $brandByMerchantPrefix = array(
        '9804' => 'Verotel',
        '9762' => 'CardBilling',
        '9653' => 'BitsafePay',
        '9511' => 'Bill',
        '9444' => 'PaintFest',
        '9388' => 'GayCharge',
        '9001' => 'YoursafeDirect',
    );

    public static function create_from_merchant_id($merchantID){
        $merchantPrefix = substr($merchantID, 0, 4);
        if (!array_key_exists($merchantPrefix, static::$brandByMerchantPrefix)){
            throw new Exception("Invalid merchant ID");
        }
        return static::create_from_name(static::$brandByMerchantPrefix[$merchantPrefix]);
    }

    public static function create_from_name($brandName) {
        if (!array_search($brandName, static::$brandByMerchantPrefix)){
            throw new Exception("Invalid brand name");
        }
        require_once __DIR__."/Brand/".$brandName.".php";
        $brandClass = "Verotel\\FlexPay\\Brand\\".$brandName;
        return new $brandClass();
    }
}

