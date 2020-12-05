<?php namespace App\Http\Requests;

use Common\Core\BaseFormRequest;

class ModifyReplies extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->route('type') === 'drafts') {
            return $messages = [
                'body'      => 'nullable|string|min:1',
                'status'    => 'array',
                'uploads'   => 'required_without:body|array|max:10',
                'uploads.*' => 'required_without:body|integer|exists:file_entries,id'
            ];
        }

        return $messages = [
            'body'      => 'required|string|min:1',
            'uploads'   => 'array|max:5',
            'uploads.*' => 'integer|exists:file_entries,id'
        ];
    }
}
