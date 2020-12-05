<?php namespace App\Http\Validators;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TriggerStoreValidator
{
    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Validate TriggerController Store method.
     * @throws ValidationException
     */
    public function validate()
    {
        $validator = Validator::make($this->request->all(), [
            'name'    => 'required|unique:triggers|min:1|max:250',
            'conditions' => 'required|array',
            'conditions.*.value' => 'required|max:255',
            'conditions.*.matchType' => 'required|in:any,all',
            'actions' => 'required|array',
            'actions.*.value' => 'required|max:255',
        ], [
            'conditions.required' => 'Trigger must have at least one condition.',
            'conditions.*.value.required' => 'Condition value field can\'t be empty.',
            'actions.*.value.required' => 'Action value field can\'t be empty.',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag()->messages();

            throw new ValidationException($validator, new JsonResponse(['status' => 'error', 'messages' => $messages], 422));
        }
    }
}
