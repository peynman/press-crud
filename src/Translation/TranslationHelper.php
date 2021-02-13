<?php

namespace Larapress\CRUD\Translation;

class TranslationHelper
{
    /**
     * @param  string $locale
     *
     * @return ILanguage|null
     */
    public static function getLocaleLanguage($locale)
    {
        $avLangs = config('larapress.crud.languages');
        /** @var ILanguage $lang */
        $lang = null;
        foreach ($avLangs as $name => $class) {
            if ($name === $locale) {
                $lang = new $class();
            }
        }

        return $lang;
    }
}
