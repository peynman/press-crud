<?php

namespace Larapress\CRUD\Services\Persian;

class PersianText
{
    public function __construct()
    {
        // constructor body
    }

    public static function numbers_fa($numbers)
    {
        $numbers = self::numbers_ar_fa($numbers);
        $numbers = self::numbers_en_fa($numbers);
        return $numbers;
    }

    public static function numbers_en($numbers)
    {
        $numbers = self::numbers_fa_en($numbers);
        $numbers = self::numbers_ar_en($numbers);
        return $numbers;
    }

    public static function numbers_ar_fa($numbers)
    {
        $find = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $replace = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return (string) str_replace($find, $replace, $numbers);
    }

    public static function numbers_en_fa($numbers)
    {
        $find = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $replace = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return (string) str_replace($find, $replace, $numbers);
    }

    public static function numbers_ar_en($numbers)
    {
        $find = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $replace = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return (string) str_replace($find, $replace, $numbers);
    }

    public static function numbers_fa_en($numbers)
    {
        $find = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $replace = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return (string) str_replace($find, $replace, $numbers);
    }

    public static function text($text)
    {
        $from = [
            // collection 1
            ['؆', '؇', '؈', '؉', '؊', '؍', '؎', 'ؐ', 'ؑ', 'ؒ', 'ؓ', 'ؔ', 'ؕ', 'ؖ', 'ؘ', 'ؙ', 'ؚ', '؞', 'ٖ', 'ٗ', '٘', 'ٙ', 'ٚ', 'ٛ', 'ٜ', 'ٝ', 'ٞ', 'ٟ', '٪', '٬', '٭', 'ہ', 'ۂ', 'ۃ', '۔', 'ۖ', 'ۗ', 'ۘ', 'ۙ', 'ۚ', 'ۛ', 'ۜ', '۞', '۟', '۠', 'ۡ', 'ۢ', 'ۣ', 'ۤ', 'ۥ', 'ۦ', 'ۧ', 'ۨ', '۩', '۪', '۫', '۬', 'ۭ', 'ۮ', 'ۯ', 'ﮧ', '﮲', '﮳', '﮴', '﮵', '﮶', '﮷', '﮸', '﮹', '﮺', '﮻', '﮼', '﮽', '﮾', '﮿', '﯀', '﯁', 'ﱞ', 'ﱟ', 'ﱠ', 'ﱡ', 'ﱢ', 'ﱣ', 'ﹰ', 'ﹱ', 'ﹲ', 'ﹳ', 'ﹴ', 'ﹶ', 'ﹷ', 'ﹸ', 'ﹹ', 'ﹺ', 'ﹻ', 'ﹼ', 'ﹽ', 'ﹾ', 'ﹿ'],
            // collection 2
            ['أ', 'إ', 'ٱ', 'ٲ', 'ٳ', 'ٵ', 'ݳ', 'ݴ', 'ﭐ', 'ﭑ', 'ﺃ', 'ﺄ', 'ﺇ', 'ﺈ', 'ﺍ', 'ﺎ', '𞺀', 'ﴼ', 'ﴽ', '𞸀'],
            // collection 3
            ['ٮ', 'ݕ', 'ݖ', 'ﭒ', 'ﭓ', 'ﭔ', 'ﭕ', 'ﺏ', 'ﺐ', 'ﺑ', 'ﺒ', '𞸁', '𞸜', '𞸡', '𞹡', '𞹼', '𞺁', '𞺡'],
            // collection 4
            ['ڀ', 'ݐ', 'ݔ', 'ﭖ', 'ﭗ', 'ﭘ', 'ﭙ', 'ﭚ', 'ﭛ', 'ﭜ', 'ﭝ'],
            // collection 5
            ['ٹ', 'ٺ', 'ٻ', 'ټ', 'ݓ', 'ﭞ', 'ﭟ', 'ﭠ', 'ﭡ', 'ﭢ', 'ﭣ', 'ﭤ', 'ﭥ', 'ﭦ', 'ﭧ', 'ﭨ', 'ﭩ', 'ﺕ', 'ﺖ', 'ﺗ', 'ﺘ', '𞸕', '𞸵', '𞹵', '𞺕', '𞺵'],
            // collection 6
            ['ٽ', 'ٿ', 'ݑ', 'ﺙ', 'ﺚ', 'ﺛ', 'ﺜ', '𞸖', '𞸶', '𞹶', '𞺖', '𞺶'],
            // collection 7
            ['ڃ', 'ڄ', 'ﭲ', 'ﭳ', 'ﭴ', 'ﭵ', 'ﭶ', 'ﭷ', 'ﭸ', 'ﭹ', 'ﺝ', 'ﺞ', 'ﺟ', 'ﺠ', '𞸂', '𞸢', '𞹂', '𞹢', '𞺂', '𞺢'],
            // collection 8
            ['ڇ', 'ڿ', 'ݘ', 'ﭺ', 'ﭻ', 'ﭼ', 'ﭽ', 'ﭾ', 'ﭿ', 'ﮀ', 'ﮁ', '𞸃', '𞺃'],
            // collection 9
            ['ځ', 'ݮ', 'ݯ', 'ݲ', 'ݼ', 'ﺡ', 'ﺢ', 'ﺣ', 'ﺤ', '𞸇', '𞸧', '𞹇', '𞹧', '𞺇', '𞺧'],
            // collection 10
            ['ڂ', 'څ', 'ݗ', 'ﺥ', 'ﺦ', 'ﺧ', 'ﺨ', '𞸗', '𞸷', '𞹗', '𞹷', '𞺗', '𞺷'],
            // collection 11
            ['ڈ', 'ډ', 'ڊ', 'ڌ', 'ڍ', 'ڎ', 'ڏ', 'ڐ', 'ݙ', 'ݚ', 'ﺩ', 'ﺪ', '𞺣', 'ﮂ', 'ﮃ', 'ﮈ', 'ﮉ'],
            // collection 12
            ['ﱛ', 'ﱝ', 'ﺫ', 'ﺬ', '𞸘', '𞺘', '𞺸', 'ﮄ', 'ﮅ', 'ﮆ', 'ﮇ'],
            // collection 13
            ['٫', 'ڑ', 'ڒ', 'ړ', 'ڔ', 'ڕ', 'ږ', 'ݛ', 'ݬ', 'ﮌ', 'ﮍ', 'ﱜ', 'ﺭ', 'ﺮ', '𞸓', '𞺓', '𞺳'],
            // collection 14
            ['ڗ', 'ڙ', 'ݫ', 'ݱ', 'ﺯ', 'ﺰ', '𞸆', '𞺆', '𞺦'],
            // collection 15
            ['ﮊ', 'ﮋ', 'ژ'],
            // collection 16
            ['ښ', 'ݽ', 'ݾ', 'ﺱ', 'ﺲ', 'ﺳ', 'ﺴ', '𞸎', '𞸮', '𞹎', '𞹮', '𞺎', '𞺮'],
            // collection 17
            ['ڛ', 'ۺ', 'ݜ', 'ݭ', 'ݰ', 'ﺵ', 'ﺶ', 'ﺷ', 'ﺸ', '𞸔', '𞸴', '𞹔', '𞹴', '𞺔', '𞺴'],
            // collection 18
            ['ڝ', 'ﺹ', 'ﺺ', 'ﺻ', 'ﺼ', '𞸑', '𞹑', '𞸱', '𞹱', '𞺑', '𞺱'],
            // collection 19
            ['ڞ', 'ۻ', 'ﺽ', 'ﺾ', 'ﺿ', 'ﻀ', '𞸙', '𞸹', '𞹙', '𞹹', '𞺙', '𞺹'],
            // collection 20
            ['ﻁ', 'ﻂ', 'ﻃ', 'ﻄ', '𞸈', '𞹨', '𞺈', '𞺨'],
            // collection 21
            ['ڟ', 'ﻅ', 'ﻆ', 'ﻇ', 'ﻈ', '𞸚', '𞹺', '𞺚', '𞺺'],
            // collection 22
            ['؏', 'ڠ', 'ﻉ', 'ﻊ', 'ﻋ', 'ﻌ', '𞸏', '𞸯', '𞹏', '𞹯', '𞺏', '𞺯'],
            // collection 23
            ['ۼ', 'ݝ', 'ݞ', 'ݟ', 'ﻍ', 'ﻎ', 'ﻏ', 'ﻐ', '𞸛', '𞸻', '𞹛', '𞹻', '𞺛', '𞺻'],
            // collection 24
            ['؋', 'ڡ', 'ڢ', 'ڣ', 'ڤ', 'ڥ', 'ڦ', 'ݠ', 'ݡ', 'ﭪ', 'ﭫ', 'ﭬ', 'ﭭ', 'ﭮ', 'ﭯ', 'ﭰ', 'ﭱ', 'ﻑ', 'ﻒ', 'ﻓ', 'ﻔ', '𞸐', '𞸞', '𞸰', '𞹰', '𞹾', '𞺐', '𞺰'],
            // collection 25
            ['ٯ', 'ڧ', 'ڨ', 'ﻕ', 'ﻖ', 'ﻗ', 'ﻘ', '𞸒', '𞸟', '𞸲', '𞹒', '𞹟', '𞹲', '𞺒', '𞺲', '؈'],
            // collection 26
            ['ػ', 'ؼ', 'ك', 'ڪ', 'ګ', 'ڬ', 'ڭ', 'ڮ', 'ݢ', 'ݣ', 'ݤ', 'ݿ', 'ﮎ', 'ﮏ', 'ﮐ', 'ﮑ', 'ﯓ', 'ﯔ', 'ﯕ', 'ﯖ', 'ﻙ', 'ﻚ', 'ﻛ', 'ﻜ', '𞸊', '𞸪', '𞹪'],
            // collection 27
            ['ڰ', 'ڱ', 'ڲ', 'ڳ', 'ڴ', 'ﮒ', 'ﮓ', 'ﮔ', 'ﮕ', 'ﮖ', 'ﮗ', 'ﮘ', 'ﮙ', 'ﮚ', 'ﮛ', 'ﮜ', 'ﮝ'],
            // collection 28
            ['ڵ', 'ڶ', 'ڷ', 'ڸ', 'ݪ', 'ﻝ', 'ﻞ', 'ﻟ', 'ﻠ', '𞸋', '𞸫', '𞹋', '𞺋', '𞺫'],
            // collection 29
            ['۾', 'ݥ', 'ݦ', 'ﻡ', 'ﻢ', 'ﻣ', 'ﻤ', '𞸌', '𞸬', '𞹬', '𞺌', '𞺬'],
            // collection 30
            ['ڹ', 'ں', 'ڻ', 'ڼ', 'ڽ', 'ݧ', 'ݨ', 'ݩ', 'ﮞ', 'ﮟ', 'ﮠ', 'ﮡ', 'ﻥ', 'ﻦ', 'ﻧ', 'ﻨ', '𞸍', '𞸝', '𞸭', '𞹍', '𞹝', '𞹭', '𞺍', '𞺭'],
            // collection 31
            ['ؤ', 'ٶ', 'ٷ', 'ۄ', 'ۅ', 'ۆ', 'ۇ', 'ۈ', 'ۉ', 'ۊ', 'ۋ', 'ۏ', 'ݸ', 'ݹ', 'ﯗ', 'ﯘ', 'ﯙ', 'ﯚ', 'ﯛ', 'ﯜ', 'ﯝ', 'ﯞ', 'ﯟ', 'ﯠ', 'ﯡ', 'ﯢ', 'ﯣ', 'ﺅ', 'ﺆ', 'ﻭ', 'ﻮ', '𞸅', '𞺅', '𞺥'],
            // collection 32
            ['ة', 'ھ', 'ۀ', 'ە', 'ۿ', 'ﮤ', 'ﮥ', 'ﮦ', 'ﮩ', 'ﮨ', 'ﮪ', 'ﮫ', 'ﮬ', 'ﮭ', 'ﺓ', 'ﺔ', 'ﻩ', 'ﻪ', 'ﻫ', 'ﻬ', '𞸤', '𞹤', '𞺄'],
            // collection 33
            ['ؠ', 'ئ', 'ؽ', 'ؾ', 'ؿ', 'ى', 'ي', 'ٸ', 'ۍ', 'ێ', 'ې', 'ۑ', 'ے', 'ۓ', 'ݵ', 'ݶ', 'ݷ', 'ݺ', 'ݻ', 'ﮢ', 'ﮣ', 'ﮮ', 'ﮯ', 'ﮰ', 'ﮱ', 'ﯤ', 'ﯥ', 'ﯦ', 'ﯧ', 'ﯨ', 'ﯩ', 'ﯼ', 'ﯽ', 'ﯾ', 'ﯿ', 'ﺉ', 'ﺊ', 'ﺋ', 'ﺌ', 'ﻯ', 'ﻰ', 'ﻱ', 'ﻲ', 'ﻳ', 'ﻴ', '𞸉', '𞸩', '𞹉', '𞹩', '𞺉', '𞺩'],
            // collection 34
            ['ٴ', '۽', 'ﺀ'],
            // collection 35
            ['ﻵ', 'ﻶ', 'ﻷ', 'ﻸ', 'ﻹ', 'ﻺ', 'ﻻ', 'ﻼ'],
            // collection 36
            ['ﷲ', '﷼', 'ﷳ', 'ﷴ', 'ﷵ', 'ﷶ', 'ﷷ', 'ﷸ', 'ﷹ', 'ﷺ', 'ﷻ'],
        ];
        $to = [
            // collection 1
            '',
            // collection 2
            'ا',
            // collection 3
            'ب',
            // collection 4
            'پ',
            // collection 5
            'ت',
            // collection 6
            'ث',
            // collection 7
            'ج',
            // collection 8
            'چ',
            // collection 9
            'ح',
            // collection 10
            'خ',
            // collection 11
            'د',
            // collection 12
            'ذ',
            // collection 13
            'ر',
            // collection 14
            'ز',
            // collection 15
            'ژ',
            // collection 16
            'س',
            // collection 17
            'ش',
            // collection 18
            'ص',
            // collection 19
            'ض',
            // collection 20
            'ط',
            // collection 21
            'ظ',
            // collection 22
            'ع',
            // collection 23
            'غ',
            // collection 24
            'ف',
            // collection 25
            'ق',
            // collection 26
            'ک',
            // collection 27
            'گ',
            // collection 28
            'ل',
            // collection 29
            'م',
            // collection 30
            'ن',
            // collection 31
            'و',
            // collection 32
            'ه',
            // collection 33
            'ی',
            // collection 34
            'ء',
            // collection 35
            'لا',
            // collection 36
            ['الله', 'ریال', 'اکبر', 'محمد', 'صلعم', 'رسول', 'علیه', 'وسلم', 'صلی', 'صلی الله علیه وسلم', 'جل جلاله'],
        ];
        for ($i = 0; $i < count($from); $i++) {
            $text = str_replace($from[$i], $to[$i], $text);
        }
        return $text;
    }

    public static function standard($string)
    {
        $string = self::numbers_en($string);
        $string = self::text($string);
        return $string;
    }

    public static function persian($string)
    {
        $string = self::numbers_fa($string);
        $string = self::text($string);
        return $string;
    }
}
