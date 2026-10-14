<?php

namespace App\Services\Famille;

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\InvitationPartage;
use App\Models\ShareCoffre;
use Illuminate\Support\Facades\DB;

final class FamilyAccessRevocationService
{
    public function remove(FamilyMember $member): void
    {
        DB::transaction(function () use ($member): void {
            $group = FamilyGroup::query()->lockForUpdate()->findOrFail($member->family_group_id);
            $membership = FamilyMember::query()->lockForUpdate()->find($member->id);
            if (! $membership || $membership->role === 'owner') {
                return;
            }

            // The canonical family vault also covers historical, untagged shares.
            $scope = fn ($q) => $q->where('family_group_id', $group->id)
                ->when($group->coffre_id, fn ($q) => $q->orWhere('coffre_id', $group->coffre_id));
            ShareCoffre::where('destinataire_id', $membership->user_id)->where($scope)->delete();
            InvitationPartage::where('email_destinataire', $membership->user?->email)
                ->where($scope)->where('statut', 'en_attente')
                ->update(['statut' => 'expiree', 'traitee_le' => now()]);
            $membership->delete();
        });
    }

    public function removeAll(FamilyGroup $group): int
    {
        return DB::transaction(function () use ($group): int {
            FamilyGroup::query()->lockForUpdate()->findOrFail($group->id);
            $members = $group->members()->where('role', 'member')->get();
            foreach ($members as $member) {
                $this->remove($member);
            }

            return $members->count();
        });
    }
}
