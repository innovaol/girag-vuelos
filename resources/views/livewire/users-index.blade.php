<div>
    <div class="page-header d-flex align-items-start justify-content-between">
        <div>
            <h1 class="page-title">Usuarios</h1>
            <p class="page-subtitle">Gestión de cuentas y permisos de acceso al sistema</p>
        </div>
        <button wire:click="openCreate" class="btn btn-primary">
            <i class="fa-solid fa-user-plus me-2"></i>Nuevo Usuario
        </button>
    </div>

    <div class="app-card">
        <div class="table-search">
            <input wire:model.live.debounce.300ms="search"
                   class="table-search-input" type="text" placeholder="Buscar por nombre o correo…">
            <div class="table-meta">{{ $users->total() }} usuario{{ $users->total() !== 1 ? 's' : '' }}</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="app-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>
                            <button class="sort-btn {{ $sortField === 'name' ? 'active' : '' }}" wire:click="sort('name')">
                                Nombre
                                <i class="fa-solid {{ $sortField === 'name' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'name' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th>
                            <button class="sort-btn {{ $sortField === 'email' ? 'active' : '' }}" wire:click="sort('email')">
                                Correo
                                <i class="fa-solid {{ $sortField === 'email' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'email' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th>Rol</th>
                        <th>Permisos</th>
                        <th style="text-align:right;padding-right:20px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:white;flex-shrink:0;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="fw-semibold">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td style="color:var(--text-secondary);font-size:13px;">{{ $user->email }}</td>
                        <td>
                            @if($user->is_admin_vuelos)
                                <span class="status-pill" style="background:#fef3c7;color:#92400e;">Admin</span>
                            @elseif($user->is_billing_supervisor && $user->is_flight_supervisor)
                                <span class="status-pill" style="background:#ede9fe;color:#5b21b6;">Completo</span>
                            @elseif($user->is_billing_supervisor)
                                <span class="status-pill" style="background:#d1fae5;color:#065f46;">Facturador</span>
                            @elseif($user->is_flight_supervisor)
                                <span class="status-pill" style="background:#dbeafe;color:#1d4ed8;">Supervisor</span>
                            @else
                                <span class="status-pill" style="background:#f1f5f9;color:#64748b;">Operador</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                @if($user->is_flight_supervisor)
                                <span title="Supervisor de Vuelos" style="width:22px;height:22px;border-radius:5px;background:#dbeafe;display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-check" style="font-size:9px;color:#1d4ed8;"></i>
                                </span>
                                @endif
                                @if($user->is_billing_supervisor)
                                <span title="Facturador" style="width:22px;height:22px;border-radius:5px;background:#d1fae5;display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-file-invoice" style="font-size:9px;color:#065f46;"></i>
                                </span>
                                @endif
                                @if($user->is_admin_vuelos)
                                <span title="Administrador" style="width:22px;height:22px;border-radius:5px;background:#fef3c7;display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-shield" style="font-size:9px;color:#92400e;"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:right;padding-right:16px;">
                            <div class="action-btn-group" style="justify-content:flex-end;">
                                <button wire:click="openEdit({{ $user->id }})" class="action-btn action-btn-edit" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                <button wire:click="archive({{ $user->id }})"
                                        onclick="return confirm('¿Archivar al usuario {{ addslashes($user->name) }}?');"
                                        class="action-btn action-btn-delete" title="Archivar">
                                    <i class="fa-solid fa-archive"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:48px;color:var(--text-muted);">
                            <i class="fa-solid fa-users" style="font-size:28px;display:block;margin-bottom:10px;opacity:.3;"></i>
                            No hay usuarios activos.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </div>

    <!-- ── Modal ──────────────────────────────────────────── -->
    <div class="modal fade" id="user-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-weight:700;font-size:15px;">
                        {{ $editingId ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body" style="padding:24px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre <span style="color:#f43f5e;">*</span></label>
                                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Correo electrónico <span style="color:#f43f5e;">*</span></label>
                                <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">
                                    Contraseña {!! $editingId ? '<span style="color:var(--text-muted);font-weight:400;text-transform:none;letter-spacing:0;">(vacío = sin cambios)</span>' : '<span style="color:#f43f5e;">*</span>' !!}
                                </label>
                                <input wire:model="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="{{ $editingId ? '••••••••' : 'Mínimo 8 caracteres' }}">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr style="border-color:var(--border);margin:20px 0;">

                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-secondary);margin-bottom:14px;">Permisos de Acceso</div>

                        <div style="display:grid;grid-template-columns:1fr;gap:10px;">
                            <label style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--border);border-radius:10px;cursor:pointer;transition:border-color .15s;"
                                   :class="{{ $is_flight_supervisor ? 'border-color:#c7d2fe;background:#eef2ff;' : '' }}">
                                <input wire:model="is_flight_supervisor" type="checkbox" class="form-check-input" style="flex-shrink:0;margin:0;">
                                <div>
                                    <div style="font-size:13px;font-weight:600;">Supervisor de Vuelos</div>
                                    <div style="font-size:11px;color:var(--text-muted);">Puede aprobar vuelos (otorgar VoBo)</div>
                                </div>
                            </label>
                            <label style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--border);border-radius:10px;cursor:pointer;">
                                <input wire:model="is_billing_supervisor" type="checkbox" class="form-check-input" style="flex-shrink:0;margin:0;">
                                <div>
                                    <div style="font-size:13px;font-weight:600;">Facturador</div>
                                    <div style="font-size:11px;color:var(--text-muted);">Puede marcar vuelos como facturados y exportar a Sage 50</div>
                                </div>
                            </label>
                            <label style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--border);border-radius:10px;cursor:pointer;">
                                <input wire:model="is_admin_vuelos" type="checkbox" class="form-check-input" style="flex-shrink:0;margin:0;">
                                <div>
                                    <div style="font-size:13px;font-weight:600;">Administrador del Sistema</div>
                                    <div style="font-size:11px;color:var(--text-muted);">Puede revertir vuelos y gestionar catálogos (aerolíneas, aeronaves, doc. types, usuarios)</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $editingId ? 'Guardar cambios' : 'Crear Usuario' }}</span>
                            <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
