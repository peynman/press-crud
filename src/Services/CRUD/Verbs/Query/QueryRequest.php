<?php

namespace Larapress\CRUD\Services\CRUD\Verbs\Query;

use Illuminate\Foundation\Http\FormRequest;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\Services\CRUD\ICRUDProvider;
use Illuminate\Support\Str;

class QueryRequest extends FormRequest
{

    /** @var ICRUDProvider */
    protected $provider;

    /** @var string[] */
    protected $validRelationNames;

    /** @var string[] */
    protected $validSortColumnNames;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return is_null($this->provider);
    }

    /**
     * Undocumented function
     *
     * @param ICRUDProvider $provider
     *
     * @return void
     */
    public function useProvider(ICRUDProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Undocumented function
     *
     * @return ICRUDProvider
     */
    public function getProvider(): ICRUDProvider
    {
        return $this->provider;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->validRelationNames = $this->provider->getValidRelations();
        if (Helpers::isAssocArray($this->validRelationNames)) {
            $this->validRelationNames = array_keys($this->validRelationNames);
        }

        $validSorts = $this->provider->getValidSortColumns();
        $this->validSortColumnNames = Helpers::isAssocArray($validSorts) ? array_keys($validSorts) : array_values($validSorts);

        return [
            'with' => 'nullable|array',
            'with.*.name' => 'required_with:with|string|in:' . implode(',', $this->validRelationNames),
            'with.*.columns' => 'required_with:with|string',
            'sort' => 'nullable|array',
            'sort.*.column' => 'required_with:sort|string|in:' . implode(',', $this->validSortColumnNames),
            'sort.*.dir' => 'required_with:sort|string|in:desc,asc',
            'filters' => 'nullable|array',
            'limit' => 'nullable|numeric',
            'page' => 'nullable|numeric',
            'ref_id' => 'nullable|numeric',
        ];
    }

    /**
     * Undocumented function
     *
     * @return string[]
     */
    public function getValidRelationNames()
    {
        if (is_null($this->validRelationNames)) {
            $this->validRelationNames = $this->provider->getValidRelations();
            if (Helpers::isAssocArray($this->validRelationNames)) {
                $this->validRelationNames = array_keys($this->validRelationNames);
            }
        }

        return $this->validRelationNames;
    }

    /**
     * Undocumented function
     *
     * @return string[]
     */
    public function getValidSortColumnNames()
    {
        if (is_null($this->validSortColumnNames)) {
            $validSorts = $this->provider->getValidSortColumns();
            $this->validSortColumnNames = Helpers::isAssocArray($validSorts) ? array_keys($validSorts) : array_values($validSorts);
        }

        return $this->validSortColumnNames;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function hasRelations()
    {
        return $this->has('with');
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getRelationsToLoad()
    {
        return $this->get('with', []);
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function isSearchQuery()
    {
        $search = $this->get('search');
        return is_string($search) && (strlen($search) >= 3 || Str::startsWith($search, '#') );
    }

    /**
     * Undocumented function
     *
     * @return string|null
     */
    public function getSearchTerm()
    {
        return $this->get('search');
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function shouldSort()
    {
        return !is_null($this->get('sort'));
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getOrders()
    {
        return $this->get('sort', []);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->get('filters', []);
    }
}
