<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

//models
use App\Models\Post;
use App\Models\Review;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_posts()
    {
        $posts = Post::factory(10)->create()->each(function($post) {
            $review = Review::factory()->create([
                'post_id' => $post->id,
                'abusive' => 1,
                'description' => 'Abusive content'
            ]);

            $post->review = [
                'post_id' => $post->id,
                'abusive' => $review->abusive,
                'description' => $review->description
            ];
        });

        $response = $this->get('/api/posts');
        $response->assertStatus(200);
        $response->assertJsonCount(count($posts));
        $response->assertExactJson($posts->toArray());
    }

    public function test_get_single_post() {
        $post = Post::factory()->create();
        $review = Review::factory()->create([
            'post_id' => $post->id,
            'abusive' => 1,
            'description' => 'Abusive content'
        ]);

        $post->review = [
            'post_id' => $post->id,
            'abusive' => $review->abusive,
            'description' => $review->description
        ];

        $response = $this->get('/api/posts/' . $post->id);
        $response->assertStatus(200);
        $response->assertJson($post->toArray());
    }

    public function test_create_post() {
        $post = Post::factory()->make();

        $response = $this->post('/api/posts/create', [
            'title' => $post->title,
            'description' => $post->description,
            'image' => UploadedFile::fake()->image('avatar.jpg')
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Post created.'
        ]);

        sleep(5);

        $createdPost = Post::first();

        $this->assertDatabaseHas('posts', $createdPost->toArray());
        $this->assertDatabaseHas('reviews', [
            'post_id' => $createdPost->id
        ]);

        Storage::assertExists($createdPost->image);
        Storage::delete($createdPost->image);
    }

    public function test_update_single_post() {
        $post = Post::factory()->create();
        $review = Review::factory()->create([
            'post_id' => $post->id,
            'abusive' => 1,
            'description' => 'Abusive content'
        ]);

        $response = $this->post('/api/posts/update', [
            'post_id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image' => UploadedFile::fake()->image('avatar.jpg')
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Post updated.'
        ]);

        sleep(5);

        $updatedPost = Post::first();

        $this->assertDatabaseHas('posts', $updatedPost->toArray());
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'post_id' => $review->post_id
        ]);

        Storage::assertExists($updatedPost->image);
        Storage::delete($updatedPost->image);
    }

    public function test_delete_post() {
        $post = Post::factory()->create();
        Review::factory()->create([
            'post_id' => $post->id,
            'abusive' => 1,
            'description' => 'Abusive content'
        ]);

        $response = $this->delete('/api/posts/delete/' . $post->id);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Post deleted.'
        ]);

        sleep(5);

        $this->assertDatabaseMissing('posts', $post->toArray());
        $this->assertDatabaseMissing('reviews', [
            'post_id' => $post->id
        ]);

        Storage::assertMissing($post->image);
    }
}
