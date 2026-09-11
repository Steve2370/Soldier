<?php

namespace App\Policies;

use App\Models\ElementCoffre;
use App\Models\User;

final class ElementCoffrePolicy
{
    public function view(User $user, ElementCoffre $element): bool
    {
        return $this->isOwner($user, $element) || $element->coffre->partages()
            ->where('destinataire_id', $user->id)
            ->actifs()
            ->exists();
    }

    public function update(User $user, ElementCoffre $element): bool
    {
        return $this->isOwner($user, $element) || $element->coffre->partages()
            ->where('destinataire_id', $user->id)
            ->actifs()
            ->where('permission', 'ecriture')
            ->exists();
    }

    public function delete(User $user, ElementCoffre $element): bool
    {
        return $this->update($user, $element);
    }

    private function isOwner(User $user, ElementCoffre $element): bool
    {
        return $element->coffre->user_id === $user->id;
    }
}
