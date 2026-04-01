<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleUser extends Model
{
    protected $table = 'cles_user';

    protected $fillable = [
        'user_id',
        'kdf_salt',
        'kdf_algorithme',
        'kdf_params',
        'encrypted_kek',
        'public_key',
        'encrypted_private_key',
        'verification_master_key',
        'version_schema',
    ];

    protected $hidden = [
        'encrypted_kek',
        'encrypted_private_key',
        'verification_master_key',
        'kdf_salt',
        'kdf_params',
    ];

    protected $casts = [
        'kdf_params' => 'array',
        'encrypted_kek' => 'array',
        'encrypted_private_key' => 'array',
        'verification_master_key' => 'array',
        'version_schema' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
