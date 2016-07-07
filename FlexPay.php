<?php

/**
 * FlexPay PHP library version 3.2
 */
final class FlexPay {

    const FLEXPAY_URL       = 'https://secure.verotel.com/startorder';
    const STATUS_URL        = 'https://secure.verotel.com/salestatus';
    const PROTOCOL_VERSION  = '3.2';

    /**
     * Generates SHA1 signature
     * @param string $secret
     * @param array $params
     * @return string SHA1 encoded signature
     */
    public static function get_signature($secret, $params) {
        $filtered = self::_filter_params($params);
        return self::_signature($secret, $filtered);
    }


    /**
     * Validates signature
     * @param string $secret
     * @param array $params just params allowed with signature
     * @return bool
     */
    public static function validate_signature($secret, $params) {
        $sign1 = strtolower($params['signature']);
        unset($params['signature']);
        $sign2 = self::_signature($secret, $params);
        return ($sign1 === $sign2) ? true : false;
    }


    /**
     * @param string $secret
     * @param array $params
     * @return string Purchase URL
     */
    public static function get_purchase_URL($secret, $params) {
        return self::_generate_URL(self::FLEXPAY_URL, $secret, 'purchase', $params);
    }


    /**
     * @param string $secret
     * @param array $params
     * @return string subscription URL
     */
    public static function get_subscription_URL($secret, $params) {
        return self::_generate_URL(self::FLEXPAY_URL, $secret, 'subscription', $params);
    }


    /**
     * @param string $secret
     * @param array $params
     * @return string status URL
     */
    public static function get_status_URL($secret, $params) {
        return self::_generate_URL(self::STATUS_URL, $secret, NULL, $params);
    }


    /**
     * Common function for generating signature
     * @param string $secret
     * @param array $params
     * @return string
     */
    private static function _signature($secret, $params) {
        $outArray = array($secret);
        ksort($params, SORT_REGULAR);
        foreach ($params as $key => $value) {
            array_push($outArray, "$key=$value");
        }

        return strtolower(sha1(join(":", $outArray)));
    }


    /**
     * Returns URL
     * @param string $baseURL e.g. http://www.xyz.com
     * @param string $secret
     * @param string $type
     * @param array $params URL params
     * @return string URL
     * @throws Exception if any parameter is invalid
     */
    private static function _generate_URL($baseURL, $secret, $type, $params) {
        if (!isset($secret) || !is_string($secret) || empty($secret)) {
            throw new Exception("no secret given");
        }

        if (!isset($params) || empty($params)) {
            throw new Exception("no params given");
        }

        if (!is_array($params)) {
            throw new Exception("invalid params");
        }

        if (!empty($type)){
            $params['type'] = $type;
        }

        $params['version'] = self::PROTOCOL_VERSION;

        ksort($params, SORT_REGULAR);
        $outArray = array();
        foreach ($params as $key => $value) {
            if(($value !== "") && ($value !== null)) {
                $outArray[$key] = $value;
            }
        }

        $signature = self::get_signature($secret, $outArray);
        $outArray['signature'] = $signature;

        return self::_build_URL($baseURL, $outArray);
    }


    /**
     * Returns URL string
     * @param string $baseURL
     * @param array $params
     * @return string URL
     */
    private static function _build_URL($baseURL, $params) {
        $arr = array();

        foreach ($params as $key => $value) {
            $arr[] = "$key=" . urlencode($value);
        }
        return $baseURL . "?" . join("&", $arr);
    }


    /**
     * Filters out unsupported parameters
     * @param array $params unfiltered URL params
     * @return array filtered parameters
     */
    private static function _filter_params($params) {
        $keys = array_keys($params);
        $filtered = array();
        $regexp = '/^(
            version
            | shopID
            | price(Amount|Currency)
            | paymentMethod
            | description
            | referenceID
            | saleID
            | custom[123]
            | subscriptionType
            | period
            | name
            | trialAmount
            | trialPeriod
            | cancelDiscountPercentage
            | type
            | backURL
            )$/x';

        foreach ($keys as $key) {
            if (preg_match($regexp, $key)) {
                $filtered[$key] = $params[$key];
            }
        }

        return $filtered;
    }
}
