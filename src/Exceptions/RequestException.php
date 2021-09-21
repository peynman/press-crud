<?php

namespace Larapress\CRUD\Exceptions;

use Exception;
use Illuminate\Http\Request;

class RequestException extends Exception
{
    // array
    protected $appendToResponse;

    public function __construct($message, $code = 400, $appendToResponse = null)
    {
        parent::__construct($message, $code);
        $this->appendToResponse = $appendToResponse;
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function render(Request $request)
    {
        if ($request->wantsJson()) {
            $error = [
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
            ];

            if (!is_null($this->appendToResponse)) {
                $error = array_merge($error, $this->appendToResponse);
            }

            return response()->json($error, $this->getCode());
        }

        return response($this->getMessage(), $this->getCode());
    }
}
