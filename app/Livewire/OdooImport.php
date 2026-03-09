<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\Aircraft;
use Carbon\Carbon;

class OdooImport extends Component
{
    use WithFileUploads;

    public $file              = null;
    public array $preview     = [];      // parsed rows ready for review
    public array $conflicts   = [];      // rows with unresolved entities
    public array $selected    = [];      // booleans: which rows to import
    public bool  $parsed      = false;
    public bool  $importing   = false;
    public array $results     = [];      // import results
    public string $errorMsg   = '';

    // ── Column map for Odoo export ──────────────────────────────────────
    // Col 0: Número de Reporte  Col 1: Escoja el Vuelo  Col 2: Línea Aérea
    // Col 3: Tipo de Aeronave   Col 4: ETA              Col 5: Tipo de Servicio
    // Col 6: Matrícula          Col 7: (dup)            Col 8: Estado Reporte
    // Col 9: Estado Vuelo

    // ── Step 1: Upload & Parse ──────────────────────────────────────────

    public function analyzeFile(): void
    {
        $this->preview   = [];
        $this->conflicts = [];
        $this->selected  = [];
        $this->results   = [];
        $this->parsed    = false;
        $this->errorMsg  = '';

        // Basic presence check
        if (!$this->file) {
            $this->errorMsg = 'Por favor selecciona un archivo primero.';
            return;
        }

        $this->validate([
            'file' => 'required|file|max:20480',   // max 20 MB, skip mimes check (temp files have no extension)
        ]);

        // ── Store in a known location so we get a real filesystem path ──
        try {
            $storedPath  = $this->file->storeAs('imports-xlsx', uniqid('odoo_') . '.xlsx', 'local');
            $fullPath    = \Illuminate\Support\Facades\Storage::disk('local')->path($storedPath);
        } catch (\Throwable $e) {
            $this->errorMsg = 'No se pudo guardar el archivo temporalmente: ' . $e->getMessage();
            return;
        }

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);

            \Illuminate\Support\Facades\Storage::disk('local')->delete($storedPath);  // cleanup


            if (count($rows) < 2) {
                $this->errorMsg = 'El archivo no contiene filas de datos (solo tiene encabezado o está vacío).';
                return;
            }

            // ── Detect columns by header name ─────────────────────────────
            $headerRow = array_map(fn($h) => mb_strtolower(trim((string)$h)), $rows[0] ?? []);

            // Normalize: remove accents and special chars for comparison
            $normalize = fn(string $s): string => str_replace('-', ' ', \Illuminate\Support\Str::slug($s));
            $normHeaders = array_map($normalize, $rows[0] ?? []);

            // Known column aliases (normalized) → field name
            $colAliases = [
                'numero de reporte' => 'odoo_ref',
                'n reporte'         => 'odoo_ref',
                'reporte'           => 'odoo_ref',
                'escoja el vuelo'   => 'vuelo',
                'vuelo'             => 'vuelo',
                'linea aerea'       => 'airline',
                'aerolinea'         => 'airline',
                'tipo de aeronave'  => 'aircraft_type',
                'tipo aeronave'     => 'aircraft_type',
                'eta'               => 'eta',
                'tipo de servicio'  => 'tipo_servicio',
                'tipo servicio'     => 'tipo_servicio',
                'matricula'         => 'matricula',
                'matricula aeronave'=> 'matricula',
                'estado del reporte'=> 'estado',
                'estado reporte'    => 'estado',
                'estado'            => 'estado',
                'estado vuelo'      => 'estado_vuelo',
            ];

            // Build colMap: field_name => column_index
            $colMap = [];
            foreach ($normHeaders as $i => $h) {
                // Si la columna ya la mapié con un alias más exacto antes, no la sobreescribo
                // Pero iteramos por alias en orden.
                foreach ($colAliases as $alias => $field) {
                    if ($h === $normalize($alias)) {
                        // Solo asignar si no hemos asignado un campo para este índice
                        // y si el campo no ha sido asignado. O sobrescribimos si es exacto.
                        if (!isset($colMap[$field])) {
                            $colMap[$field] = $i;
                        }
                        break;
                    }
                }
            }

            // Required fields check
            $required = ['vuelo', 'airline'];
            $missing  = [];
            foreach ($required as $req) {
                if (!isset($colMap[$req])) $missing[] = $req;
            }

            if (!empty($missing)) {
                // Show diagnostic: what we found vs what's needed
                $foundHeaders   = implode(' | ', array_map(fn($h) => '"'.$h.'"', $rows[0] ?? []));
                $missingLabels  = ['vuelo' => 'Escoja el Vuelo / Vuelo', 'airline' => 'Línea Aérea / Aerolínea'];
                $missingReadable = implode(', ', array_map(fn($m) => $missingLabels[$m] ?? $m, $missing));
                $this->errorMsg =
                    "El archivo no tiene las columnas requeridas. "
                    . "Faltan: <strong>{$missingReadable}</strong>.<br><br>"
                    . "Columnas encontradas en la fila 1: {$foundHeaders}<br><br>"
                    . "Asegúrate de exportar el archivo directamente desde Odoo sin modificar las columnas.";
                return;
            }

            // Fallback to index if header not found (for optional fields)
            $col = fn(array $row, string $field, int $fallback): mixed
                => $row[$colMap[$field] ?? $fallback] ?? null;

            // ── Build preview rows ─────────────────────────────────────────
            foreach (array_slice($rows, 1) as $idx => $row) {
                if (empty(array_filter($row))) continue;

                $odooRef      = trim((string)$col($row, 'odoo_ref',     0));
                $vuelo        = trim((string)$col($row, 'vuelo',        1));
                $lineaAerea   = trim((string)$col($row, 'airline',      2));
                $tipoAeronave = trim((string)$col($row, 'aircraft_type',3));
                $etaRaw       =              $col($row, 'eta',          4);
                $tipoServicio = trim((string)$col($row, 'tipo_servicio',5));
                $matricula    = strtoupper(trim(str_replace('-', '', (string)$col($row, 'matricula', 6))));
                $estadoRep    = trim((string)$col($row, 'estado',       8));

                // Parse flight number from e.g. "Avianca AV040-041 BOG 05-03-2026"
                $flightNumber = null;
                $flightDate   = null;
                if ($vuelo) {
                    $vueloText = $vuelo;
                    // Extraer fecha si existe en el string (ej: 05/03/2026 o 05-03-2026)
                    if (preg_match('/(\d{2}[-\/]\d{2}[-\/]\d{4})/', $vueloText, $m)) {
                        try {
                            $flightDate = Carbon::createFromFormat(
                                str_contains($m[1], '-') ? 'd-m-Y' : 'd/m/Y',
                                $m[1]
                            )->format('Y-m-d');
                        } catch (\Exception $e) {}
                        $vueloText = str_replace($m[1], '', $vueloText);
                    }
                    
                    // Extraer el número de vuelo del texto restante
                    // Intenta formato tipo aerolínea (AV 040, CM123, etc)
                    if (preg_match('/\b([A-Z]{1,3}\s*\d+[-\d]*)\b/i', $vueloText, $m2)) {
                        $flightNumber = strtoupper(str_replace(' ', '', $m2[1]));
                    } 
                    // Si no, captura la primera palabra alfanumérica (por si solo pusieron el número como 1234)
                    elseif (preg_match('/\b([A-Z0-9]+)\b/i', $vueloText, $m3)) {
                        $flightNumber = strtoupper($m3[1]);
                    } 
                    // Fallback directo a todo el string resultante
                    else {
                        $flightNumber = strtoupper(trim(preg_replace('/[^A-Za-z0-9\- ]/', '', $vueloText)));
                    }
                }

                // ETA
                $eta = null;
                if ($etaRaw) {
                    try {
                        $eta = Carbon::parse($etaRaw)->format('Y-m-d H:i:s');
                        if (!$flightDate) {
                            $flightDate = Carbon::parse($etaRaw)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {}
                }

                // Check duplicate
                $exists = false;
                if ($odooRef) {
                    $exists = Flight::where('odoo_ref', $odooRef)->exists();
                }
                if (!$exists && $flightNumber && $flightDate) {
                    $exists = Flight::where('flight_number', $flightNumber)
                        ->where('flight_date', $flightDate)->exists();
                }

                // Resolve airline
                $airlineModel  = null;
                $airlineStatus = 'ok';
                if ($lineaAerea) {
                    $airlineModel = Airline::active()
                        ->where('name', 'like', '%' . $lineaAerea . '%')
                        ->first();
                    if (!$airlineModel) $airlineStatus = 'new';
                }

                // Resolve aircraft
                $aircraftModel  = null;
                $aircraftStatus = 'ok';
                if ($matricula) {
                    $aircraftModel = Aircraft::active()
                        ->where('registration_number', 'like', '%' . $matricula . '%')
                        ->first();
                    if (!$aircraftModel) $aircraftStatus = 'new';
                }

                $this->preview[]            = [
                    'idx'             => $idx,
                    'odoo_ref'        => $odooRef,
                    'vuelo_raw'       => $vuelo,
                    'flight_number'   => $flightNumber,
                    'flight_date'     => $flightDate,
                    'airline_name'    => $lineaAerea,
                    'airline_id'      => $airlineModel?->id,
                    'airline_status'  => $airlineStatus,
                    'tipo_aeronave'   => $tipoAeronave,
                    'matricula'       => $matricula,
                    'aircraft_id'     => $aircraftModel?->id,
                    'aircraft_status' => $aircraftStatus,
                    'tipo_servicio'   => $tipoServicio,
                    'eta'             => $eta,
                    'estado_odoo'     => $estadoRep,
                    'already_exists'  => $exists,
                    'import'          => !$exists,
                ];
                $this->selected[$idx] = !$exists;
            }

            $this->parsed = true;

        } catch (\Throwable $e) {
            // Cleanup on failure
            \Illuminate\Support\Facades\Storage::disk('local')->delete($storedPath);

            $msg = $e->getMessage();
            if (str_contains($msg, 'ZipArchive') || str_contains($msg, 'zip')) {
                $this->errorMsg = 'Error de extensión ZIP: '
                    . 'Reinicia Apache desde el Panel de Control de XAMPP. '
                    . 'Detalle: ' . $msg;
            } else {
                $this->errorMsg = 'Error al procesar el archivo: ' . $msg;
            }
        }
    }

    // ── Step 2: Execute Import ──────────────────────────────────────────

    public function importFlights(): void
    {
        abort_unless(auth()->user()->canMarkAsBilled() || auth()->user()->canAdminVuelos(), 403);

        $this->results  = [];
        $imported       = 0;
        $skipped        = 0;
        $created_airlines  = 0;
        $created_aircraft  = 0;

        foreach ($this->preview as $row) {
            $idx = $row['idx'];
            if (empty($this->selected[$idx])) {
                $skipped++;
                continue;
            }

            if ($row['already_exists']) {
                $skipped++;
                continue;
            }

            if (!$row['flight_number'] || !$row['flight_date']) {
                $this->results[] = ['status' => 'error', 'msg' => "Fila {$idx}: no se pudo extraer número/fecha de vuelo de \"{$row['vuelo_raw']}\""];
                continue;
            }

            try {
                // Ensure airline exists
                $airlineId = $row['airline_id'];
                if (!$airlineId && $row['airline_name']) {
                    $airline   = Airline::firstOrCreate(['name' => strtoupper($row['airline_name'])]);
                    $airlineId = $airline->id;
                    $created_airlines++;
                }

                // Ensure aircraft exists
                $aircraftId = $row['aircraft_id'];
                if (!$aircraftId && $row['matricula']) {
                    $aircraft = Aircraft::firstOrCreate(
                        ['registration_number' => $row['matricula']],
                        [
                            'model'      => $row['tipo_aeronave'] ?: null,
                            'airline_id' => $airlineId,
                        ]
                    );
                    $aircraftId = $aircraft->id;
                    $created_aircraft++;
                }

                Flight::create([
                    'odoo_ref'      => $row['odoo_ref'],
                    'flight_number' => strtoupper($row['flight_number']),
                    'flight_date'   => $row['flight_date'],
                    'airline_id'    => $airlineId,
                    'aircraft_id'   => $aircraftId,
                    'tipo_servicio' => $row['tipo_servicio'],
                    'eta'           => $row['eta'],
                    'status'        => Flight::STATUS_PENDING,
                    'created_by'    => auth()->id(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $this->results[] = ['status' => 'error', 'msg' => "Vuelo {$row['flight_number']}: " . $e->getMessage()];
            }
        }

        $this->results[] = [
            'status' => 'success',
            'msg'    => "Importación completada: {$imported} vuelos importados, {$skipped} omitidos."
                      . ($created_airlines ? " Se crearon {$created_airlines} aerolínea(s) nuevas." : '')
                      . ($created_aircraft ? " Se crearon {$created_aircraft} aeronave(s) nuevas." : ''),
        ];

        $this->preview  = [];
        $this->parsed   = false;
        $this->file     = null;
        $this->selected = [];
    }

    public function render()
    {
        return view('livewire.odoo-import')
            ->layout('layouts.app', ['title' => 'Importar desde Odoo']);
    }
}
