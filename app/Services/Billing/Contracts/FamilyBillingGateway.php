<?php

namespace App\Services\Billing\Contracts;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

interface FamilyBillingGateway
{
    public function checkout(User $user): RedirectResponse;

    public function portal(User $user): RedirectResponse;
}
