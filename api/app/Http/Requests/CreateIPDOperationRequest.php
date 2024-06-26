<?php

namespace App\Http\Requests;

use App\Models\IpdOperation;
use Illuminate\Foundation\Http\FormRequest;

class CreateIPDOperationRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return IpdOperation::$rules;
    }

    /**
     *
     *
     * @return array|string[]
     */
    public function messages()
    {
        return IpdOperation::$messages;
    }
}
