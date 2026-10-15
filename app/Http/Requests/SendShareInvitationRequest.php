<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendShareInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && (! $this->boolean('partage_groupe') || $this->user()->hasFamilyPlan());
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            // Older clients omit this field: absence means individual, never family.
            'partage_groupe' => ['sometimes', 'required', 'boolean'],
            'coffre_id' => ['required', 'integer', 'exists:coffres,id'],
            'email' => [Rule::excludeIf($this->boolean('partage_groupe')), 'required', 'string', 'email', 'max:255'],
            'permission' => ['required', Rule::in(['lecture', 'ecriture'])],
            'element_ids' => ['required', 'array', 'min:1'],
            'element_ids.*' => ['integer', 'exists:elements_coffres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'partage_groupe.*' => 'Choisissez un utilisateur ou votre groupe famille.',
            'coffre_id.required' => 'Sélectionnez un coffre à partager.',
            'email.required' => 'Indiquez l’email du destinataire.',
            'email.email' => 'Indiquez une adresse email valide.',
            'element_ids.required' => 'Sélectionnez au moins un élément à partager.',
            'element_ids.min' => 'Sélectionnez au moins un élément à partager.',
            'permission.*' => 'Choisissez une permission de lecture ou d’écriture.',
        ];
    }
}
