<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesRegulatoryInsightPayload;
use App\Http\Requests\Concerns\RegulatoryInsightRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRegulatoryInsightRequest extends FormRequest
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
        return $this->insightRules();
    }
}
