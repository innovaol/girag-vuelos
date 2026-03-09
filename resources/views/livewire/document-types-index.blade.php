<div>
    <div class="page-header d-flex align-items-start justify-content-between">
        <div>
            <h1 class="page-title">Tipos de Documento</h1>
            <p class="page-subtitle">Clasificación de documentos adjuntos a los vuelos</p>
        </div>
        <button wire:click="openCreate" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Nuevo Tipo
        </button>
    </div>

    <div class="app-card">
        <div class="table-search">
            <input wire:model.live.debounce.300ms="search"
                   class="table-search-input" type="text" placeholder="Buscar tipo de documento…">
            <div class="table-meta">{{ $docTypes->total() }} tipo{{ $docTypes->total() !== 1 ? 's' : '' }}</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="app-table" style="width:100%;">
                <thead>
                    <tr>
                        <th style="width:60px;color:var(--text-muted);">#</th>
                        <th>
                            <button class="sort-btn {{ $sortField === 'name' ? 'active' : '' }}" wire:click="sort('name')">
                                Nombre
                                <i class="fa-solid {{ $sortField === 'name' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'name' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th style="text-align:right;padding-right:20px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docTypes as $dt)
                    <tr wire:key="dt-{{ $dt->id }}">
                        <td style="color:var(--text-muted);font-size:12px;">{{ $dt->id }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:28px;height:28px;border-radius:7px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fa-solid fa-file-lines" style="color:var(--accent);font-size:11px;"></i>
                                </div>
                                <span class="fw-semibold">{{ $dt->name }}</span>
                            </div>
                        </td>
                        <td style="text-align:right;padding-right:16px;">
                            <div class="action-btn-group" style="justify-content:flex-end;">
                                <button wire:click="openEdit({{ $dt->id }})" class="action-btn action-btn-edit" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button wire:click="archive({{ $dt->id }})"
                                        onclick="return confirm('¿Archivar este tipo de documento?');"
                                        class="action-btn action-btn-delete" title="Archivar">
                                    <i class="fa-solid fa-archive"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align:center;padding:48px;color:var(--text-muted);">
                            <i class="fa-solid fa-folder-open" style="font-size:28px;display:block;margin-bottom:10px;opacity:.3;"></i>
                            No hay tipos de documento registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $docTypes->links() }}
    </div>

    <!-- ── Modal ──────────────────────────────────────────── -->
    <div class="modal fade" id="doctype-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-weight:700;font-size:15px;">
                        {{ $editingId ? 'Editar Tipo de Documento' : 'Nuevo Tipo de Documento' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body" style="padding:24px;">
                        <div>
                            <label class="form-label">Nombre <span style="color:#f43f5e;">*</span></label>
                            <input wire:model="name" type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Reporte de Rampa" autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
