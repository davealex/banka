<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountTransferRequest extends FormRequest
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
            'credit' => ['required', 'integer', 'exists:accounts,number', 'different:debit'],
            'debit' => ['required', 'integer', 'exists:accounts,number', 'different:credit'],
            'amount' => ['required', 'integer', 'min:1000']
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'credit.different' => 'Same accounts! You cannot transfer from an account to itself.',
            'debit.different' => 'Same accounts! You cannot transfer from an account to itself.'
        ];
    }
}
