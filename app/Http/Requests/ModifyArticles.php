<?php namespace App\Http\Requests;

use Common\Core\BaseFormRequest;

class ModifyArticles extends BaseFormRequest
{
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'categories.required' => 'Article needs to be attached to at least one category.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title'      => 'required|string|min:3|max:250',
            'slug'       => 'string|min:3|max:250|nullable',
            'body'       => 'required',
            'draft'      => 'boolean',
            'visibility' => 'string|min:2|max:250',
            'position'   => 'integer|min:0',
            'tags'       => 'array',
            'tags.*'     => 'string|min:1'
        ];

        if ($this->method() === 'POST') {
            $rules['categories'] = 'required|array|min:1';
            $rules['categories.*'] = 'required|integer|min:1';
        }

        return $rules;
    }
}
