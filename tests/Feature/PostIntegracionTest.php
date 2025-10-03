<?php

namespace Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PostIntegracionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
    }

    public function test_it_has_one_comment() {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();

        $this->assertInstanceOf(Comment::class, $post->comments->first());
        $this->assertEquals($comment->id, $post->comments()->first()->id);
    }
}
