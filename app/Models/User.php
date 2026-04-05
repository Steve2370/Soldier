<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Support\HasOnceHash;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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
