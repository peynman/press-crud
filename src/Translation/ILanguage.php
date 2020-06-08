<?php

namespace Larapress\CRUD\Translation;

use Carbon\Carbon;

interface ILanguage
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param float $number
     *
     * @return string
     */
    public function formatDecimal($number);

    /**
     * @param int $number
     *
     * @return string
     */
    public function formatInteger($number);

    /**
     * @param float $amount
     * @param string $currencyTitle
     *
     * @return string
     */
    public function formatCurrency($amount, $currencyTitle);

    /**
     * @param Carbon $datetime
     * @param string $format
     * @return string
     */
    public function formatDateTime(Carbon $datetime, $format);

    /**
     * @return bool
     */
    public function isRTL();
}
