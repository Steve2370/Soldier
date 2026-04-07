<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationPartage extends Model
{
    protected $table = 'invitations_partage';

    protected $fillable = [
        'coffre_id',
        'expediteur_id',
        'email_destinataire',
        'token_hash',
        'data_key_encrypted',
        'permission',
        'statut',
        'expire_le',
        'traitee_le',
        'element_ids',
    ];

    protected $hidden = [
        'token_hash',
        'data_key_encrypted',
    ];

    protected $casts = [
        'expire_le' => 'datetime',
        'traitee_le' => 'datetime',
    ];

    public function coffre(): BelongsTo
    {
        return $this->belongsTo(Coffre::class);
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }
}
