<div>
    <!-- Header -->
    <div class="page-header d-flex align-items-center gap-3">
        <a href="{{ route('flights.index') }}" class="action-btn" style="border:none;background:#e2e8f0;">
            <i class="fa-solid fa-arrow-left" style="font-size:13px;"></i>
        </a>
        <div>
            <h1 class="page-title">Crear Vuelo</h1>
            <p class="page-subtitle">Registrar un nuevo vuelo operacional</p>
        </div>
    </div>

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
                                       placeholder="AV040-041" style="text-transform:uppercase;font-weight:700;letter-spacing:.05em;">
                                @error('flight_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha <span style="color:#f43f5e;">*</span></label>
                                <input wire:model="flight_date" type="text" id="id_date"
                                       class="form-control datepicker @error('flight_date') is-invalid @enderror"
                                       placeholder="dd/mm/yyyy" autocomplete="off">
                                @error('flight_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Servicio</label>
                                <select wire:model="tipo_servicio" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    @foreach(\App\Models\Flight::TIPOS_SERVICIO as $tipo)
                                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aerolínea <span style="color:#f43f5e;">*</span></label>
                                <select wire:model.live="airline_id" class="form-select @error('airline_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach($airlines as $airline)
                                        <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                                    @endforeach
                                </select>
                                @error('airline_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aeronave / Matrícula</label>
                                <select wire:model="aircraft_id" class="form-select" {{ !$airline_id ? 'disabled' : '' }}>
                                    <option value="">{{ !$airline_id ? 'Primero seleccione aerolínea' : 'Seleccionar...' }}</option>
                                    @foreach($aircraftOptions as $id => $reg)
                                        <option value="{{ $id }}">{{ $reg }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Origen</label>
                                <input wire:model="origen" type="text" class="form-control"
                                       placeholder="BOG" maxlength="3"
                                       style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:.1em;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Destino</label>
                                <input wire:model="destino" type="text" class="form-control"
                                       placeholder="MIA" maxlength="3"
                                       style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:.1em;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gate / Puerta</label>
                                <input wire:model="gate" type="text" class="form-control" placeholder="142">
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

                <!-- Sección 2: Tiempos operacionales -->
                <div class="app-card mb-4">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="fa-regular fa-clock me-2" style="color:#3b82f6;"></i>Tiempos Operacionales</span>
                        <span style="font-size:11px;color:var(--text-muted);">Formato 24h (HH:MM)</span>
                    </div>
                    <div class="app-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">ETA (Llegada Estimada)</label>
                                <input wire:model="eta" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ATA (Llegada Real)</label>
                                <input wire:model="ata" type="time" class="form-control" style="font-family:monospace;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">STD (Salida Programada)</label>
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
                                <input wire:model="pax_in" type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Pax Salida</label>
                                <input wire:model="pax_out" type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Maletas Descarga</label>
                                <input wire:model="bags_offloading" type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Maletas Carga</label>
                                <input wire:model="bags_loading" type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ULDs Descarga</label>
                                <input wire:model="ulds_offloading" type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">ULDs Carga</label>
                                <input wire:model="ulds_loading" type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kgs Entrada</label>
                                <input wire:model="kgs_inbound" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kgs Salida</label>
                                <input wire:model="kgs_outbound" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
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
                                       placeholder="Ej: 93/0017 (separa por /)">
                                <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Si son varios, separa con "/"</div>
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
                                <textarea wire:model="observaciones" class="form-control" rows="3"
                                          placeholder="Notas del líder que atendió el vuelo..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Col lateral ────────────────────────────── -->
            <div class="col-xl-4">

                <!-- Facturación (solo billing/admin) -->
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
                                <input wire:model="vuelo_pagado" type="number" step="0.01" min="0" class="form-control" style="padding-left:28px;" placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Fumigación (USD)</label>
                            <div style="position:relative;">
                                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-weight:600;">$</span>
                                <input wire:model="fumigacion" type="number" step="0.01" min="0" class="form-control" style="padding-left:28px;" placeholder="0.00">
                            </div>
                        </div>
                        @if($vuelo_pagado > 0 || $fumigacion > 0)
                        <div style="margin-top:12px;padding:10px 14px;background:#f1f5f9;border-radius:8px;">
                            <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;">Total</div>
                            <div style="font-size:20px;font-weight:800;color:#4338ca;">${{ number_format($vuelo_pagado + $fumigacion, 2) }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Documentos -->
                <div class="app-card">
                    <div class="app-card-header">
                        <span class="app-card-title"><i class="fa-solid fa-paperclip me-2" style="color:var(--text-secondary);"></i>Documentos</span>
                        <button type="button" wire:click="addFileRow" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="app-card-body">
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:12px;">PDF, Word, Excel — máx. 10 MB c/u</div>

                        @foreach($newFiles as $i => $fileRow)
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size:11px;font-weight:600;color:var(--text-muted);">Archivo {{ $i + 1 }}</span>
                                <button type="button" wire:click="removeFileRow({{ $i }})"
                                        style="background:none;border:none;color:#e11d48;cursor:pointer;padding:0;font-size:12px;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <input wire:model="newFiles.{{ $i }}.file" type="file"
                                   class="form-control mb-2 @error("newFiles.{$i}.file") is-invalid @enderror"
                                   style="font-size:11px;">
                            @error("newFiles.{$i}.file") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <select wire:model="newFiles.{{ $i }}.doc_type_id"
                                    class="form-select @error("newFiles.{$i}.doc_type_id") is-invalid @enderror"
                                    style="font-size:12px;">
                                <option value="">Tipo de documento...</option>
                                @foreach($documentTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                            @error("newFiles.{$i}.doc_type_id") <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endforeach

                        @if(empty($newFiles))
                        <div style="text-align:center;padding:20px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:10px;color:var(--text-muted);font-size:12px;">
                            <i class="fa-solid fa-cloud-arrow-up" style="display:block;font-size:20px;margin-bottom:6px;opacity:.4;"></i>
                            Haz clic en "+" para adjuntar
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- Actions sticky -->
        <div style="position:sticky;bottom:0;background:white;border-top:1px solid var(--border);padding:14px 0;margin-top:24px;z-index:50;">
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="fa-solid fa-floppy-disk me-2"></i>Crear Vuelo</span>
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
    flatpickr('.datepicker', {
        dateFormat: 'd/m/Y',
        locale: 'es',
        allowInput: true,
        onChange: function (selectedDates, dateStr) {
            var parts = dateStr.split('/');
            if (parts.length === 3) {
                @this.set('flight_date', parts[2] + '-' + parts[1] + '-' + parts[0]);
            }
        }
    });
});
</script>
@endpush
