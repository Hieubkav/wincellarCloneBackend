<?php

namespace App\Http\Requests;

class ProductSearchRequest extends ProductIndexRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['q'] = ['required', 'string', 'min:2', 'max:120'];

        return $rules;
    }
}
