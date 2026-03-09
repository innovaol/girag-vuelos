<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DocumentType extends Model
{
    protected $fillable = ['name', 'is_archived'];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<Document> */
    public function documents()
    {
        return $this->hasMany(Document::class, 'doc_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }
}
