<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\Aircraft;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;

class FlightsCreate extends Component
{
    use WithFileUploads;

    // ── Core ───────────────────────────────────────────────────────────
    public string  $flight_number = '';
    public string  $flight_date   = '';
    public ?int    $airline_id    = null;
    public ?int    $aircraft_id   = null;

    // ── CCO Operational ────────────────────────────────────────────────
    public string  $tipo_servicio = '';
    public string  $origen        = '';
    public string  $destino       = '';
    public string  $gate          = '';
    public string  $eta           = '';
    public string  $ata           = '';
    public string  $std           = '';
    public string  $block_in      = '';
    public string  $block_off     = '';
    public string  $start_offloading = '';
    public string  $end_offloading   = '';
    public string  $start_loading    = '';
    public string  $end_loading      = '';
    public ?int    $pax_in           = null;
    public ?int    $pax_out          = null;
    public ?int    $bags_offloading  = null;
    public ?int    $bags_loading     = null;
    public ?int    $ulds_offloading  = null;
    public ?int    $ulds_loading     = null;
    public ?float  $kgs_inbound      = null;
    public ?float  $kgs_outbound     = null;
    public string  $delay_codes      = '';
    public string  $delay_responsibility = '';
    public string  $observaciones    = '';
    public ?int    $leader_id        = null;

    // ── Billing (only billing_supervisor / admin) ──────────────────────
    public float   $vuelo_pagado = 0;
    public float   $fumigacion   = 0;

    // ── Documents ──────────────────────────────────────────────────────
    public array   $newFiles = [];

    // ── Dynamic aircraft options ───────────────────────────────────────
    public array   $aircraftOptions = [];

    protected function rules(): array
    {
        return [
            'flight_number'   => 'required|string|max:50',
            'flight_date'     => 'required|date',
            'airline_id'      => 'required|exists:airlines,id',
            'aircraft_id'     => 'nullable|exists:aircraft,id',
            'tipo_servicio'   => 'nullable|string|max:100',
            'origen'          => 'nullable|string|max:3',
            'destino'         => 'nullable|string|max:3',
            'gate'            => 'nullable|string|max:20',
            'eta'             => 'nullable|string',
            'ata'             => 'nullable|string',
            'std'             => 'nullable|string',
            'block_in'        => 'nullable|string',
            'block_off'       => 'nullable|string',
            'start_offloading'=> 'nullable|string',
            'end_offloading'  => 'nullable|string',
            'start_loading'   => 'nullable|string',
            'end_loading'     => 'nullable|string',
            'pax_in'          => 'nullable|integer|min:0',
            'pax_out'         => 'nullable|integer|min:0',
            'bags_offloading' => 'nullable|integer|min:0',
            'bags_loading'    => 'nullable|integer|min:0',
            'ulds_offloading' => 'nullable|integer|min:0',
            'ulds_loading'    => 'nullable|integer|min:0',
            'kgs_inbound'     => 'nullable|numeric|min:0',
            'kgs_outbound'    => 'nullable|numeric|min:0',
            'delay_codes'     => 'nullable|string|max:200',
            'delay_responsibility'=> 'nullable|string|max:20',
            'observaciones'   => 'nullable|string',
            'leader_id'       => 'nullable|exists:users,id',
            'vuelo_pagado'    => 'nullable|numeric|min:0',
            'fumigacion'      => 'nullable|numeric|min:0',
            'newFiles.*.file'         => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'newFiles.*.doc_type_id'  => 'nullable|exists:document_types,id',
        ];
    }

    public function updatedAirlineId($value): void
    {
        $this->aircraft_id     = null;
        $this->aircraftOptions = [];
        if ($value) {
            $this->aircraftOptions = Aircraft::active()
                ->where('airline_id', $value)
                ->orderBy('registration_number')
                ->pluck('registration_number', 'id')
                ->toArray();
        }
    }

    public function addFileRow(): void
    {
        $this->newFiles[] = ['file' => null, 'doc_type_id' => null];
    }

    public function removeFileRow(int $index): void
    {
        array_splice($this->newFiles, $index, 1);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->canCreateFlight(), 403);

        $this->validate();

        // Duplicate check
        if (Flight::where('flight_number', strtoupper($this->flight_number))
                  ->where('flight_date', $this->flight_date)
                  ->exists()) {
            $this->addError('flight_number', 'Ya existe un vuelo con este número en la fecha seleccionada.');
            return;
        }

        // Build date/time helpers
        $date = $this->flight_date;
        $timeToDatetime = fn($t) => $t ? $date . ' ' . $t . ':00' : null;

        $flight = Flight::create([
            'flight_number'       => strtoupper($this->flight_number),
            'flight_date'         => $date,
            'airline_id'          => $this->airline_id,
            'aircraft_id'         => $this->aircraft_id ?: null,
            'created_by'          => auth()->id(),
            'status'              => Flight::STATUS_PENDING,
            // CCO
            'tipo_servicio'       => $this->tipo_servicio ?: null,
            'origen'              => strtoupper($this->origen) ?: null,
            'destino'             => strtoupper($this->destino) ?: null,
            'gate'                => $this->gate ?: null,
            'eta'                 => $timeToDatetime($this->eta),
            'ata'                 => $timeToDatetime($this->ata),
            'std'                 => $timeToDatetime($this->std),
            'block_in'            => $timeToDatetime($this->block_in),
            'block_off'           => $timeToDatetime($this->block_off),
            'start_offloading'    => $this->start_offloading ?: null,
            'end_offloading'      => $this->end_offloading ?: null,
            'start_loading'       => $this->start_loading ?: null,
            'end_loading'         => $this->end_loading ?: null,
            'pax_in'              => $this->pax_in,
            'pax_out'             => $this->pax_out,
            'bags_offloading'     => $this->bags_offloading,
            'bags_loading'        => $this->bags_loading,
            'ulds_offloading'     => $this->ulds_offloading,
            'ulds_loading'        => $this->ulds_loading,
            'kgs_inbound'         => $this->kgs_inbound,
            'kgs_outbound'        => $this->kgs_outbound,
            'delay_codes'         => $this->delay_codes ?: null,
            'delay_responsibility'=> $this->delay_responsibility ?: null,
            'observaciones'       => $this->observaciones ?: null,
            'leader_id'           => $this->leader_id ?: null,
            // Billing
            'vuelo_pagado'        => $this->vuelo_pagado ?? 0,
            'fumigacion'          => $this->fumigacion ?? 0,
        ]);

        // Upload documents
        foreach ($this->newFiles as $fileRow) {
            if (!empty($fileRow['file'])) {
                $path = $fileRow['file']->store('documents', 'local');
                Document::create([
                    'flight_id'       => $flight->id,
                    'file_path'       => $path,
                    'original_name'   => $fileRow['file']->getClientOriginalName(),
                    'doc_type_id'     => $fileRow['doc_type_id'] ?: null,
                    'uploaded_by'     => auth()->id(),
                ]);
            }
        }

        session()->flash('message', "Vuelo {$flight->flight_number} creado correctamente.");
        $this->redirect(route('flights.index'));
    }

    public function render()
    {
        $airlines      = Airline::active()->orderBy('name')->get();
        $documentTypes = DocumentType::active()->orderBy('name')->get();
        $users         = User::where('is_archived', false)->orderBy('name')->get(['id', 'name']);

        return view('livewire.flights-create', compact('airlines', 'documentTypes', 'users'))
            ->layout('layouts.app', ['title' => 'Crear Vuelo']);
    }
}
