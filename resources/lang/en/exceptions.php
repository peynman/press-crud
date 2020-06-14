<?php

use Larapress\CRUD\Exceptions\AppException;

return [
    'app' => [
        AppException::ERR_INVALID_CREDENTIALS => 'نام کاربری یا رمز عبور درست نیست',
        AppException::ERR_ACCESS_DENIED => 'اجازه دسترسی وجود ندارد',
        AppException::ERR_INVALID_QUERY => 'درخواست نا معتبر',
        AppException::ERR_ACCOUNT_ALREADY_EXISTS => 'یک کاربر قبلا با این شماره ثبت نام کرده، آیا مایل به بازیابی رمز هستید؟',
        AppException::ERR_INVALID_PARAMS => 'مقدار ورودی معتبر نیست'
    ],
];
