<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\Aircraft;
use App\Models\DocumentType;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class FlightsEdit extends Component
{
    use WithFileUploads;

    public Flight $flight;

    // ── Core ───────────────────────────────────────────────────────────
    public string  $flight_number = '';
    public string  $flight_date   = '';
    public ?int    $airline_id    = null;
    public ?int    $aircraft_id   = null;

    // ── CCO Operational ────────────────────────────────────────────────
    public string  $tipo_servicio        = '';
    public string  $origen               = '';
    public string  $destino              = '';
    public string  $gate                 = '';
    public string  $eta                  = '';
    public string  $ata                  = '';
    public string  $std                  = '';
    public string  $block_in             = '';
    public string  $block_off            = '';
    public string  $start_offloading     = '';
    public string  $end_offloading       = '';
    public string  $start_loading        = '';
    public string  $end_loading          = '';
    public ?int    $pax_in               = null;
    public ?int    $pax_out              = null;
    public ?int    $bags_offloading      = null;
    public ?int    $bags_loading         = null;
    public ?int    $ulds_offloading      = null;
    public ?int    $ulds_loading         = null;
    public ?float  $kgs_inbound          = null;
    public ?float  $kgs_outbound         = null;
    public string  $delay_codes          = '';
    public string  $delay_responsibility = '';
    public string  $observaciones        = '';
    public ?int    $leader_id            = null;

    // ── Billing ────────────────────────────────────────────────────────
    public float   $vuelo_pagado = 0;
    public float   $fumigacion   = 0;

    // ── Documents ──────────────────────────────────────────────────────
    public array   $aircraftOptions = [];
    public array   $newFiles        = [];
    public array   $removedDocIds   = [];

    protected function rules(): array
    {
        return [
            'flight_number'       => 'required|string|max:50',
            'flight_date'         => 'required|date',
            'airline_id'          => 'required|exists:airlines,id',
            'aircraft_id'         => 'nullable|exists:aircraft,id',
            'tipo_servicio'       => 'nullable|string|max:100',
            'origen'              => 'nullable|string|max:3',
            'destino'             => 'nullable|string|max:3',
            'gate'                => 'nullable|string|max:20',
            'eta'                 => 'nullable|string',
            'ata'                 => 'nullable|string',
            'std'                 => 'nullable|string',
            'block_in'            => 'nullable|string',
            'block_off'           => 'nullable|string',
            'start_offloading'    => 'nullable|string',
            'end_offloading'      => 'nullable|string',
            'start_loading'       => 'nullable|string',
            'end_loading'         => 'nullable|string',
            'pax_in'              => 'nullable|integer|min:0',
            'pax_out'             => 'nullable|integer|min:0',
            'bags_offloading'     => 'nullable|integer|min:0',
            'bags_loading'        => 'nullable|integer|min:0',
            'ulds_offloading'     => 'nullable|integer|min:0',
            'ulds_loading'        => 'nullable|integer|min:0',
            'kgs_inbound'         => 'nullable|numeric|min:0',
            'kgs_outbound'        => 'nullable|numeric|min:0',
            'delay_codes'         => 'nullable|string|max:200',
            'delay_responsibility'=> 'nullable|string|max:20',
            'observaciones'       => 'nullable|string',
            'leader_id'           => 'nullable|exists:users,id',
            'vuelo_pagado'        => 'nullable|numeric|min:0',
            'fumigacion'          => 'nullable|numeric|min:0',
            'newFiles.*.file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'newFiles.*.doc_type_id' => 'nullable|exists:document_types,id',
        ];
    }

    public function mount(Flight $flight): void
    {
        // Approved/billed can be viewed but not CC-edited
        abort_unless(auth()->user()->canCreateFlight() || auth()->user()->canMarkAsBilled(), 403);

        $this->flight        = $flight;
        $this->flight_number = $flight->flight_number;
        $this->flight_date   = $flight->flight_date->format('Y-m-d');
        $this->airline_id    = $flight->airline_id;
        $this->aircraft_id   = $flight->aircraft_id;

        // CCO
        $this->tipo_servicio        = $flight->tipo_servicio ?? '';
        $this->origen               = $flight->origen ?? '';
        $this->destino              = $flight->destino ?? '';
        $this->gate                 = $flight->gate ?? '';
        $this->eta                  = $flight->eta ? $flight->eta->format('H:i') : '';
        $this->ata                  = $flight->ata ? $flight->ata->format('H:i') : '';
        $this->std                  = $flight->std ? $flight->std->format('H:i') : '';
        $this->block_in             = $flight->block_in ? $flight->block_in->format('H:i') : '';
        $this->block_off            = $flight->block_off ? $flight->block_off->format('H:i') : '';
        $this->start_offloading     = $flight->start_offloading ?? '';
        $this->end_offloading       = $flight->end_offloading ?? '';
        $this->start_loading        = $flight->start_loading ?? '';
        $this->end_loading          = $flight->end_loading ?? '';
        $this->pax_in               = $flight->pax_in;
        $this->pax_out              = $flight->pax_out;
        $this->bags_offloading      = $flight->bags_offloading;
        $this->bags_loading         = $flight->bags_loading;
        $this->ulds_offloading      = $flight->ulds_offloading;
        $this->ulds_loading         = $flight->ulds_loading;
        $this->kgs_inbound          = $flight->kgs_inbound;
        $this->kgs_outbound         = $flight->kgs_outbound;
        $this->delay_codes          = $flight->delay_codes ?? '';
        $this->delay_responsibility = $flight->delay_responsibility ?? '';
        $this->observaciones        = $flight->observaciones ?? '';
        $this->leader_id            = $flight->leader_id;
        // Billing
        $this->vuelo_pagado = (float)$flight->vuelo_pagado;
        $this->fumigacion   = (float)$flight->fumigacion;

        if ($this->airline_id) {
            $this->aircraftOptions = Aircraft::active()
                ->where('airline_id', $this->airline_id)
                ->orderBy('registration_number')
                ->pluck('registration_number', 'id')
                ->toArray();
        }
    }

    public function updatedAirlineId($value): void
    {
        $this->aircraft_id    = null;
        $this->aircraftOptions = [];
        if ($value) {
            $this->aircraftOptions = Aircraft::active()
                ->where('airline_id', $value)
                ->orderBy('registration_number')
                ->pluck('registration_number', 'id')
                ->toArray();
        }
    }

    public function addFileRow(): void { $this->newFiles[] = ['file' => null, 'doc_type_id' => '']; }

    public function removeFileRow(int $index): void
    {
        unset($this->newFiles[$index]);
        $this->newFiles = array_values($this->newFiles);
    }

    public function removeExistingDoc(int $docId): void
    {
        if (!in_array($docId, $this->removedDocIds)) {
            $this->removedDocIds[] = $docId;
        }
    }

    public function save()
    {
        $canEdit   = auth()->user()->canCreateFlight() && $this->flight->status === Flight::STATUS_PENDING;
        $canBilling = auth()->user()->canMarkAsBilled() || auth()->user()->canAdminVuelos();

        abort_unless($canEdit || $canBilling, 403);

        $this->validate();

        $date            = $this->flight_date;
        $timeToDatetime  = fn($t) => $t ? $date . ' ' . $t . ':00' : null;

        $updateData = [
            // Always updatable if permitted
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
            'vuelo_pagado'        => $this->vuelo_pagado ?? 0,
            'fumigacion'          => $this->fumigacion ?? 0,
        ];

        // Core fields only editable on pending
        if ($canEdit) {
            $updateData['flight_number'] = strtoupper(trim($this->flight_number));
            $updateData['flight_date']   = $date;
            $updateData['airline_id']    = $this->airline_id;
            $updateData['aircraft_id']   = $this->aircraft_id ?: null;
        }

        $this->flight->update($updateData);

        // Remove documents
        foreach ($this->removedDocIds as $docId) {
            $doc = Document::find($docId);
            if ($doc && $doc->flight_id === $this->flight->id) {
                $doc->delete();
            }
        }

        // New documents
        foreach ($this->newFiles as $fileRow) {
            if (!empty($fileRow['file'])) {
                $uploaded    = $fileRow['file'];
                $storagePath = 'documents/' . uniqid() . '_' . $uploaded->getClientOriginalName();
                Storage::disk('public')->put($storagePath, file_get_contents($uploaded->getRealPath()));
                Document::create([
                    'flight_id'     => $this->flight->id,
                    'doc_type_id'   => $fileRow['doc_type_id'] ?: null,
                    'file_path'     => $storagePath,
                    'original_name' => $uploaded->getClientOriginalName(),
                    'uploaded_by'   => auth()->id(),
                ]);
            }
        }

        session()->flash('message', "Vuelo {$this->flight->flight_number} actualizado.");
        return redirect()->route('flights.index');
    }

    public function render()
    {
        $airlines      = Airline::active()->orderBy('name')->get();
        $documentTypes = DocumentType::active()->orderBy('name')->get();
        $users         = User::where('is_archived', false)->orderBy('name')->get(['id', 'name']);
        $existingDocs  = $this->flight->documents()
            ->with('docType')
            ->whereNotIn('id', $this->removedDocIds)
            ->get();

        return view('livewire.flights-edit', [
            'airlines'      => $airlines,
            'documentTypes' => $documentTypes,
            'users'         => $users,
            'existingDocs'  => $existingDocs,
        ])->layout('layouts.app', ['title' => 'Editar Vuelo - ' . $this->flight->flight_number]);
    }
}
