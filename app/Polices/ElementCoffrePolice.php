<?php

namespace App\Polices;

use App\Models\ElementCoffre;
use App\Models\User;

class ElementCoffrePolice
{
    public function view(User $user, ElementCoffre $element): bool
    {
        return $element->coffre->user_id === $user->id;
    }

    public function update(User $user, ElementCoffre $element): bool
    {
        return $element->coffre->user_id === $user->id;
    }

    public function delete(User $user, ElementCoffre $element): bool
    {
        return $element->coffre->user_id === $user->id;
    }
}
