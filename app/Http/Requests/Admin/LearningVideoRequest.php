<?php

namespace App\Http\Requests\Admin;

use App\Models\LearningVideo;
use Illuminate\Validation\Rule;

class LearningVideoRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::in(array_keys(LearningVideo::KINDS))],
            // Lessons belong to a course; live streams do not.
            'category_id' => [
                Rule::requiredIf($this->input('kind') === 'lesson'), 'nullable',
                Rule::exists('categories', 'id')->where('type', 'course'),
            ],
            ...TranslatedRules::for('title', max: 150),
            ...TranslatedRules::for('description', required: false, max: 2000),
            'video_url' => ['required', 'url:https', 'max:255'],
            'thumbnail' => $this->imageRules(false),
            ...$this->common(),
        ];
    }
}
