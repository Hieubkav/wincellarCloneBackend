<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProductIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'min:1', 'max:120'],
            'terms' => ['sometimes', 'array'],
            'terms.brand' => ['sometimes', 'array'],
            'terms.brand.*' => ['integer', 'min:1'],
            'terms.origin.country' => ['sometimes', 'array'],
            'terms.origin.country.*' => ['integer', 'min:1'],
            'terms.origin.region' => ['sometimes', 'array'],
            'terms.origin.region.*' => ['integer', 'min:1'],
            'terms.grape' => ['sometimes', 'array'],
            'terms.grape.*' => ['integer', 'min:1'],
            'type' => ['sometimes', 'array'],
            'type.*' => ['integer', 'min:1'],
            'category' => ['sometimes', 'array'],
            'category.*' => ['integer', 'min:1'],
            'price_min' => ['nullable', 'integer', 'min:0'],
            'price_max' => ['nullable', 'integer', 'min:0'],
            'alcohol_min' => ['nullable', 'numeric', 'min:0'],
            'alcohol_max' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:60'],
            'sort' => ['nullable', 'string', 'max:25'],
            'cursor' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'price_min' => $this->normalizeNumber($this->input('price_min')),
            'price_max' => $this->normalizeNumber($this->input('price_max')),
            'alcohol_min' => $this->normalizeFloat($this->input('alcohol_min')),
            'alcohol_max' => $this->normalizeFloat($this->input('alcohol_max')),
            'q' => $this->normalizeSearch($this->input('q')),
        ]);
    }

    protected function passedValidation(): void
    {
        $perPage = (int) $this->input('per_page', 24);
        $this->merge([
            'per_page' => $perPage,
            'sort' => $this->input('sort', '-created_at'),
        ]);

        $cursor = $this->input('cursor');
        if ($cursor !== null) {
            $page = (int) floor(((int) $cursor) / max($perPage, 1)) + 1;
            $page = max($page, 1);

            $this->merge([
                'page' => $page,
            ]);

            $this->attributes->set('using_cursor', true);
        } else {
            $this->merge([
                'page' => $this->input('page', 1),
            ]);

            $this->attributes->set('using_cursor', false);
        }
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $priceMin = $this->input('price_min');
                $priceMax = $this->input('price_max');
                if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
                    $validator->errors()->add('price_min', 'price_min must be less than or equal to price_max');
                }

                $alcoholMin = $this->input('alcohol_min');
                $alcoholMax = $this->input('alcohol_max');
                if ($alcoholMin !== null && $alcoholMax !== null && $alcoholMin > $alcoholMax) {
                    $validator->errors()->add('alcohol_min', 'alcohol_min must be less than or equal to alcohol_max');
                }
            },
        ];
    }

    private function normalizeNumber(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function normalizeFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function normalizeSearch(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
