<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_relation_post() {
        User::factory()->create();
        Post::factory()->create();
        $comment = Comment::factory()->create();

        $this->assertInstanceOf(BelongsTo::class, $comment->post());
    }
}
