<?php

namespace Larapress\CRUD\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class ValidationException extends AppException
{
    /** @var Validator */
    protected $validations;

    protected $appendToResponse;

    public function __construct($validations, $appendToResponse = null)
    {
        parent::__construct(AppException::ERR_VALIDATION);
        $this->validations = $validations;
        $this->appendToResponse = $appendToResponse;
    }

    /**
     * @return Validator
     */
    public function getValidations()
    {
        return $this->validations;
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
                'code' => $this->getErrorCode(),
                'validations' => $this->getValidations()->getMessageBag()->toArray(),
            ];

            if (config('app.debug')) {
                $error = array_merge($error, [
                    'exception' => get_class($this),
                    'file' => $this->getFile(),
                    'line' => $this->getLine(),
                    'trace' => collect($this->getTrace())->map(function ($trace) {
                        return Arr::except($trace, ['args']);
                    })->all(),
                ]);
            }

            if (!is_null($this->appendToResponse)) {
                $error = array_merge($error, $this->appendToResponse);
            }

            return response()->json($error, 400);
        }

        return response($this->getMessage(), 400);
    }
}
