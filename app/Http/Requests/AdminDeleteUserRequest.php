<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AdminDeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return ['confirmation_email' => ['required', 'string', Rule::in([$this->route('user')->email])]];
    }

    public function messages(): array
    {
        return ['confirmation_email.required' => 'Recopiez l’adresse email du compte à supprimer.',
            'confirmation_email.in' => 'L’adresse de confirmation ne correspond pas au compte.'];
    }
}
