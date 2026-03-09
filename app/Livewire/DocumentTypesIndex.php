<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DocumentType;

class DocumentTypesIndex extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $sortField = 'name';
    public string $sortDir   = 'asc';
    public int    $perPage   = 15;

    public string $name    = '';
    public ?int $editingId = null;

    protected function rules(): array
    {
        return ['name' => 'required|string|max:255'];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function sort(string $field): void
    {
        $this->sortField === $field
            ? $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc'
            : [$this->sortField = $field, $this->sortDir = 'asc'];
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'editingId']);
        $this->dispatch('open-modal', 'doctype-modal');
    }

    public function openEdit(int $id): void
    {
        $dt            = DocumentType::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $dt->name;
        $this->dispatch('open-modal', 'doctype-modal');
    }

    public function save(): void
    {
        $this->validate();
        if ($this->editingId) {
            DocumentType::findOrFail($this->editingId)->update(['name' => $this->name]);
            session()->flash('message', 'Tipo de documento actualizado.');
        } else {
            DocumentType::create(['name' => $this->name]);
            session()->flash('message', 'Tipo de documento creado.');
        }
        $this->reset(['name', 'editingId']);
        $this->dispatch('close-modal', 'doctype-modal');
    }

    public function archive(int $id): void
    {
        DocumentType::findOrFail($id)->update(['is_archived' => true]);
        session()->flash('message', 'Tipo de documento archivado.');
    }

    public function render()
    {
        $docTypes = DocumentType::active()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.document-types-index', compact('docTypes'))
            ->layout('layouts.app', ['title' => 'Tipos de Documento']);
    }
}
