<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passkey extends Model
{
    protected $fillable = [
        'user_id',
        'nom',
        'credential_id',
        'cle_publique',
        'compteur',
        'type_authenticator',
        'algorithme_cose',
        'derniere_utilisation',
    ];

    protected $casts = [
        'derniere_utilisation' => 'datetime',
        'compteur' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
