<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_flight_supervisor',
        'is_billing_supervisor',
        'is_admin_vuelos',
        'is_archived',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'is_flight_supervisor'   => 'boolean',
            'is_billing_supervisor'  => 'boolean',
            'is_admin_vuelos'        => 'boolean',
            'is_archived'            => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Flight> */
    public function createdFlights()
    {
        return $this->hasMany(Flight::class, 'created_by');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Flight> */
    public function billedFlights()
    {
        return $this->hasMany(Flight::class, 'billing_user_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Flight> */
    public function supervisedFlights()
    {
        return $this->belongsToMany(Flight::class, 'flight_supervisors');
    }

    // ─── Permission Helpers ───────────────────────────────────────────

    /** Puede crear/editar vuelos en estado pending */
    public function canCreateFlight(): bool
    {
        return !$this->is_archived;
    }

    /** Puede otorgar VoBo (aprobar vuelos pendientes) */
    public function canApproveFlight(): bool
    {
        return $this->is_flight_supervisor && !$this->is_archived;
    }

    /** Puede marcar un vuelo aprobado como facturado */
    public function canMarkAsBilled(): bool
    {
        return $this->is_billing_supervisor && !$this->is_archived;
    }

    /** Puede revertir un vuelo a pendiente (admin) */
    public function canAdminVuelos(): bool
    {
        return $this->is_admin_vuelos && !$this->is_archived;
    }
}
