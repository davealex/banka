<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->isAuthorized();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:35'],
            'last_name' => ['required', 'string', 'max:35'],
            'email' => ['required', 'string', 'email', 'max:50'],
            'initial_deposit' => ['required', 'integer', 'min:1000'],
            'account_type' => ['required', 'string', 'exists:types,name']
        ];
    }
}
