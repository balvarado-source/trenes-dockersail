<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_relation_comments() {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->assertInstanceOf(HasMany::class, $post->comments());
    }

    public function test_it_does_not_has_comments() {
        User::factory()->create();
        $post = Post::factory()->create();

        $this->assertTrue($post->comments->isEmpty());
    }
}
