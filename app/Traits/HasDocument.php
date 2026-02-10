<?php

namespace App\Traits;

use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
