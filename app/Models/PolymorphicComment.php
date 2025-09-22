<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolymorphicComment extends Model
{
    protected $fillable = ['content', 'commentable_id', 'commentable_type'];

    /**
     * Obtener el modelo padre (Image, Video, etc.) que posee el comentario.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
