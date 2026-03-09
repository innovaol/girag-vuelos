<div>
    @php $readonly = $flight->status !== 'pending'; @endphp

    <!-- Header -->
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('flights.index') }}" class="action-btn" style="border:none;background:#e2e8f0;">
            <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <div>
            <h1 class="page-title">
                Vuelo {{ $flight->flight_number }}
                @if($readonly)
                <span class="status-pill {{ $flight->status === 'approved' ? 'status-approved' : 'status-billed' }}" style="margin-left:8px;vertical-align:middle;">
                    {{ ucfirst($flight->status) }}
                </span>
                @endif
            </h1>
            <p class="page-subtitle">{{ $flight->flight_date->format('d/m/Y') }}
                @if($readonly) — Solo se pueden editar campos operacionales y de facturación @endif
            </p>
        </div>
    </div>

    @if($readonly)
    <div style="padding:12px 18px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;font-size:13px;color:#92400e;margin-bottom:20px;">
        <i class="fa-solid fa-lock me-2"></i>
        Los datos básicos del vuelo (número, fecha, aerolínea, matrícula) están bloqueados porque el vuelo ya fue {{ $flight->status === 'approved' ? 'aprobado' : 'facturado' }}.
        Solo puedes actualizar los datos operacionales, estadísticas de tráfico y facturación.
    </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="row g-4">

            <!-- ── Col principal ──────────────────────────── -->
            <div class="col-xl-8">

                <!-- Sección 1: Identificación -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="fa-solid fa-plane me-2" style="color:var(--accent);"></i>Identificación del Vuelo</span>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">N° Vuelo <span style="color:#f43f5e;">*</span></label>
                                <input wire:model.blur="flight_number" type="text"
                                       class="form-control @error('flight_number') is-invalid @enderror"
                                       style="text-transform:uppercase;font-weight:700;letter-spacing:.05em;"
                                       {{ $readonly ? 'readonly' : '' }}>
                                @error('flight_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha <span style="color:#f43f5e;">*</span></label>
                                <input wire:model="flight_date" type="{{ $readonly ? 'text' : 'text' }}" id="edit_date"
                                       class="form-control {{ !$readonly ? 'datepicker' : '' }} @error('flight_date') is-invalid @enderror"
                                       {{ $readonly ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Servicio</label>
                                <select wire:model="tipo_servicio" class="form-select">
                                    <option value="">—</option>
                                    @foreach(\App\Models\Flight::TIPOS_SERVICIO as $tipo)
                                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aerolínea <span style="color:#f43f5e;">*</span></label>
                                @if($readonly)
                                    <input type="text" class="form-control" value="{{ $flight->airline->name ?? '—' }}" readonly>
                                @else
                                    <select wire:model.live="airline_id" class="form-select @error('airline_id') is-invalid @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($airlines as $airline)
                                            <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('airline_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aeronave / Matrícula</label>
                                @if($readonly)
                                    <input type="text" class="form-control" value="{{ $flight->aircraft->registration_number ?? '—' }}" readonly>
                                @else
                                    <select wire:model="aircraft_id" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        @foreach($aircraftOptions as $id => $reg)
                                            <option value="{{ $id }}">{{ $reg }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Origen</label>
                                <input wire:model="origen" type="text" class="form-control" maxlength="3"
                                       style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:.1em;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Destino</label>
                                <input wire:model="destino" type="text" class="form-control" maxlength="3"
                                       style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:.1em;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gate / Puerta</label>
                                <input wire:model="gate" type="text" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Líder CCO</label>
                                <select wire:model="leader_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Tiempos -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="fa-regular fa-clock me-2" style="color:#3b82f6;"></i>Tiempos Operacionales</span>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">ETA</label>
                                <input wire:model="eta" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ATA</label>
                                <input wire:model="ata" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">STD</label>
                                <input wire:model="std" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Block In</label>
                                <input wire:model="block_in" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Block Off</label>
                                <input wire:model="block_off" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Inicio Descarga</label>
                                <input wire:model="start_offloading" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fin Descarga</label>
                                <input wire:model="end_offloading" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Inicio Carga</label>
                                <input wire:model="start_loading" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fin Carga</label>
                                <input wire:model="end_loading" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Tráfico -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="fa-solid fa-people-group me-2" style="color:#10b981;"></i>Tráfico</span>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Pax Entrada</label>
                                <input wire:model="pax_in" type="number" min="0" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pax Salida</label>
                                <input wire:model="pax_out" type="number" min="0" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Maletas Desc.</label>
                                <input wire:model="bags_offloading" type="number" min="0" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Maletas Carga</label>
                                <input wire:model="bags_loading" type="number" min="0" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ULDs Desc.</label>
                                <input wire:model="ulds_offloading" type="number" min="0" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ULDs Carga</label>
                                <input wire:model="ulds_loading" type="number" min="0" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kgs Entrada</label>
                                <input wire:model="kgs_inbound" type="number" step="0.01" min="0" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kgs Salida</label>
                                <input wire:model="kgs_outbound" type="number" step="0.01" min="0" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Demoras -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="fa-solid fa-triangle-exclamation me-2" style="color:#f59e0b;"></i>Demoras y Observaciones</span>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Código(s) de Demora</label>
                                <input wire:model="delay_codes" type="text" class="form-control"
                                       placeholder="Ej: 93/0017">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Responsabilidad</label>
                                <select wire:model="delay_responsibility" class="form-select">
                                    <option value="">—</option>
                                    @foreach(\App\Models\Flight::DELAY_RESPONSIBILITIES as $r)
                                        <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea wire:model="observaciones" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Col lateral ────────────────────────────── -->
            <div class="col-xl-4">

                <!-- Facturación -->
                @if(auth()->user()->is_billing_supervisor || auth()->user()->is_admin_vuelos)
                <div class="app-card mb-4" style="border:1px solid #c7d2fe;">
                    <div class="app-card-header" style="background:#eef2ff;">
                        <span class="app-card-title" style="color:#4338ca;"><i class="fa-solid fa-dollar-sign me-2"></i>Facturación</span>
                        <span style="font-size:10px;background:#c7d2fe;color:#4338ca;padding:2px 7px;border-radius:99px;font-weight:700;">CONTABILIDAD</span>
                    </div>
                    <div class="app-card-body">
                        <div class="mb-3">
                            <label class="form-label">Vuelo Pagado (USD)</label>
                            <div style="position:relative;">
                                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:600;">$</span>
                                <input wire:model="vuelo_pagado" type="number" step="0.01" min="0" class="form-control" style="padding-left:28px;">
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Fumigación (USD)</label>
                            <div style="position:relative;">
                                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:600;">$</span>
                                <input wire:model="fumigacion" type="number" step="0.01" min="0" class="form-control" style="padding-left:28px;">
                            </div>
                        </div>
                        <div style="margin-top:12px;padding:10px 14px;background:#f1f5f9;border-radius:8px;">
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;">Total</div>
                            <div style="font-size:20px;font-weight:800;color:#4338ca;">${{ number_format($vuelo_pagado + $fumigacion, 2) }}</div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Documentos existentes -->
                <div class="app-card mb-4">
                    <div class="app-card-header d-flex align-items-center justify-content-between">
                        <span class="app-card-title"><i class="fa-solid fa-paperclip me-2" style="color:var(--text-secondary);"></i>Documentos</span>
                        <button type="button" wire:click="addFileRow" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="app-card-body">
                        @forelse($existingDocs as $doc)
                        <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#f8fafc;border-radius:8px;margin-bottom:8px;border:1px solid #e2e8f0;">
                            <i class="fa-solid fa-file" style="color:#94a3b8;font-size:16px;flex-shrink:0;"></i>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->original_name }}</div>
                                <div style="font-size:10px;color:var(--text-muted);">{{ $doc->docType->name ?? 'Sin tipo' }}</div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('documents.view', $doc->id) }}" target="_blank"
                                   class="action-btn action-btn-view" style="width:28px;height:28px;" title="Ver">
                                    <i class="fa-solid fa-eye" style="font-size:10px;"></i>
                                </a>
                                <button type="button" wire:click="removeExistingDoc({{ $doc->id }})"
                                        class="action-btn action-btn-delete" style="width:28px;height:28px;" title="Quitar">
                                    <i class="fa-solid fa-xmark" style="font-size:10px;"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div style="text-align:center;padding:16px;color:var(--text-muted);font-size:12px;">Sin documentos adjuntos</div>
                        @endforelse

                        @foreach($newFiles as $i => $fileRow)
                        <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;padding:12px;margin-bottom:10px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size:11px;font-weight:600;color:#6366f1;">Nuevo archivo</span>
                                <button type="button" wire:click="removeFileRow({{ $i }})"
                                        style="background:none;border:none;color:#e11d48;cursor:pointer;font-size:12px;padding:0;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <input wire:model="newFiles.{{ $i }}.file" type="file"
                                   class="form-control mb-2" style="font-size:11px;" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png">
                            <select wire:model="newFiles.{{ $i }}.doc_type_id" class="form-select" style="font-size:12px;">
                                <option value="">Tipo de documento...</option>
                                @foreach($documentTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Info estado -->
                <div class="app-card" style="background:#f8fafc;">
                    <div class="app-card-body">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-secondary);margin-bottom:12px;">Información del Vuelo</div>
                        <div style="display:grid;gap:8px;font-size:12px;">
                            <div style="display:flex;justify-content:space-between;">
                                <span style="color:var(--text-muted);">Estado</span>
                                <span class="status-pill {{ $flight->status === 'pending' ? 'status-pending' : ($flight->status === 'approved' ? 'status-approved' : 'status-billed') }}">
                                    {{ $flight->status_label }}
                                </span>
                            </div>
                            @if($flight->odoo_ref)
                            <div style="display:flex;justify-content:space-between;">
                                <span style="color:var(--text-muted);">Ref. Odoo</span>
                                <span style="font-family:monospace;font-weight:600;">{{ $flight->odoo_ref }}</span>
                            </div>
                            @endif
                            <div style="display:flex;justify-content:space-between;">
                                <span style="color:var(--text-muted);">Creado por</span>
                                <span>{{ $flight->creator->name ?? '—' }}</span>
                            </div>
                            @if($flight->approver)
                            <div style="display:flex;justify-content:space-between;">
                                <span style="color:var(--text-muted);">Aprobado por</span>
                                <span>{{ $flight->approver->name }}</span>
                            </div>
                            @endif
                            @if($flight->billingUser)
                            <div style="display:flex;justify-content:space-between;">
                                <span style="color:var(--text-muted);">Facturado por</span>
                                <span>{{ $flight->billingUser->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Actions -->
        <div style="position:sticky;bottom:0;background:white;border-top:1px solid var(--border);padding:14px 0;margin-top:24px;z-index:50;">
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="fa-solid fa-floppy-disk me-2"></i>Guardar Cambios</span>
                    <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>Guardando...</span>
                </button>
                <a href="{{ route('flights.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('#edit_date:not([readonly])')) {
        flatpickr('#edit_date', {
            dateFormat: 'Y-m-d',
            allowInput: true,
        });
    }
});
</script>
@endpush
