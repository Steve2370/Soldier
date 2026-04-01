<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareCoffre extends Model
{
    protected $table = 'shares_coffre';

    protected $fillable = [
        'coffre_id',
        'proprietaire_id',
        'destinataire_id',
        'data_key_destinataire_encrypted',
        'permission',
        'expire_le',
        'statut',
        'accepte_le',
    ];

    protected $hidden = [
        'data_key_destinataire_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'expire_le' => 'datetime',
            'accepte_le' => 'datetime',
        ];
    }

    public function coffre(): BelongsTo
    {
        return $this->belongsTo(Coffre::class, 'coffre_id');
    }

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'accepte')->where(fn($q) =>$q->whereNull('expire_le')
        ->orWhere('expire_le', '>', now()));
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en attente');
    }

    public function isValide(): bool
    {
        if ($this->statut !== 'accepte') {
            return false;
        }

        if ($this->expire_le && $this->expire_le->isPast()) {
            return false;
        }
        return true;
    }

    public function peuEcrire(): bool
    {
        return $this->isValide() && $this->permission === 'ecrire';
    }
}
