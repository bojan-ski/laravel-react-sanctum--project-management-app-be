<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';
    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'uploaded_by',
        'doc_name',
        'doc_path',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFullDocumentUrlAttribute(): string
    {
        return Storage::url($this->doc_path);
    }

    public function getFullDocumentPathAttribute(): string
    {
        return Storage::disk('public')->path($this->doc_path);
    }
}
