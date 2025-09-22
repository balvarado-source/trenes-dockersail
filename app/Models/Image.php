<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Image extends Model
{
    protected $fillable = ['title', 'url', 'description' , 'user_id'];

    /**
     * Obtener todos los comentarios polimórficos de la imagen.
     */
    public function polymorphicComments(): MorphMany
    {
        return $this->morphMany(PolymorphicComment::class, 'commentable');
    }
}
