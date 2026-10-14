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
        'element_ids',
        'family_group_id',
    ];

    protected $hidden = [
        'data_key_destinataire_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'expire_le' => 'datetime',
            'accepte_le' => 'datetime',
            'element_ids' => 'array',
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
        return $query->where('statut', 'accepte')
            ->where(fn ($q) => $q->whereNull('expire_le')->orWhere('expire_le', '>', now()))
            // Also deny stale historical grants left behind by the old removal workflow.
            ->where(fn ($q) => $q->whereNull('family_group_id')->orWhereExists(fn ($members) => $members
                ->selectRaw('1')->from('family_members')
                ->whereColumn('family_members.family_group_id', 'shares_coffre.family_group_id')
                ->whereColumn('family_members.user_id', 'shares_coffre.destinataire_id')))
            ->whereNotExists(fn ($groups) => $groups->selectRaw('1')->from('family_groups')
                ->whereColumn('family_groups.coffre_id', 'shares_coffre.coffre_id')
                ->whereNotExists(fn ($members) => $members->selectRaw('1')->from('family_members')
                    ->whereColumn('family_members.family_group_id', 'family_groups.id')
                    ->whereColumn('family_members.user_id', 'shares_coffre.destinataire_id')));
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_entente');
    }

    public function scopePourElement($query, int $elementId)
    {
        return $query->where(fn ($selection) => $selection
            ->whereNull('element_ids')->orWhereJsonContains('element_ids', $elementId));
    }

    public function isValide(): bool
    {
        if ($this->statut !== 'accepte' || ($this->expire_le && $this->expire_le->isPast())) {
            return false;
        }

        // Unsaved values can be inspected in isolation; authorizations always query actifs().
        return ! $this->exists || static::whereKey($this->getKey())->actifs()->exists();
    }

    public function peuEcrire(): bool
    {
        return $this->isValide() && $this->permission === 'ecriture';
    }
}
