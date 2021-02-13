<?php

namespace Larapress\CRUD\Translation\Lang;

use Larapress\CRUD\Translation\ILanguage;
use NumberFormatter;

class Persian implements ILanguage
{
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
    public function getName()
    {
        return "fa";
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Farsi';
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
        return true;
    }
}
