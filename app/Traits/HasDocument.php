<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Document;

trait HasDocument
{
    public function document(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function hasDocument(): bool
    {
        return $this->document()->exists();
    }
}
