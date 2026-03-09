<div>
    <div class="page-header d-flex align-items-start justify-content-between">
        <div>
            <h1 class="page-title">Aeronaves</h1>
            <p class="page-subtitle">Catálogo de aeronaves registradas por aerolínea</p>
        </div>
        <button wire:click="openCreate" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Nueva Aeronave
        </button>
    </div>

    <div class="app-card">
        <div class="table-search">
            <input wire:model.live.debounce.300ms="search"
                   class="table-search-input" type="text" placeholder="Buscar matrícula, modelo, aerolínea…">
            <div class="table-meta">{{ $aircraft->total() }} aeronave{{ $aircraft->total() !== 1 ? 's' : '' }}</div>
        </div>

        <div style="overflow-x:auto;">
            <table class="app-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>
                            <button class="sort-btn {{ $sortField === 'registration_number' ? 'active' : '' }}" wire:click="sort('registration_number')">
                                Matrícula
                                <i class="fa-solid {{ $sortField === 'registration_number' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'registration_number' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th>
                            <button class="sort-btn {{ $sortField === 'model' ? 'active' : '' }}" wire:click="sort('model')">
                                Modelo
                                <i class="fa-solid {{ $sortField === 'model' && $sortDir === 'asc' ? 'fa-sort-up' : ($sortField === 'model' ? 'fa-sort-down' : 'fa-sort') }} sort-icon"></i>
                            </button>
                        </th>
                        <th>Aerolínea</th>
                        <th style="text-align:right;padding-right:20px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aircraft as $ac)
                    <tr wire:key="ac-{{ $ac->id }}">
                        <td>
                            <span style="font-family:monospace;font-weight:700;font-size:14px;letter-spacing:.04em;color:var(--accent);">
                                {{ $ac->registration_number }}
                            </span>
                        </td>
                        <td style="color:var(--text-secondary);">{{ $ac->model ?? '—' }}</td>
                        <td class="fw-semibold">{{ $ac->airline->name ?? '—' }}</td>
                        <td style="text-align:right;padding-right:16px;">
                            <div class="action-btn-group" style="justify-content:flex-end;">
                                <button wire:click="openEdit({{ $ac->id }})" class="action-btn action-btn-edit" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button wire:click="archive({{ $ac->id }})"
                                        onclick="return confirm('¿Archivar la aeronave {{ addslashes($ac->registration_number) }}?');"
                                        class="action-btn action-btn-delete" title="Archivar">
                                    <i class="fa-solid fa-archive"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:48px;color:var(--text-muted);">
                            <i class="fa-solid fa-jet-fighter" style="font-size:28px;display:block;margin-bottom:10px;opacity:.3;"></i>
                            No hay aeronaves registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $aircraft->links() }}
    </div>

    <!-- ── Modal ──────────────────────────────────────────── -->
    <div class="modal fade" id="aircraft-modal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-weight:700;font-size:15px;">
                        {{ $editingId ? 'Editar Aeronave' : 'Nueva Aeronave' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body" style="padding:24px;">
                        <div class="mb-3">
                            <label class="form-label">Matrícula <span style="color:#f43f5e;">*</span></label>
                            <input wire:model="registration_number" type="text"
                                   class="form-control @error('registration_number') is-invalid @enderror"
                                   placeholder="N12345" style="text-transform:uppercase;font-family:monospace;font-weight:700;letter-spacing:.05em;">
                            @error('registration_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modelo</label>
                            <input wire:model="model_name" type="text" class="form-control" placeholder="Boeing 737-800">
                        </div>
                        <div>
                            <label class="form-label">Aerolínea <span style="color:#f43f5e;">*</span></label>
                            <select wire:model="airline_id" class="form-select @error('airline_id') is-invalid @enderror">
                                <option value="">Seleccione una aerolínea</option>
                                @foreach($airlines as $airline)
                                    <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                                @endforeach
                            </select>
                            @error('airline_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
