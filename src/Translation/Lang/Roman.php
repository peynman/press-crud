<?php

namespace Larapress\CRUD\Translation\Lang;

use Larapress\CRUD\Translation\ILanguage;
use NumberFormatter;

class Roman implements ILanguage
{

    /** @var NumberFormatter $decimal */
    protected $decimal;
    /** @var NumberFormatter $integer */
    protected $integer;

    public function __construct()
    {
        $this->decimal = new NumberFormatter('en', NumberFormatter::INTEGER_DIGITS);
        $this->integer = new NumberFormatter('en', NumberFormatter::DECIMAL);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "en";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'English';
    }

    /**
     * @param $number
     * @return string
     */
    public function formatDecimal($number)
    {
        return $this->decimal->format($number);
    }

    /**
     * @param $number
     * @return string
     */
    public function formatInteger($number)
    {
        return $this->integer->format($number);
    }

    /**
     * @param $amount
     * @param $currencyTitle
     * @return string
     */
    public function formatCurrency($amount, $currencyTitle)
    {
        return sprintf("%s %s", $this->formatDecimal($amount), $currencyTitle);
    }

    /**
     * @param $datetime
     * @param $format
     * @return string
     */
    public function formatDateTime($datetime, $format)
    {
        return $datetime->format($format);
    }

    /**
     * @return bool
     */
    public function isRTL()
    {
        return false;
    }
}
