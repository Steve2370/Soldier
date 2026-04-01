<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coffre extends Model
{
    use SoftDeletes;

    protected $table = 'coffres';

    protected $fillable = [
        'user_id',
        'nom',
        'couleur',
        'icone',
        'data_key_encrypted',
    ];

    protected $hidden = [
        'data_key_encrypted',
    ];

    public function elements(): HasMany
    {
        return $this->hasMany(ElementCoffre::class, 'coffre_id');
    }

    protected $casts = [
        'data_key_encrypted' => 'array',
    ];

    protected function proprietaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function partages(): HasMany
    {
        return $this->hasMany(ShareCoffre::class, 'coffre_id');
    }

    public function scopePartagesActifs($query)
    {
        return $query->whereHas('partages', fn ($q) =>
            $q->where('statut', 'accepte')->where(fn($q2) =>
                $q2->whereNull('expire_le')->orWhere('expire_le', '>', now())));
    }

    public function isAccessiblePar(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->partages()->where('destinataire_id', $user->id)
            ->where('statut', 'accepte')->where(fn($q) => $q->whereNull('expire_le')
                ->orWhere('expire_le', '>', now()))->exists();
    }

    public function nombreElements(): int
    {
        return $this->elements()->count();
    }
}
