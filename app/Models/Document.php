<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected $fillable = ['flight_id', 'doc_type_id', 'file_path', 'original_name'];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Flight, Document> */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<DocumentType, Document> */
    public function docType()
    {
        return $this->belongsTo(DocumentType::class, 'doc_type_id');
    }

    /**
     * Delete the physical file when deleting the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (Document $doc) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
        });
    }

    /**
     * Generate a unique filename for storage.
     * Format: documents/{flight_number}-{YYYYMMDD}_{HHMMSS}_{unique}.{ext}
     */
    public static function generateFilename(string $flightNumber, string $originalName): string
    {
        $slug = preg_replace('/[^a-z0-9]/i', '-', $flightNumber);
        $date = now()->format('Ymd');
        $time = now()->format('His');
        $unique = substr(uniqid(), -8);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        return "documents/{$slug}-{$date}_{$time}_{$unique}.{$ext}";
    }
}
