<?php
namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, MassPrunable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'totp_secret_chiffre',
        'actif',
        'active_le',
        'type',
        'oauth_provider',
        'oauth_id',
        'has_master_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Purge les comptes supprimés depuis plus de 30 jours.
     * Exécuté via : php artisan model:prune
     */
    public function prunable(): Builder
    {
        return static::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30));
    }

    /**
     * Nettoyage des données liées avant purge définitive.
     */
    protected function pruning(): void
    {
        if ($this->avatar && \Storage::disk('public')->exists($this->avatar)) {
            \Storage::disk('public')->delete($this->avatar);
        }

        $this->coffres()->each(function ($coffre) {
            $coffre->elements()->forceDelete();
            $coffre->delete();
        });

        $this->clesUser()->delete();
        $this->mfa()->delete();
        $this->passKeys()->delete();
        $this->tokens()->delete();
    }

    public function mfa(): HasMany
    {
        return $this->hasMany(Mfa::class);
    }

    public function passKeys(): HasMany
    {
        return $this->hasMany(Passkey::class);
    }

    public function sharesRecus(): HasMany
    {
        return $this->hasMany(ShareCoffre::class, 'destinataire_id');
    }

    public function clesUser(): HasOne
    {
        return $this->hasOne(CleUser::class);
    }

    public function coffres(): HasMany
    {
        return $this->hasMany(Coffre::class);
    }

    public function partagesEnvoyes(): HasMany
    {
        return $this->hasMany(ShareCoffre::class, 'proprietaire_id');
    }

    public function partagesRecus(): HasMany
    {
        return $this->hasMany(ShareCoffre::class, 'destinataire_id');
    }

    public function getClePublique(): ?string
    {
        return $this->clesUser?->cle_publique;
    }

    public function isCleInitialise(): bool
    {
        return $this->clesUser()->exists();
    }
}
