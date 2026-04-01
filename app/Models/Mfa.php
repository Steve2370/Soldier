<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mfa extends Model
{
    protected $table = 'mfa';

    protected $fillable = [
        'user_id',
        'type',
        'actif',
        'code_hash',
        'code_expire_le',
        'tentatives',
        'totp_secret_chiffre',
        'codes_recuperation',
    ];

    protected $hidden = [
        'code_hash',
        'totp_secret_chiffre',
        'codes_recuperation',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'code_expire_le' => 'datetime',
        'tentatives' => 'integer',
        'codes_recuperation' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
