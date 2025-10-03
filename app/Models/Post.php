<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * @OA\Schema(
 *     schema="Post",
 *     description="Modelo de Post",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="title", type="string", example="Post 1"),
 *     @OA\Property(property="content", type="string", example="Contenido del Post"),
 *     @OA\Property(property="created_at", type="string", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updated_at", type="string", example="2021-01-01 00:00:00")
 * )
 */

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
