<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Airline extends Model
{
    protected $fillable = ['name', 'sage_code', 'is_archived'];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Aircraft> */
    public function aircraft()
    {
        return $this->hasMany(Aircraft::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Flight> */
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    // Scope: solo activas (no archivadas)
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }
}
