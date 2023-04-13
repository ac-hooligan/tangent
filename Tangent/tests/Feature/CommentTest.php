<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CommentTest extends TestCase
{
    private function authorize()
    {
        $user = new User(array('name' => 'John Doe 2', 'email' => 'doe@example.com', 'password' => 'admin123456'));
        $this->be($user);
    }
    /**
     * A basic feature test example.
     */
    public function testRequiredFieldsForComments()
    {
        $this->withoutExceptionHandling();
        $this->authorize();

        $response = $this->json('POST', 'api/comments', ['Accept' => 'application/json']);
        $response->assertJson([
            "success" => false,
            "message" => [
                "title" => ["The title field is required."],
                "post_id" => ["The post id field is required."],
                "description" => ["The description field is required."],
            ]
        ]);
    }

    public function testSuccessfulCommentPost()
    {
        $this->withoutExceptionHandling();
        $this->authorize();
        Auth::user();

        $userData = Comment::factory()->create();

        $response = $this->json('POST', 'api/comments', $userData->toArray(), ['Accept' => 'application/json']);
        $response->assertJsonStructure([
            "success",
            "data" => [
                "id",
                "title",
                "description",
                "rating",
                "created_at",
                "updated_at"
            ],
            "message"
        ]);
    }
}
