<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentView extends Component
{
    public function mount(Document $document)
    {
        abort_unless(auth()->user()->canCreateFlight(), 403);

        $path = Storage::disk('public')->path($document->file_path);

        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        $name = $document->original_name ?? basename($document->file_path);

        // Download flag
        $download = request()->query('download', false);

        if ($download) {
            return response()->download($path, $name);
        }

        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $name . '"',
        ]);
    }

    public function render()
    {
        // This component is only used for the mount redirect, no view needed
        return response('', 200);
    }
}
