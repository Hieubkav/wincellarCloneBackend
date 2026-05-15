<?php

namespace App\Http\Requests\Api\V1\Articles;

use App\Support\Content\ArticleContentCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'sort' => ['nullable', 'string', 'max:25'],
            'category_key' => ['nullable', 'string', Rule::in(ArticleContentCatalog::categoryKeys())],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'per_page' => $this->input('per_page', 12),
            'sort' => $this->input('sort', '-created_at'),
        ]);
    }
}
