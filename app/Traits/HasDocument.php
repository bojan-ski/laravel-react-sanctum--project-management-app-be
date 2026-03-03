<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Document;

trait HasDocument
{
    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function hasDocument(): bool
    {
        return $this->document()->exists();
    }
}
