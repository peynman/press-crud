<?php

use Larapress\CRUD\Exceptions\AppException;

return [
    'app' => [
        AppException::ERR_INVALID_CREDENTIALS => 'نام کاربری یا رمز عبور درست نیست',
        AppException::ERR_ACCESS_DENIED => 'اجازه دسترسی وجود ندارد',
        AppException::ERR_INVALID_QUERY => 'درخواست نا معتبر',
        AppException::ERR_ACCOUNT_ALREADY_EXISTS => 'یک کاربر قبلا با این شماره ثبت نام کرده، آیا مایل به بازیابی رمز هستید؟',
        AppException::ERR_INVALID_PARAMS => 'مقدار ورودی معتبر نیست',
        AppException::ERR_ACCESS_BANNED => 'دسترسی شما مسدود شده است، لطفا با پشتیبانی با شماره 09361222120 بگیرید',
        AppException::ERR_NUMBER_ALREADY_EXISTS => 'این شماره برای کاربر دیگری در همین دامنه ثبت شده',
        AppException::ERR_OBJECT_NOT_FOUND => 'رکورد مورد نظر پیدا نشد',
        AppException::ERR_VALIDATION => 'مقادیر ورودی معتبر نیستند',
        AppException::ERR_OBJ_ACCESS_DENIED => 'اجازه دسترسی به این رکورد وجود ندارد',
        AppException::ERR_INVALID_FILE_TYPE => 'نوع فایل ارسالی قابل پذیرش نیست',
        AppException::ERR_UNEXPECTED_RESULT => 'نتیجه مناسب بدست نیامد، لطفا با پشتیبانی تماس بگیرید',
        AppException::ERR_ALREADY_EXECUTED => 'قبلا اجرا شده است.',
        AppException::ERR_OBJ_NOT_READY => 'رکورد مورد نظر اماده نیست',
        AppException::ERR_NOT_ENOUGHT_ITEMS_IN_CART => 'تعداد محصولات سبد برای استفاده از کد تخفیف کافی نیست',
        AppException::ERR_NOT_ENOUGHT_AMOUNT_IN_CART => 'مبلغ سبد برای استفاده از کد تخفیف کافی نیست',
        AppException::ERR_NOT_GIFT_EXPIRED => 'کد تخفیف منقضی شده است',
        AppException::ERR_REJECTED_RESULT => 'مشکلی از سمت سامانه پذیرنده پیش آمد. لطفا بعدا تلاش کنید',
        AppException::ERR_USER_HAS_NO_DOMAIN => 'دامنه‌ای برای کاربر ثبت نشده است',
    ],
];
