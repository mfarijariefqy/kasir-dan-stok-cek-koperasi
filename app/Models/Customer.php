<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'address', 'notes', 'is_active', 'user_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hasLoginAccount(): bool
    {
        return $this->user_id !== null;
    }
}
