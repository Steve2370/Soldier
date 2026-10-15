<?php

namespace App\Services\Admin;

use App\Models\FamilyGroup;
use App\Models\User;
use App\Services\Famille\FamilyAccessRevocationService;
use App\Services\Logs\ActivityLogService;
use Illuminate\Support\Facades\DB;

final readonly class FamilyGrantService
{
    public function __construct(private FamilyAccessRevocationService $revocation) {}

    public function setGranted(User $actor, User $target, bool $granted): void
    {
        abort_unless($actor->is_admin, 403);

        DB::transaction(function () use ($actor, $target, $granted): void {
            $user = User::query()->lockForUpdate()->findOrFail($target->id);
            if (($user->family_granted_at !== null) === $granted) {
                return;
            }

            $user->forceFill(['family_granted_at' => $granted ? now() : null])->save();
            if (! $user->hasFamilyPlan()) {
                foreach (FamilyGroup::where('owner_id', $user->id)->get() as $group) {
                    $this->revocation->removeAll($group);
                }
            }

            ActivityLogService::log($granted ? 'famille_offerte' : 'famille_offre_retiree',
                ($granted ? 'Accès Famille offert à ' : 'Accès Famille offert retiré à ').$user->name,
                $actor->id, ['target_user_id' => $user->id]);
        });
    }
}
