<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthSanctumRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un usuario para autenticar
        $this->user = User::factory()->create();

        // Autenticar con Sanctum
        Sanctum::actingAs($this->user, ['*']);
    }

    public function test_can_list_posts()
    {
        Post::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_post()
    {
        $data = [
            'title' => 'Mi primer post',
            'content' => 'Contenido del post'
        ];

        $response = $this->postJson('/api/posts', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('posts', $data);
    }
}
