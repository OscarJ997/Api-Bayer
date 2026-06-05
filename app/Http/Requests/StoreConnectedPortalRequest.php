<?php

namespace App\Http\Requests;

use App\Enums\PortalCategory;
use App\Enums\PortalStatus;
use App\Support\NormalizeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConnectedPortalRequest extends FormRequest
{
    private const COUNTRY_CODES = [
        'AR', 'BO', 'BR', 'CL', 'CO', 'CR', 'CU', 'DO', 'EC', 'SV',
        'GT', 'HN', 'MX', 'NI', 'PA', 'PY', 'PE', 'UY',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', 'size:2', Rule::in(self::COUNTRY_CODES)],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048', 'url'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', Rule::enum(PortalCategory::class)],
            'status' => ['sometimes', Rule::enum(PortalStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('country_code')) {
            $merged['country_code'] = strtoupper((string) $this->input('country_code'));
        }

        if ($this->has('url')) {
            $merged['url'] = NormalizeUrl::apply($this->input('url'));
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }
}
