<?php

namespace Larapress\CRUD\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class AppException extends Exception
{
    const ERR_INVALID_CREDENTIALS = 10001;
    const ERR_INVALID_TELEGRAM_CREDENTIALS = 10002;
    const ERR_OUTDATED_TELEGRAM_CREDENTIALS = 10003;
    const ERR_TELEGRAM_USER_DOES_NOT_EXISTS = 10004;
    const ERR_TELEGRAM_USER_NOT_REGISTERED = 10005;
    const ERR_VALIDATION = 10006;
    const ERR_INVALID_QUERY = 10007;
    const ERR_ACCESS_DENIED = 10010;
    const ERR_OBJECT_NOT_FOUND = 10011;
    const ERR_UNEXPECTED_RESULT = 10012;
    const ERR_WRONG_PASSWORD = 10013;
    const ERR_OBJ_ACCESS_DENIED = 10014;
    const ERR_OBJ_NOT_READY = 10015;
    const ERR_OBJ_FILE_NOT_FOUND = 10016;
    const ERR_OBJ_PARAMETER_INVALID = 10017;
    const ERR_OBJ_PARAMETER_UNKNOWN = 10018;
    const ERR_BANK_REQUEST_NOT_FOUND = 10019;
    const ERR_NOT_ENOUGH_BALANCE = 10020;
    const ERR_UNIMPLEMENTED_CRUD = 10021;
    const ERR_INVALID_BANK_REDIRECT_QUERY = 10022;
    const ERR_BANK_REDIRECT_TR_NOT_FOUND = 10023;
    const ERR_INVALID_TRANS_STATUS = 10024;
    const ERR_NOT_VALID_QUERY = 10025;
    const ERR_NO_RESPONSE_FOR_QUERY = 10026;
    const ERR_INVALID_PARAMS = 10027;
    const ERR_REJECTED_RESULT = 10028;
    const ERR_WRONG_CODE = 10029;
    const ERR_EXPIRED_CODE = 10030;
    const ERR_INVALID_CONFIG_DOMAIN = 10031;
    const ERR_PROXY_ERROR = 10032;
    const ERR_INVALID_CLASS_BINDING = 10033;
    const ERR_INVALID_ACTION_METHOD = 10034;
    const ERR_ACCOUNT_ALREADY_EXISTS = 10035;
    const ERR_ACCESS_BANNED = 10036;
    const ERR_NUMBER_ALREADY_EXISTS = 10037;
    const ERR_INVALID_FILE_TYPE = 10038;

    private $error_code;

    public function __construct($error_code, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            empty(self::getErrorCodeString($error_code)) ? $message : self::getErrorCodeString($error_code),
            $code,
            $previous
        );
        $this->error_code = $error_code;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * Undocumented function
     *
     * @param [type] $error_code
     * @return void
     */
    public static function getErrorCodeString($error_code)
    {
        return trans('larapress::exceptions.app.'.$error_code);
    }


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return Response
     */
    public function render(Request $request)
    {
        if ($request->wantsJson()) {
            $error = config('app.debug') ? [
                'message' => $this->getMessage(),
                'exception' => get_class($this),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'code' => $this->getErrorCode(),
            ] : [
                'code' => $this->getErrorCode(),
                'message' => $this->getMessage(),
            ];
            return response()->json($error, 400);
        }
    }
}
