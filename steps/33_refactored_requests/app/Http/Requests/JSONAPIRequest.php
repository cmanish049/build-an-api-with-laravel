<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JSONAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'data' => 'required|array',
            'data.id' => ($this->method() === 'PATCH') ? 'required|string' : 'string',
            'data.type' => ['required',Rule::in(array_keys(config('jsonapi.resources')))],
            'data.attributes' => 'required|array',
        ];

        return $this->mergeConfigRules($rules);
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    public function mergeConfigRules(array $rules): array
    {
        $type = $this->input('data.type');

        if ($type && config("jsonapi.resources.{$type}")) {

            switch ($this->method) {
                case 'PATCH':
                    $rules = array_merge($rules, config("jsonapi.resources.{$type}.validationRules.update"));
                    break;

                case 'POST':
                default:
                    $rules = array_merge($rules, config("jsonapi.resources.{$type}.validationRules.create"));
                    break;
            }

        }

        return $rules;
    }


}
