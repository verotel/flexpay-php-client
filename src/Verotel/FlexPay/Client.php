<?php
namespace Verotel\FlexPay;

/**
 * FlexPay Client library
 *
 * This library allows merchants to use Verotel payment gateway
 * and get paid by their users via Credit card and other payment methods.
 *
 */

require_once __DIR__."/Brand.php";
require_once __DIR__."/Exception.php";

class Client {
    const PROTOCOL_VERSION  = '3.4';

    private $brand;
    private $secret;
    private $shopId;

    /**
     * Construct the FlexPay Client for given brand (Verotel, CardBilling, FreenomPay).
     *
     * @param integer $shop_id
     * @param string $secret
     * @param string $brand_name
     * @throws Exception if no secret or shopID was given
     */

    public function __construct($shopId, $secret, $brand = null) {
        if ($brand) {
            $this->brand = $brand;
        } else {
            $this->brand = Brand::create_from_name('Verotel');
        }
        if (empty($secret)) {
            throw new Exception("no secret given");
        }
        $this->secret = $secret;
        if (empty($shopId)) {
            throw new Exception("no shopID given");
        }
        $this->shopId = $shopId;
    }

    /**
     * @param array $params
     * @return string Purchase URL
     */
    public function get_purchase_URL($params) {
        return $this->_generate_URL($this->brand->flexpay_URL(), 'purchase', $params);
    }

    /**
     * @param array $params
     * @return string subscription URL
     */
    public function get_subscription_URL($params) {
        return $this->_generate_URL($this->brand->flexpay_URL(), 'subscription', $params);
    }

    /**
     * @param array $params
     * @return string status URL
     */
    public function get_status_URL($params) {
        return $this->_generate_URL($this->brand->status_URL(), NULL, $params);
    }

    /**
     * @param array $params
     * @return string upgrade subscription URL
     */
    public function get_upgrade_subscription_URL($params) {
        return $this->_generate_URL($this->brand->flexpay_URL(), 'upgradesubscription', $params);
    }

    /**
     * @param array $params
     * @return string cancel subscription URL
     */
    public function get_cancel_subscription_URL($params) {
        return $this->_generate_URL($this->brand->cancel_URL(), NULL, $params);
    }

    /**
     * Validates signature
     * @param array $params just params allowed with signature
     * @return bool
     */
    public function validate_signature($params) {
        $inputSignature = strtolower($params['signature']);
        unset($params['signature']);
        $generatedSignature = $this->_signature($params);
        return ($inputSignature === $generatedSignature);
    }

    /**
     * Generates SHA1 signature
     * @param array $params
     * @return string SHA1 encoded signature
     */
    public function get_signature($params) {
        $filtered = $this->_filter_params($params);
        return $this->_signature($filtered);
    }

    private function _signature($params) {
        $outArray = array($this->secret);
        if (!isset($params['shopID'])){
            $params['shopID'] = $this->shopId;
        }
        ksort($params, SORT_REGULAR);
        foreach ($params as $key => $value) {
            array_push($outArray, "$key=$value");
        }
        return strtolower(sha1(join(":", $outArray)));
    }

    private function _generate_URL($baseUrl, $type, $params) {
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

        if (!isset($params['shopID'])){
            $params['shopID'] = $this->shopId;
        }

        ksort($params, SORT_REGULAR);
        $outArray = array();
        foreach ($params as $key => $value) {
            if(($value !== "") && ($value !== null)) {
                $outArray[$key] = $value;
            }
        }

        $signature = $this->get_signature($outArray);
        $outArray['signature'] = $signature;

        return $this->_build_URL($baseUrl, $outArray);
    }

    private function _build_URL($baseUrl, $params) {
        return $baseUrl . "?" . http_build_query($params);
    }

    private function _filter_params($params) {
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
            | declineURL
            | precedingSaleID
            | upgradeOption
            )$/x';

        foreach ($keys as $key) {
            if (preg_match($regexp, $key)) {
                $filtered[$key] = $params[$key];
            }
        }

        return $filtered;
    }
}
