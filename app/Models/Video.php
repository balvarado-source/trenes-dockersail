<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Video extends Model
{
    protected $fillable = ['title', 'url', 'duration', 'description'];

    /**
     * Obtener todos los comentarios polimórficos del video.
     */
    public function polymorphicComments(): MorphMany
    {
        return $this->morphMany(PolymorphicComment::class, 'commentable');
    }
}
