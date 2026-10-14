<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_keys(config('vault.payloads')))],
            'label' => ['required', 'string', 'max:200'],
            'url' => ['nullable', 'url:http,https', 'max:500'],
            'identifiant' => ['nullable', 'string', 'max:255'],
            'mot_de_passe' => ['nullable', 'string', 'max:1000'],
            'passphrase_ssh' => ['nullable', 'string', 'max:1000'],
            'totp_secret' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:30'],
            'titulaire' => ['nullable', 'string', 'max:255'],
            'expiration' => ['nullable', 'string', 'max:10'],
            'cvv' => ['nullable', 'string', 'max:10'],
            'code_pin' => ['nullable', 'string', 'max:20'],
            'prenom' => ['nullable', 'string', 'max:255'],
            'nom' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'passeport' => ['nullable', 'string', 'max:100'],
            'serveur' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'int', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'cle_privee' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'contenu' => ['nullable', 'string', 'max:50000'],
            'champs_personnels' => ['sometimes', 'array', 'max:50'],
            'champs_personnels.*' => ['array:nom,valeur'],
            'champs_personnels.*.nom' => ['required', 'string', 'max:100'],
            'champs_personnels.*.valeur' => ['present', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Type invalide.',
            'label.required' => 'Le nom est obligatoire.',
            'label.max' => 'Le nom ne doit pas dépasser 200 caractères.',
            'url.url' => 'L\'URL n\'est pas valide. Exemple : https://github.com',
        ];
    }
}
