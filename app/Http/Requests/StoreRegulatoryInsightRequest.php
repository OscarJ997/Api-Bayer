<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRegulatoryInsightPayload;
use App\Http\Requests\Concerns\RegulatoryInsightRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreRegulatoryInsightRequest extends FormRequest
{
    use NormalizesRegulatoryInsightPayload;
    use RegulatoryInsightRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isBatch()) {
            $rules = ['*' => ['required', 'array']];

            foreach ($this->insightRules() as $field => $fieldRules) {
                $rules['*.'.$field] = $fieldRules;
            }

            return $rules;
        }

        return $this->insightRules();
    }
}
