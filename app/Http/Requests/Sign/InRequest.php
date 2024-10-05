<?php

namespace App\Http\Requests\Sign;

use App\Facades\MessageFixer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class InRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6|max:255'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());

        throw new ValidationException($validator, $response);
    }
}
