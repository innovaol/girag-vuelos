<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Flight;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FlightsIndex extends Component
{
    use WithPagination;

    // ─── Table state ──────────────────────────────────────────
    public string $search      = '';
    public string $sortField   = 'flight_date';
    public string $sortDir     = 'desc';
    public int    $perPage     = 20;
    public string $statusFilter = '';   // '', 'pending', 'approved', 'billed'

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

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

    // ─── Actions ──────────────────────────────────────────────

    public function approve(int $id): void
    {
        abort_unless(auth()->user()->canApproveFlight(), 403);
        $flight = Flight::findOrFail($id);
        abort_if($flight->status !== Flight::STATUS_PENDING, 403);
        $flight->update([
            'status'      => Flight::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        session()->flash('message', "Vuelo {$flight->flight_number} aprobado.");
    }

    public function markAsBilled(int $id): void
    {
        abort_unless(auth()->user()->canMarkAsBilled(), 403);
        $flight = Flight::findOrFail($id);
        abort_if($flight->status !== Flight::STATUS_APPROVED, 403);
        $flight->update([
            'status'          => Flight::STATUS_BILLED,
            'billing_user_id' => auth()->id(),
            'billed_at'       => now(),
        ]);
        session()->flash('message', "Vuelo {$flight->flight_number} marcado como facturado.");
    }

    public function revertToPending(int $id): void
    {
        abort_unless(auth()->user()->canAdminVuelos(), 403);
        $flight = Flight::findOrFail($id);
        if ($flight->status === Flight::STATUS_PENDING) return;
        $flight->update([
            'status'          => Flight::STATUS_PENDING,
            'approved_by'     => null,
            'approved_at'     => null,
            'billing_user_id' => null,
            'billed_at'       => null,
        ]);
        session()->flash('message', "Vuelo {$flight->flight_number} revertido a Pendiente.");
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->canCreateFlight(), 403);
        $flight = Flight::findOrFail($id);
        abort_if($flight->status !== Flight::STATUS_PENDING, 403);
        $number = $flight->flight_number;
        $flight->documents()->each(fn ($d) => $d->delete());
        $flight->delete();
        session()->flash('message', "Vuelo {$number} eliminado.");
    }

    public function exportSage(): StreamedResponse
    {
        abort_unless(auth()->user()->canMarkAsBilled(), 403);

        $flights = Flight::with(['airline'])
            ->where('status', Flight::STATUS_BILLED)
            ->whereNull('sage_exported_at')
            ->orderBy('flight_date')
            ->get();

        $filename = 'sage_export_' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($flights) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Customer ID', 'Invoice #', 'Invoice Date',
                'Item ID', 'Description', 'Quantity', 'Unit Price',
                'Amount', 'Tax ID',
            ]);

            foreach ($flights as $flight) {
                $customerId  = $flight->airline->sage_code ?? $flight->airline->name;
                $invoiceNum  = 'FLT-' . str_pad($flight->id, 5, '0', STR_PAD_LEFT);
                $date        = $flight->flight_date->format('m/d/Y');   // Sage 50 expects MM/DD/YYYY
                $airlineName = $flight->airline->name ?? '';
                $tipoLabel   = $flight->tipo_servicio ? " ({$flight->tipo_servicio})" : '';
                $itemId      = $flight->isCarguero() ? 'SVC-CARGO' : 'SVC-RAMPA';
                $description = "Vuelo {$flight->flight_number}{$tipoLabel} - {$airlineName}";
                $vueloPagado = (float)$flight->vuelo_pagado;
                $fumigacion  = (float)$flight->fumigacion;

                // Main service line
                fputcsv($handle, [
                    $customerId, $invoiceNum, $date,
                    $itemId, $description,
                    1, number_format($vueloPagado, 2, '.', ''),
                    number_format($vueloPagado, 2, '.', ''),
                    'EXENTO',
                ]);

                // Fumigation as separate line if applicable
                if ($fumigacion > 0) {
                    fputcsv($handle, [
                        $customerId, $invoiceNum, $date,
                        'SVC-FUMIG', "Fumigación - Vuelo {$flight->flight_number}",
                        1, number_format($fumigacion, 2, '.', ''),
                        number_format($fumigacion, 2, '.', ''),
                        'EXENTO',
                    ]);
                }

                $flight->update(['sage_exported_at' => now()->toDateTimeString()]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── Render ───────────────────────────────────────────────

    public function render()
    {
        $allowedSorts = ['flight_number', 'flight_date', 'status'];
        $sortField = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'flight_date';

        $flights = Flight::with(['airline', 'aircraft', 'creator'])
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('flight_number', 'like', '%' . $this->search . '%')
                   ->orWhereHas('airline', fn ($qa) => $qa->where('name', 'like', '%' . $this->search . '%'))
                   ->orWhereHas('aircraft', fn ($qa) => $qa->where('registration_number', 'like', '%' . $this->search . '%'));
            }))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy($sortField, $this->sortDir)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $pendingCount  = Flight::where('status', Flight::STATUS_PENDING)->count();
        $approvedCount = Flight::where('status', Flight::STATUS_APPROVED)->count();
        $billedCount   = Flight::where('status', Flight::STATUS_BILLED)->count();
        $sageCount     = Flight::where('status', Flight::STATUS_BILLED)->whereNull('sage_exported_at')->count();

        return view('livewire.flights-index', [
            'flights'       => $flights,
            'pendingCount'  => $pendingCount,
            'approvedCount' => $approvedCount,
            'billedCount'   => $billedCount,
            'sageCount'     => $sageCount,
        ])->layout('layouts.app', ['title' => 'Gestión de Vuelos']);
    }
}
