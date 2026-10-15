<?php

namespace App\Services\Admin;

use App\Models\InvitationPartage;
use App\Models\User;
use App\Services\Logs\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteUserService
{
    public function __construct(private readonly RemoveUserAvatar $avatars) {}

    public function delete(User $actor, User $target): void
    {
        abort_unless($actor->is_admin, 403);

        DB::transaction(function () use ($actor, $target): void {
            $user = User::query()->lockForUpdate()->findOrFail($target->id);
            if ($actor->is($user)) {
                throw ValidationException::withMessages(['confirmation_email' => 'Vous ne pouvez pas supprimer votre propre compte.']);
            }

            // Never remove the local billing record while Stripe may still bill it.
            if ($user->subscriptions()->where(function ($query): void {
                $query->whereNotIn('stripe_status', ['canceled', 'incomplete_expired'])
                    ->orWhere('ends_at', '>', now());
            })->exists()) {
                throw ValidationException::withMessages(['confirmation_email' => 'Un abonnement Stripe est encore en cours. Résiliez-le dans Stripe et attendez sa fin et sa synchronisation avant de supprimer ce compte.']);
            }

            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            InvitationPartage::where('email_destinataire', $user->email)->delete();
            $user->tokens()->delete(); // Polymorphic relation, no foreign-key cascade.
            // Foreign keys cascade vaults (including trashed ones), encrypted keys,
            // shares, family groups/members, MFA and passkeys in this transaction.
            $user->forceDelete();
            DB::afterCommit(fn () => $this->avatars->remove($user->avatar));
            ActivityLogService::log('user_supprime', "Compte supprimé définitivement : {$user->name}", $actor->id, ['target_user_id' => $user->id]);
        });
    }
}
