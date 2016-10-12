<?php
namespace Verotel\FlexPay\Brand;

class Base {
    const BASE_URL    = '';
    const FLEXPAY_URL = '/startorder';
    const STATUS_URL  = '/salestatus';
    const CANCEL_URL  = '/cancel-subscription';

    public function flexpay_URL() {
        return static::BASE_URL . static::FLEXPAY_URL;
    }

    public function status_URL() {
        return static::BASE_URL . static::STATUS_URL;
    }

    public function cancel_URL() {
        return static::BASE_URL . static::CANCEL_URL;
    }
}
