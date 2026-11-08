<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class KundliChartRequest extends FormRequest
{
    public function rules(): array
    {
        return BirthDetailsRules::for();
    }
}
