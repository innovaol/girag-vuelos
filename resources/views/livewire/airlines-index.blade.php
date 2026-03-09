<div>
    <div class="page-header d-flex align-items-start justify-content-between">
        <div>
            <h1 class="page-title">Aerolíneas</h1>
            <p class="page-subtitle">Catálogo de aerolíneas registradas en el sistema</p>
        </div>
        <button wire:click="openCreate" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Nueva Aerolínea
        </button>
    </div>

    <div class="app-card">
        <div class="table-search">
            <input wire:model.live.debounce.300ms="search"
                   class="table-search-input" type="text" placeholder="Buscar aerolínea o código Sage…">
            <div class="table-meta">{{ $airlines->total() }} aerolínea{{ $airlines->total() !== 1 ? 's' : '' }}</div>
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
                            <button class="sort-btn {{ $sortField === 'sage_code' ? 'active' : '' }}" wire:click="sort('sage_code')">
                                Código Sage
                                <i class="fa-solid {{ $sortField === 'sage_code' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'sage_code' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th style="text-align:right;padding-right:20px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($airlines as $airline)
                    <tr wire:key="airline-{{ $airline->id }}">
                        <td class="fw-semibold">{{ $airline->name }}</td>
                        <td>
                            @if($airline->sage_code)
                                <span style="font-family:monospace;background:#f1f5f9;border-radius:5px;padding:2px 8px;font-size:12px;font-weight:600;">{{ $airline->sage_code }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;padding-right:16px;">
                            <div class="action-btn-group" style="justify-content:flex-end;">
                                <button wire:click="openEdit({{ $airline->id }})" class="action-btn action-btn-edit" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button wire:click="archive({{ $airline->id }})"
                                        onclick="return confirm('¿Archivar la aerolínea {{ addslashes($airline->name) }}?');"
                                        class="action-btn action-btn-delete" title="Archivar">
                                    <i class="fa-solid fa-archive"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:48px;color:var(--text-muted);">
                            <i class="fa-solid fa-building-columns" style="font-size:28px;display:block;margin-bottom:10px;opacity:.3;"></i>
                            No hay aerolíneas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $airlines->links() }}
    </div>

    <!-- ── Modal ──────────────────────────────────────────── -->
    <div class="modal fade" id="airline-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-weight:700;font-size:15px;">
                        {{ $editingId ? 'Editar Aerolínea' : 'Nueva Aerolínea' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body" style="padding:24px;">
                        <div class="mb-3">
                            <label class="form-label">Nombre <span style="color:#f43f5e;">*</span></label>
                            <input wire:model="name" type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="AMERICAN AIRLINES" style="text-transform:uppercase;" autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Código Sage</label>
                            <input wire:model="sage_code" type="text" class="form-control"
                                   placeholder="Ej: AA001"
                                   style="font-family:monospace;font-weight:600;letter-spacing:.05em;">
                            <div style="font-size:11px;color:var(--text-muted);margin-top:5px;">
                                Se usa como Customer ID en las exportaciones a Sage 50.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $editingId ? 'Guardar cambios' : 'Registrar' }}</span>
                            <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
