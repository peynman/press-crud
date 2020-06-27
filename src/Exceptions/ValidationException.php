<?php

namespace Larapress\CRUD\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class ValidationException extends AppException
{
    /** @var Validator */
    protected $validations;

    public function __construct($validations)
    {
        parent::__construct(AppException::ERR_VALIDATION);
        $this->validations = $validations;
    }

    /**
     * @return Validator
     */
    public function getValidations()
    {
        return $this->validations;
    }

    public function render(Request $request)
    {
        if ($request->wantsJson()) {
            $error = config('app.debug') ? [
                'message' => $this->getMessage(),
                'exception' => get_class($this),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => collect($this->getTrace())->map(function ($trace) {
                    return Arr::except($trace, ['args']);
                })->all(),
                'code' => $this->getErrorCode(),
                'validations' => $this->getValidations()->getMessageBag()->toArray(),
            ] : [
                'code' => $this->getErrorCode(),
                'message' => $this->getMessage(),
                'validations' => $this->getValidations()->getMessageBag()->toArray(),
            ];
            return response()->json($error, 400);
        }
    }
}
