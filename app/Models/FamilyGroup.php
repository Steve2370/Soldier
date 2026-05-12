<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyGroup extends Model
{
    protected $fillable = ['owner_id', 'nom'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, FamilyMember::class, 'family_group_id', 'id', 'id', 'user_id');
    }

    public function isFull(): bool
    {
        return $this->members()->count() >= 6;
    }
}
