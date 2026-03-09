<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Flight extends Model
{
    protected $fillable = [
        // Core
        'odoo_ref',
        'flight_number',
        'flight_date',
        'airline_id',
        'aircraft_id',
        'created_by',
        'billing_user_id',
        'status',
        'approved_by',
        'approved_at',
        'billed_at',
        'sage_exported_at',
        // CCO operational
        'tipo_servicio',
        'origen',
        'destino',
        'gate',
        'eta',
        'ata',
        'std',
        'block_in',
        'block_off',
        'start_offloading',
        'end_offloading',
        'start_loading',
        'end_loading',
        'pax_in',
        'pax_out',
        'bags_offloading',
        'bags_loading',
        'ulds_offloading',
        'ulds_loading',
        'kgs_inbound',
        'kgs_outbound',
        'delay_codes',
        'delay_responsibility',
        'observaciones',
        'leader_id',
        // Billing
        'vuelo_pagado',
        'fumigacion',
    ];

    protected $casts = [
        'flight_date'      => 'date',
        'approved_at'      => 'datetime',
        'billed_at'        => 'datetime',
        'eta'              => 'datetime',
        'ata'              => 'datetime',
        'std'              => 'datetime',
        'block_in'         => 'datetime',
        'block_off'        => 'datetime',
        'vuelo_pagado'     => 'decimal:2',
        'fumigacion'       => 'decimal:2',
        'kgs_inbound'      => 'decimal:2',
        'kgs_outbound'     => 'decimal:2',
    ];

    // Tipo de servicio options
    const TIPOS_SERVICIO = [
        'Escala Comercial Pasajero',
        'Escala Comercial Carguero',
        'Escala Técnica Pasajero',
        'Escala Técnica Carguero',
        'Parada Técnica',
        'Cargo',
        'Cancelado',
        'Retorno',
        'Anulado',
    ];

    const DELAY_RESPONSIBILITIES = [
        'INCONTROLABLE',
        'CONTROLABLE',
        'EN TIEMPO',
    ];

    // Status constants
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_BILLED   = 'billed';

    // ─── Relationships ────────────────────────────────────────────────

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Airline, Flight> */
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Aircraft, Flight> */
    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Flight> */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Flight> */
    public function billingUser()
    {
        return $this->belongsTo(User::class, 'billing_user_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Flight> */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Flight> */
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<User> */
    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'flight_supervisors');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Document> */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeBilled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_BILLED);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING  => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_BILLED   => 'Facturado',
            default               => ucfirst($this->status),
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isBilled(): bool
    {
        return $this->status === self::STATUS_BILLED;
    }

    public function getTotalPagadoAttribute(): float
    {
        return (float)$this->vuelo_pagado + (float)$this->fumigacion;
    }

    public function isCarguero(): bool
    {
        return str_contains(strtolower((string)$this->tipo_servicio), 'carguero')
            || str_contains(strtolower((string)$this->tipo_servicio), 'cargo');
    }
}
