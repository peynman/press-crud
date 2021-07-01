<?php

namespace Larapress\CRUD\Services\CRUD\Verbs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Larapress\CRUD\Exceptions\ValidationException;
use Larapress\CRUD\Services\CRUD\ICRUDService;
use Larapress\CRUD\Services\CRUD\ICRUDVerb;

class Reports implements ICRUDVerb {
    /**
     * Undocumented function
     *
     * @return string
     */
    public function getVerbName(): string
    {
        return 'reports';
    }

    /**
     * Undocumented function
     *
     * @param ICRUDService $service
     * @param Request $request
     * @param ...$args
     *
     * @return mixed
     */
    public function handle(ICRUDService $service, Request $request, ...$args)
    {
        $crudProvider = $service->getCompositeProvider();

        $user = Auth::user();
        /** @var ICRUDReportSource[] */
        $reports = $crudProvider->getReportSources();

        $names = [];
        foreach ($reports as $source) {
            $sNames = $source->getReportNames($user);
            foreach ($sNames as $name) {
                $names[$name] = $source;
            }
        }

        $validate = Validator::make($request->all('name'), [
            'name' => 'required|in:' . implode(',', array_keys($names))
        ]);
        if ($validate->fails()) {
            throw new ValidationException($validate);
        }

        /** @var string */
        $report = $request->get('name');

        return $names[$report]->getReport($user, $report, $request->all());
    }
}
