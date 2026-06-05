<?php

namespace App\Http\Requests;

use App\Enums\PortalCategory;
use App\Enums\PortalStatus;
use App\Support\NormalizeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConnectedPortalRequest extends FormRequest
{
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
            'name' => ['sometimes', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:2048', 'url'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', Rule::enum(PortalCategory::class)],
            'status' => ['sometimes', Rule::enum(PortalStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('url')) {
            $this->merge([
                'url' => NormalizeUrl::apply($this->input('url')),
            ]);
        }
    }
}
