<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class ChangerMotDePasseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ancien_master_password' => ['required', 'string'],
            'nouveau_master_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'ancien_master_password.required' => 'L\'ancien master password est obligatoire.',
            'nouveau_master_password.required' => 'Le nouveau master password est obligatoire.',
            'nouveau_master_password.min' => 'Le nouveau master password doit contenir au moins 8 caractères.',
            'nouveau_master_password.confirmed' => 'Les master passwords ne correspondent pas.',
        ];
    }
}
