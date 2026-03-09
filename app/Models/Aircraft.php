<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Aircraft extends Model
{
    protected $fillable = ['registration_number', 'model', 'airline_id', 'parent_aircraft_id', 'is_archived'];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Airline, Aircraft> */
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Aircraft, Aircraft> */
    public function parentAircraft()
    {
        return $this->belongsTo(Aircraft::class, 'parent_aircraft_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Aircraft> */
    public function childAircrafts()
    {
        return $this->hasMany(Aircraft::class, 'parent_aircraft_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Flight> */
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    // Scope: solo activas
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }
}
