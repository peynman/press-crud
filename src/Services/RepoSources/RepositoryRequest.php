<?php

namespace Larapress\CRUD\Services\RepoSources;

use Illuminate\Foundation\Http\FormRequest;

class RepositoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sources.*.resource' => 'required|string|in:object,repository',
            'sources.*.class' => 'required|string',
            'sources.*.path' => 'required|string',
            'sources.*.method' => 'nullable|string',
        ];
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getSources() {
        return $this->get('sources', []);
    }
}
