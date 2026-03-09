<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Aircraft;
use App\Models\Airline;

class AircraftIndex extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $sortField = 'registration_number';
    public string $sortDir   = 'asc';
    public int    $perPage   = 15;

    public string $registration_number = '';
    public string $model_name          = '';
    public ?int   $airline_id          = null;
    public ?int   $editingId           = null;

    protected function rules(): array
    {
        $unique = $this->editingId
            ? 'required|string|max:50|unique:aircraft,registration_number,' . $this->editingId
            : 'required|string|max:50|unique:aircraft,registration_number';
        return [
            'registration_number' => $unique,
            'model_name'          => 'nullable|string|max:100',
            'airline_id'          => 'required|exists:airlines,id',
        ];
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
        $this->reset(['registration_number', 'model_name', 'airline_id', 'editingId']);
        $this->dispatch('open-modal', 'aircraft-modal');
    }

    public function openEdit(int $id): void
    {
        $ac                        = Aircraft::findOrFail($id);
        $this->editingId           = $id;
        $this->registration_number = $ac->registration_number;
        $this->model_name          = $ac->model ?? '';
        $this->airline_id          = $ac->airline_id;
        $this->dispatch('open-modal', 'aircraft-modal');
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'registration_number' => strtoupper(trim($this->registration_number)),
            'model'               => $this->model_name ?: null,
            'airline_id'          => $this->airline_id,
        ];

        if ($this->editingId) {
            Aircraft::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Aeronave actualizada.');
        } else {
            Aircraft::create($data);
            session()->flash('message', 'Aeronave registrada.');
        }

        $this->reset(['registration_number', 'model_name', 'airline_id', 'editingId']);
        $this->dispatch('close-modal', 'aircraft-modal');
    }

    public function archive(int $id): void
    {
        Aircraft::findOrFail($id)->update(['is_archived' => true]);
        session()->flash('message', 'Aeronave archivada.');
    }

    public function render()
    {
        $aircraft = Aircraft::active()
            ->with('airline')
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('registration_number', 'like', '%' . $this->search . '%')
                   ->orWhere('model', 'like', '%' . $this->search . '%')
                   ->orWhereHas('airline', fn ($qa) => $qa->where('name', 'like', '%' . $this->search . '%'));
            }))
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        $airlines = Airline::active()->orderBy('name')->get();

        return view('livewire.aircraft-index', compact('aircraft', 'airlines'))
            ->layout('layouts.app', ['title' => 'Aeronaves']);
    }
}
