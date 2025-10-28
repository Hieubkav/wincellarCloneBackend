<?php

namespace App\Http\Requests;

class ProductSuggestRequest extends ProductIndexRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['q'] = ['required', 'string', 'min:1', 'max:120'];
        $rules['limit'] = ['nullable', 'integer', 'min:1', 'max:20'];

        return $rules;
    }

    protected function passedValidation(): void
    {
        parent::passedValidation();

        $this->request->set('limit', $this->input('limit', 8));
    }
}
