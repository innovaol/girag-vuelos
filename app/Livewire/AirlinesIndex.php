<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Airline;

class AirlinesIndex extends Component
{
    use WithPagination;

    // Table
    public string $search    = '';
    public string $sortField = 'name';
    public string $sortDir   = 'asc';
    public int    $perPage   = 15;

    // Form (modal)
    public string $name      = '';
    public string $sage_code = '';
    public ?int   $editingId = null;

    protected function rules(): array
    {
        $unique = $this->editingId
            ? 'required|string|max:100|unique:airlines,name,' . $this->editingId
            : 'required|string|max:100|unique:airlines,name';
        return [
            'name'      => $unique,
            'sage_code' => 'nullable|string|max:50',
        ];
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'sage_code', 'editingId']);
        $this->dispatch('open-modal', 'airline-modal');
    }

    public function openEdit(int $id): void
    {
        $airline         = Airline::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $airline->name;
        $this->sage_code = $airline->sage_code ?? '';
        $this->dispatch('open-modal', 'airline-modal');
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => strtoupper(trim($this->name)), 'sage_code' => $this->sage_code ?: null];

        if ($this->editingId) {
            Airline::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Aerolínea actualizada correctamente.');
        } else {
            Airline::create($data);
            session()->flash('message', 'Aerolínea registrada correctamente.');
        }

        $this->reset(['name', 'sage_code', 'editingId']);
        $this->dispatch('close-modal', 'airline-modal');
    }

    public function archive(int $id): void
    {
        Airline::findOrFail($id)->update(['is_archived' => true]);
        session()->flash('message', 'Aerolínea archivada.');
    }

    public function render()
    {
        $airlines = Airline::active()
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('sage_code', 'like', '%' . $this->search . '%');
            }))
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.airlines-index', compact('airlines'))
            ->layout('layouts.app', ['title' => 'Aerolíneas']);
    }
}
