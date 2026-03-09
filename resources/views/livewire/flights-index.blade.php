<div>
    <!-- Header -->
    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1 class="page-title">Gestión de Vuelos</h1>
            <p class="page-subtitle">Registro, aprobación y seguimiento de vuelos operacionales</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @can('create-flight')
            <a href="{{ route('flights.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>Nuevo Vuelo
            </a>
            @endcan
            @can('export-sage')
            @if($sageCount > 0)
            <button wire:click="exportSage" class="btn btn-success">
                <i class="fa-solid fa-file-arrow-down me-2"></i>Exportar Sage
                <span style="background:rgba(255,255,255,.25);border-radius:99px;padding:1px 8px;font-size:11px;margin-left:4px;">{{ $sageCount }}</span>
            </button>
            @endif
            @endcan
        </div>
    </div>

    <!-- ── Stat mini-cards ────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <button wire:click="$set('statusFilter', {{ $statusFilter === 'pending' ? "''" : "'pending'" }})"
                    style="width:100%;background:{{ $statusFilter === 'pending' ? '#fffbeb' : 'white' }};border:1px solid {{ $statusFilter === 'pending' ? '#fde68a' : '#e2e8f0' }};border-radius:12px;padding:16px 20px;cursor:pointer;text-align:left;transition:all .15s ease;">
                <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Pendientes</div>
                <div style="font-size:28px;font-weight:800;letter-spacing:-.04em;color:#f59e0b;">{{ $pendingCount }}</div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button wire:click="$set('statusFilter', {{ $statusFilter === 'approved' ? "''" : "'approved'" }})"
                    style="width:100%;background:{{ $statusFilter === 'approved' ? '#eff6ff' : 'white' }};border:1px solid {{ $statusFilter === 'approved' ? '#bfdbfe' : '#e2e8f0' }};border-radius:12px;padding:16px 20px;cursor:pointer;text-align:left;transition:all .15s ease;">
                <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Aprobados (VoBo)</div>
                <div style="font-size:28px;font-weight:800;letter-spacing:-.04em;color:#3b82f6;">{{ $approvedCount }}</div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <button wire:click="$set('statusFilter', {{ $statusFilter === 'billed' ? "''" : "'billed'" }})"
                    style="width:100%;background:{{ $statusFilter === 'billed' ? '#ecfdf5' : 'white' }};border:1px solid {{ $statusFilter === 'billed' ? '#a7f3d0' : '#e2e8f0' }};border-radius:12px;padding:16px 20px;cursor:pointer;text-align:left;transition:all .15s ease;">
                <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Facturados</div>
                <div style="font-size:28px;font-weight:800;letter-spacing:-.04em;color:#10b981;">{{ $billedCount }}</div>
            </button>
        </div>
        <div class="col-6 col-lg-3">
            <div style="background:white;border-radius:12px;padding:16px 20px;border:1px solid #e2e8f0;">
                <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Pendiente Sage</div>
                <div style="font-size:28px;font-weight:800;letter-spacing:-.04em;color:#6366f1;">{{ $sageCount }}</div>
            </div>
        </div>
    </div>

    <!-- ── Table ──────────────────────────────────────────── -->
    <div class="app-card">
        <!-- Search bar -->
        <div class="table-search">
            <input wire:model.live.debounce.300ms="search"
                   class="table-search-input"
                   type="text"
                   placeholder="Buscar vuelo, aerolínea, aeronave…">
            <div class="table-meta">
                {{ $flights->total() }} vuelo{{ $flights->total() !== 1 ? 's' : '' }}
                @if($statusFilter)
                    <button wire:click="$set('statusFilter', '')"
                            style="margin-left:8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:99px;padding:2px 10px;font-size:11px;font-weight:600;color:var(--text-secondary);cursor:pointer;">
                        {{ $statusFilter }} ✕
                    </button>
                @endif
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="app-table" style="width:100%;min-width:700px;">
                <thead>
                    <tr>
                        <th>
                            <button class="sort-btn {{ $sortField === 'flight_number' ? 'active' : '' }}"
                                    wire:click="sort('flight_number')">
                                N° Vuelo
                                <i class="fa-solid {{ $sortField === 'flight_number' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'flight_number' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th>
                            <button class="sort-btn {{ $sortField === 'flight_date' ? 'active' : '' }}"
                                    wire:click="sort('flight_date')">
                                Fecha
                                <i class="fa-solid {{ $sortField === 'flight_date' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'flight_date' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th>Aerolínea</th>
                        <th>Aeronave</th>
                        <th>
                            <button class="sort-btn {{ $sortField === 'status' ? 'active' : '' }}"
                                    wire:click="sort('status')">
                                Estado
                                <i class="fa-solid {{ $sortField === 'status' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'status' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th>Creado por</th>
                        <th style="width:1%;white-space:nowrap;text-align:right;padding-right:20px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flights as $flight)
                    <tr wire:key="flight-{{ $flight->id }}">
                        <td>
                            <a href="{{ route('flights.edit', $flight->id) }}"
                               style="font-weight:700;color:var(--accent);text-decoration:none;font-size:14px;letter-spacing:.02em;">
                                {{ $flight->flight_number }}
                            </a>
                        </td>
                        <td style="color:var(--text-secondary);">{{ $flight->flight_date->format('d/m/Y') }}</td>
                        <td class="fw-semibold">{{ $flight->airline->name ?? '—' }}</td>
                        <td style="color:var(--text-secondary);font-size:13px;">{{ $flight->aircraft->registration_number ?? '—' }}</td>
                        <td>
                            @if($flight->status === 'pending')
                                <span class="status-pill status-pending">Pendiente</span>
                            @elseif($flight->status === 'approved')
                                <span class="status-pill status-approved">Aprobado</span>
                            @else
                                <span class="status-pill status-billed">Facturado</span>
                            @endif
                        </td>
                        <td style="color:var(--text-secondary);font-size:13px;">{{ $flight->creator->name ?? '—' }}</td>
                        <td style="text-align:right;padding-right:16px;">
                            <div class="action-btn-group" style="justify-content:flex-end;">
                                @if($flight->status === 'pending')
                                    @can('edit-flight')
                                    <a href="{{ route('flights.edit', $flight->id) }}" class="action-btn action-btn-edit" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    @endcan
                                    @can('approve-flight')
                                    <button wire:click="approve({{ $flight->id }})"
                                            onclick="return confirm('¿Otorgar VoBo al vuelo {{ $flight->flight_number }}?');"
                                            class="action-btn action-btn-approve" title="Otorgar VoBo">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                    @endcan
                                    @can('delete-flight')
                                    <button wire:click="delete({{ $flight->id }})"
                                            onclick="return confirm('¿Eliminar vuelo {{ $flight->flight_number }}? No se puede deshacer.');"
                                            class="action-btn action-btn-delete" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @endcan
                                @elseif($flight->status === 'approved')
                                    <a href="{{ route('flights.edit', $flight->id) }}" class="action-btn action-btn-view" title="Ver detalle">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @can('bill-flight')
                                    <button wire:click="markAsBilled({{ $flight->id }})"
                                            onclick="return confirm('¿Marcar vuelo {{ $flight->flight_number }} como facturado?');"
                                            class="action-btn action-btn-bill" title="Facturar">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </button>
                                    @endcan
                                    @can('admin-vuelos')
                                    <button wire:click="revertToPending({{ $flight->id }})"
                                            onclick="return confirm('¿Revertir a Pendiente?');"
                                            class="action-btn action-btn-revert" title="Revertir a Pendiente">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                    @endcan
                                @else
                                    <a href="{{ route('flights.edit', $flight->id) }}" class="action-btn action-btn-view" title="Ver detalle">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @can('admin-vuelos')
                                    <button wire:click="revertToPending({{ $flight->id }})"
                                            onclick="return confirm('¿Revertir a Pendiente?');"
                                            class="action-btn action-btn-revert" title="Revertir a Pendiente">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:56px;color:var(--text-muted);">
                            <i class="fa-solid fa-plane-slash" style="font-size:32px;display:block;margin-bottom:12px;opacity:.3;"></i>
                            <div style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">No hay vuelos</div>
                            <div style="font-size:12px;">
                                @if($search)Ningún vuelo coincide con "{{ $search }}"
                                @elseif($statusFilter)No hay vuelos con estado "{{ $statusFilter }}"
                                @else
                                    @can('create-flight')<a href="{{ route('flights.create') }}" style="color:var(--accent);">Crear el primer vuelo</a>@endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {{ $flights->links() }}
    </div>
</div>
