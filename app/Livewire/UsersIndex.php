<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersIndex extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $sortField = 'name';
    public string $sortDir   = 'asc';
    public int    $perPage   = 15;

    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public bool $is_flight_supervisor  = false;
    public bool $is_billing_supervisor = false;
    public bool $is_admin_vuelos       = false;
    public ?int $editingId  = null;

    protected function rules(): array
    {
        $emailRule = $this->editingId
            ? 'required|email|unique:users,email,' . $this->editingId
            : 'required|email|unique:users,email';
        return [
            'name'                   => 'required|string|max:100',
            'email'                  => $emailRule,
            'password'               => $this->editingId ? 'nullable|string|min:8' : 'required|string|min:8',
            'is_flight_supervisor'   => 'boolean',
            'is_billing_supervisor'  => 'boolean',
            'is_admin_vuelos'        => 'boolean',
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
        $this->reset(['name', 'email', 'password', 'is_flight_supervisor', 'is_billing_supervisor', 'is_admin_vuelos', 'editingId']);
        $this->dispatch('open-modal', 'user-modal');
    }

    public function openEdit(int $id): void
    {
        $user                        = User::findOrFail($id);
        $this->editingId             = $id;
        $this->name                  = $user->name;
        $this->email                 = $user->email;
        $this->password              = '';
        $this->is_flight_supervisor  = (bool) $user->is_flight_supervisor;
        $this->is_billing_supervisor = (bool) $user->is_billing_supervisor;
        $this->is_admin_vuelos       = (bool) $user->is_admin_vuelos;
        $this->dispatch('open-modal', 'user-modal');
    }

    public function save(): void
    {
        $this->validate();
        $data = [
            'name'                  => $this->name,
            'email'                 => $this->email,
            'is_flight_supervisor'  => $this->is_flight_supervisor,
            'is_billing_supervisor' => $this->is_billing_supervisor,
            'is_admin_vuelos'       => $this->is_admin_vuelos,
        ];
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Usuario actualizado.');
        } else {
            User::create($data);
            session()->flash('message', 'Usuario creado.');
        }

        $this->reset(['name', 'email', 'password', 'is_flight_supervisor', 'is_billing_supervisor', 'is_admin_vuelos', 'editingId']);
        $this->dispatch('close-modal', 'user-modal');
    }

    public function archive(int $id): void
    {
        User::findOrFail($id)->update(['is_archived' => true]);
        session()->flash('message', 'Usuario archivado.');
    }

    public function render()
    {
        $users = User::where('is_archived', false)
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.users-index', compact('users'))
            ->layout('layouts.app', ['title' => 'Usuarios']);
    }
}
