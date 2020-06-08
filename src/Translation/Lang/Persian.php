<?php

namespace Larapress\CRUD\Translation\Lang;

use Larapress\CRUD\Translation\ILanguage;
use NumberFormatter;

class Persian implements ILanguage {
    /** @var NumberFormatter $integer */
    protected $integer;
    /** @var NumberFormatter $integer */
    protected $decimal;

    public function __construct()
    {
        $this->decimal = new NumberFormatter('fa', NumberFormatter::INTEGER_DIGITS);
        $this->integer = new NumberFormatter('fa', NumberFormatter::DECIMAL);
    }

    /**
     * @return string
     */
    function getName() { return "fa"; }

    /**
     * @return string
     */
    function getTitle() { return 'Farsi'; }

    /**
     * @param $number
     * @return string
     */
    function formatDecimal($number) {
        return $this->decimal->format($number);
    }

    /**
     * @param $number
     * @return string
     */
    function formatInteger($number) {
        return $this->integer->format($number);
    }

    /**
     * @param $amount
     * @param $currencyTitle
     * @return string
     */
    function formatCurrency($amount, $currencyTitle) {
        return sprintf("%s %s", $this->formatDecimal($amount), $currencyTitle);
    }

    /**
     * @param $datetime
     * @param $format
     * @return string
     */
    function formatDateTime($datetime, $format) {
        return $datetime->format($format);
    }

    /**
     * @return bool
     */
    public function isRTL()
    {
        return true;
    }
}
