<div>
    <div class="page-header d-flex align-items-start justify-content-between">
        <div>
            <h1 class="page-title">Importar desde Odoo</h1>
            <p class="page-subtitle">Carga el reporte exportado desde Odoo para importar vuelos al sistema</p>
        </div>
        @if($parsed)
        <button wire:click="$set('parsed', false)" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-2"></i>Subir otro archivo
        </button>
        @endif
    </div>

    {{-- Resultados de importación --}}
    @foreach($results as $result)
    <div style="margin-bottom:12px;padding:14px 18px;border-radius:10px;
                background:{{ $result['status'] === 'success' ? '#ecfdf5' : '#fff1f2' }};
                border:1px solid {{ $result['status'] === 'success' ? '#a7f3d0' : '#fecdd3' }};
                color:{{ $result['status'] === 'success' ? '#065f46' : '#be123c' }};
                font-size:13px;font-weight:500;">
        <i class="fa-solid {{ $result['status'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' }} me-2"></i>
        {{ $result['msg'] }}
    </div>
    @endforeach

    @if($errorMsg)
    <div style="margin-bottom:12px;padding:14px 18px;border-radius:10px;background:#fff1f2;
                border:1px solid #fecdd3;color:#be123c;font-size:13px;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{!! $errorMsg !!}
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════
         PASO 1 — UPLOAD
         ══════════════════════════════════════════════════ --}}
    @if(!$parsed)
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="app-card">
                <div class="app-card-header">
                    <span class="app-card-title">
                        <i class="fa-solid fa-file-arrow-up me-2" style="color:var(--accent);"></i>Subir archivo Odoo
                    </span>
                </div>
                <div class="app-card-body">
                    <div>

                        {{-- Drop zone — visible solo cuando NO hay archivo aún --}}
                        @if(!$file)
                        <div id="drop-zone"
                             onclick="document.getElementById('odoo-file-input').click()"
                             style="border:2px dashed #c7d2fe;border-radius:12px;padding:40px 24px;
                                    text-align:center;background:#f8fafc;cursor:pointer;
                                    transition:all .2s;">
                            <i id="dz-icon" class="fa-solid fa-cloud-arrow-up"
                               style="font-size:42px;color:#a5b4fc;display:block;margin-bottom:14px;"></i>
                            <div style="font-weight:700;font-size:15px;color:var(--text-primary);margin-bottom:5px;">
                                Arrastra aquí o haz clic para seleccionar
                            </div>
                            <div style="font-size:12px;color:var(--text-muted);">
                                Solo archivos <strong>.xlsx</strong> exportados desde Odoo
                            </div>
                        </div>
                        @endif

                        {{-- Input oculto con wire:model --}}
                        <input wire:model="file"
                               type="file"
                               id="odoo-file-input"
                               accept=".xlsx,.xls"
                               style="display:none;">

                        {{-- Livewire uploading… spinner --}}
                        <div wire:loading wire:target="file"
                             style="margin-top:16px;padding:24px;text-align:center;
                                    border:2px dashed #c7d2fe;border-radius:12px;background:#f8fafc;">
                            <div class="spinner-border" style="color:#6366f1;width:2rem;height:2rem;"></div>
                            <div style="margin-top:10px;font-size:13px;font-weight:600;color:#6366f1;">Subiendo archivo…</div>
                        </div>

                        {{-- Archivo cargado — badge (Blade lo controla, sobrevive re-renders) --}}
                        @if($file)
                        <div wire:loading.remove wire:target="file"
                             style="display:flex;align-items:center;gap:14px;padding:16px 18px;
                                    background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;">
                            <i class="fa-solid fa-file-excel" style="color:#6366f1;font-size:26px;flex-shrink:0;"></i>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    {{ $file->getClientOriginalName() }}
                                </div>
                                <div style="font-size:11px;color:var(--text-muted);">
                                    Archivo listo para analizar
                                </div>
                            </div>
                            <button type="button"
                                    wire:click="$set('file', null)"
                                    style="background:none;border:none;color:#94a3b8;cursor:pointer;
                                           font-size:16px;padding:4px;line-height:1;"
                                    title="Quitar archivo">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        @endif

                        @error('file')
                        <div style="margin-top:8px;color:#e11d48;font-size:12px;font-weight:500;">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                        </div>
                        @enderror

                        {{-- Botón Analizar --}}
                        <button type="button"
                                wire:click="analyzeFile"
                                class="btn btn-primary w-100"
                                style="margin-top:16px;"
                                @if(!$file) disabled @endif
                                wire:loading.attr="disabled"
                                wire:target="analyzeFile">
                            <span wire:loading.remove wire:target="analyzeFile">
                                <i class="fa-solid fa-magnifying-glass me-2"></i>
                                Analizar archivo
                            </span>
                            <span wire:loading wire:target="analyzeFile">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Analizando…
                            </span>
                        </button>

                    </div>
                </div>
            </div>
        </div>

        {{-- Formato esperado --}}
        <div class="col-lg-6">
            <div class="app-card" style="height:100%;">
                <div class="app-card-header">
                    <span class="app-card-title">
                        <i class="fa-solid fa-circle-info me-2" style="color:#3b82f6;"></i>Formato esperado
                    </span>
                </div>
                <div class="app-card-body">
                    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px;">
                        El archivo debe ser el reporte exportado directamente desde Odoo
                        (<code>reporte.reporte.xlsx</code>) con estas columnas:
                    </p>
                    <table class="app-table" style="font-size:12px;">
                        <thead><tr><th style="width:36px;">Col</th><th>Nombre en Odoo</th></tr></thead>
                        <tbody>
                            @foreach([
                                'Número de Reporte',
                                'Escoja el Vuelo',
                                'Línea Aérea',
                                'Tipo de Aeronave',
                                'ETA',
                                'Tipo de Servicio',
                                'Matrícula',
                                '(duplicado)',
                                'Estado del Reporte',
                                'Estado Vuelo'
                            ] as $i => $col)
                            <tr>
                                <td style="color:var(--text-muted);font-family:monospace;">{{ $i+1 }}</td>
                                <td>{{ $col }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div style="margin-top:14px;padding:12px 14px;background:#fffbeb;border-radius:8px;
                                font-size:12px;color:#92400e;border:1px solid #fde68a;">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Las aerolíneas y aeronaves que no existan se crearán automáticamente.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════
         PASO 2 — PREVIEW
         ══════════════════════════════════════════════════ --}}
    @else
    @php
        $newAirlines = collect($preview)->where('airline_status', 'new')->count();
        $newAircraft = collect($preview)->where('aircraft_status', 'new')->count();
        $duplicates  = collect($preview)->where('already_exists', true)->count();
        $importable  = collect($preview)->where('already_exists', false)->count();
    @endphp

    <div class="d-flex flex-wrap gap-3 mb-4">
        <div style="background:white;border:1px solid #e2e8f0;border-radius:10px;padding:12px 18px;min-width:120px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:4px;">Total filas</div>
            <div style="font-size:24px;font-weight:800;color:var(--text-primary);">{{ count($preview) }}</div>
        </div>
        <div style="background:white;border:1px solid #a7f3d0;border-radius:10px;padding:12px 18px;min-width:120px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:4px;">Importables</div>
            <div style="font-size:24px;font-weight:800;color:#10b981;">{{ $importable }}</div>
        </div>
        <div style="background:white;border:1px solid #fecdd3;border-radius:10px;padding:12px 18px;min-width:120px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:4px;">Duplicados</div>
            <div style="font-size:24px;font-weight:800;color:#e11d48;">{{ $duplicates }}</div>
        </div>
        @if($newAirlines > 0)
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 18px;min-width:120px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:4px;">Aerolíneas nuevas</div>
            <div style="font-size:24px;font-weight:800;color:#f59e0b;">{{ $newAirlines }}</div>
        </div>
        @endif
        @if($newAircraft > 0)
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 18px;min-width:120px;">
            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:4px;">Aeronaves nuevas</div>
            <div style="font-size:24px;font-weight:800;color:#3b82f6;">{{ $newAircraft }}</div>
        </div>
        @endif
    </div>

    @if($newAirlines > 0 || $newAircraft > 0)
    <div style="padding:14px 18px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;
                font-size:13px;color:#92400e;margin-bottom:20px;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        <strong>Atención:</strong> Se crearán automáticamente
        @if($newAirlines > 0)<strong>{{ $newAirlines }} aerolínea(s)</strong>@endif
        @if($newAirlines > 0 && $newAircraft > 0) y @endif
        @if($newAircraft > 0)<strong>{{ $newAircraft }} aeronave(s)</strong>@endif
        nuevas. Podrás editarlas en los catálogos después de la importación.
    </div>
    @endif

    <div class="app-card">
        <div class="app-card-header d-flex align-items-center justify-content-between">
            <span class="app-card-title">Vista previa — selecciona los vuelos a importar</span>
            <button type="button"
                    wire:click="importFlights"
                    class="btn btn-primary btn-sm"
                    wire:loading.attr="disabled"
                    wire:target="importFlights">
                <span wire:loading.remove wire:target="importFlights">
                    <i class="fa-solid fa-file-import me-2"></i>Importar seleccionados
                </span>
                <span wire:loading wire:target="importFlights">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>Importando…
                </span>
            </button>
        </div>
        <div style="overflow-x:auto;">
            <table class="app-table" style="min-width:900px;">
                <thead>
                    <tr>
                        <th style="width:44px;">
                            <input type="checkbox" class="form-check-input" id="select-all-chk"
                                   title="Seleccionar todos">
                        </th>
                        <th>Ref Odoo</th>
                        <th>N° Vuelo</th>
                        <th>Fecha</th>
                        <th>Aerolínea</th>
                        <th>Matrícula</th>
                        <th>Tipo Servicio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $row)
                    <tr style="{{ $row['already_exists'] ? 'opacity:.42;' : '' }}">
                        <td>
                            <input type="checkbox"
                                   class="form-check-input row-chk"
                                   data-idx="{{ $row['idx'] }}"
                                   wire:model="selected.{{ $row['idx'] }}"
                                   {{ $row['already_exists'] ? 'disabled' : '' }}>
                        </td>
                        <td style="font-size:11px;color:var(--text-muted);font-family:monospace;">
                            {{ $row['odoo_ref'] }}
                        </td>
                        <td class="fw-semibold">
                            {{ $row['flight_number'] ?? '—' }}
                            @if(!$row['flight_number'])
                            <span title="{{ $row['vuelo_raw'] }}"
                                  style="color:#e11d48;cursor:help;font-size:10px;">⚠</span>
                            @endif
                        </td>
                        <td style="color:var(--text-secondary);white-space:nowrap;">
                            {{ $row['flight_date']
                                ? \Carbon\Carbon::parse($row['flight_date'])->format('d/m/Y')
                                : '—' }}
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $row['airline_name'] ?: '—' }}</span>
                            @if($row['airline_status'] === 'new')
                            <span style="font-size:10px;background:#fffbeb;color:#92400e;
                                         padding:1px 7px;border-radius:99px;margin-left:5px;font-weight:700;">NUEVA</span>
                            @endif
                        </td>
                        <td style="font-family:monospace;font-size:12px;font-weight:700;">
                            {{ $row['matricula'] ?: '—' }}
                            @if($row['aircraft_status'] === 'new' && $row['matricula'])
                            <span style="font-size:10px;background:#eff6ff;color:#1d4ed8;
                                         padding:1px 7px;border-radius:99px;margin-left:5px;font-weight:700;">NUEVA</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:var(--text-secondary);">
                            {{ $row['tipo_servicio'] ?: '—' }}
                        </td>
                        <td>
                            @if($row['already_exists'])
                            <span class="status-pill" style="background:#f1f5f9;color:#64748b;">Duplicado</span>
                            @else
                            <span class="status-pill status-pending">Listo</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
(function () {

    function init() {
        /* ── Drag & drop ─────────────────────────────────────── */
        const dz  = document.getElementById('drop-zone');
        const inp = document.getElementById('odoo-file-input');
        if (dz && inp) {
            ['dragenter','dragover','dragleave','drop'].forEach(ev =>
                dz.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); })
            );
            dz.addEventListener('dragenter', () => {
                dz.style.borderColor = '#6366f1';
                dz.style.background  = '#eef2ff';
                document.getElementById('dz-icon').style.color = '#6366f1';
            });
            dz.addEventListener('dragover', () => {
                dz.style.borderColor = '#6366f1';
                dz.style.background  = '#eef2ff';
            });
            dz.addEventListener('dragleave', (e) => {
                if (!dz.contains(e.relatedTarget)) {
                    dz.style.borderColor = '#c7d2fe';
                    dz.style.background  = '#f8fafc';
                    document.getElementById('dz-icon').style.color = '#a5b4fc';
                }
            });
            dz.addEventListener('drop', (e) => {
                dz.style.borderColor = '#c7d2fe';
                dz.style.background  = '#f8fafc';

                const files = e.dataTransfer.files;
                if (!files.length) return;

                const file = files[0];
                const ext  = file.name.split('.').pop().toLowerCase();
                if (ext !== 'xlsx' && ext !== 'xls') {
                    alert('Solo se aceptan archivos .xlsx');
                    return;
                }

                // Inject file into the input and trigger Livewire's wire:model
                const dt = new DataTransfer();
                dt.items.add(file);
                inp.files = dt.files;
                inp.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        /* ── Select-all checkbox ─────────────────────────────── */
        const allChk = document.getElementById('select-all-chk');
        if (allChk) {
            allChk.addEventListener('change', () => {
                document.querySelectorAll('.row-chk:not(:disabled)').forEach(c => {
                    if (c.checked !== allChk.checked) {
                        c.checked = allChk.checked;
                        c.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        }
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('livewire:navigated', init);

    // Re-attach after every Livewire DOM patch (important for drop zone reappearing)
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(({ snapshot, effect }) => {
                // Small delay for DOM to settle
                setTimeout(init, 50);
            });
        });
    }

})();
</script>
@endpush
