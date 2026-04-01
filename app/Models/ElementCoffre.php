<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElementCoffre extends Model
{
    use SoftDeletes;

    protected $table = 'elements_coffres';

    protected $fillable = [
        'coffre_id',
        'type',
        'label',
        'url',
        'favicon_url',
        'payload_encrypted',
        'iv',
        'auth_tag',
        'version_schema',
        'favori',
    ];

    protected $hidden = [
        'payload_encrypted',
        'iv',
        'auth_tag',
    ];

    public function casts(): array
    {
        return [
            'favori' => 'boolean',
            'version_schema' => 'integer',
        ];
    }

    public function coffre(): BelongsTo
    {
        return $this->belongsTo(Coffre::class, 'coffre_id');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);

    }

    public function scopeFavoris($query)
    {
        return $query->where('favori', true);
    }

    public function donneesChiffrement(): array
    {
        return [
            'payload' => $this->payload_encrypted,
            'iv' => $this->iv,
            'tag' => $this->auth_tag,
        ];
    }
}
