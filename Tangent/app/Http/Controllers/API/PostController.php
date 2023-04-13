<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Post;
use App\Http\Resources\Post as PostResource;

class PostController extends BaseController
{
    /**
     *  @OA\Get(
     *      path="/posts",
     *      operationId="getPosts",
     *      tags={"Posts"},
     *      security={{"bearerAuth":{}}},
     *      summary="Get all posts",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="number"),
     *                      @OA\Property(property="name", type="string"),
     *                      @OA\Property(property="created_at", type="string"),
     *                      @OA\Property(property="updated_at", type="string")
     *                   )
     *              ),
     *              @OA\Property(property="message", type="string", example="Categories fetched"),
     *          )
     *      )
     *  )
     */
    public function index()
    {
        $posts = Post::all();
        return $this->sendResponse(PostResource::collection($posts), 'Posts fetched.');
    }

    /**
     *  @OA\Post(
     *      path="/posts",
     *      operationId="postPost",
     *      tags={"Posts"},
     *      security={{"bearerAuth":{}}},
     *      summary="Create new post",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass category details",
     *          @OA\JsonContent(
     *              required={"title","description", "category_id"},
     *              @OA\Property(property="title", type="string", example="Test post"),
     *              @OA\Property(property="description", type="string", example="This is a food post"),
     *              @OA\Property(property="category_id", type="number", example=1),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="number", example=1),
     *                  @OA\Property(property="title", type="string", example="Test post"),
     *                  @OA\Property(property="description", type="string", example="This is a food post"),
     *                  @OA\Property(property="category_id", type="number", example=1),
     *                  @OA\Property(property="created_at", type="string", example="04/12/2023"),
     *                  @OA\Property(property="updated_at", type="string", example="04/12/2023")
     *              ),
     *              @OA\Property(property="message", type="string", example="Post created"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="object",
     *                  @OA\Property(property="title", type="array",
     *                      @OA\Items(type="string", example="The title has already been taken."),
     *                  )
     *               )
     *          )
     *      )
     *  )
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => ['required', 'unique:posts,title'],
            'category_id' => ['required', 'numeric'],
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $post = Category::find($input['category_id']);
        if (is_null($post)) {
            return $this->sendError('Category does not exist');
        }
        $authUser = Auth::user();
        $input['user_id'] = $authUser->id;

        $post = Post::create($input);
        return $this->sendResponse(new PostResource($post), 'Post created');
    }

    /**
     *  @OA\Get(
     *      path="/posts/{id}",
     *      operationId="getSinglePost",
     *      tags={"Posts"},
     *      security={{"bearerAuth":{}}},
     *      summary="Retrieve post",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="number", example=1),
     *                  @OA\Property(property="title", type="string", example="Food"),
     *                  @OA\Property(property="description", type="string", example="Lorem Ipsum is simply"),
     *                  @OA\Property(property="category_id", type="number", example=1),
     *                  @OA\Property(property="created_at", type="string", example="04/12/2023"),
     *                  @OA\Property(property="updated_at", type="string", example="04/12/2023")
     *              ),
     *              @OA\Property(property="message", type="string", example="Post fetched"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Post does not exist")
     *          )
     *      ),
     *  )
     */
    public function show($id)
    {
        $post = Post::find($id);
        if (is_null($post)) {
            return $this->sendError('Post does not exist');
        }
        return $this->sendResponse(new PostResource($post), 'Post fetched');
    }

    /**
     *  @OA\Put(
     *      path="/posts/{id}",
     *      operationId="putPost",
     *      tags={"Posts"},
     *      security={{"bearerAuth":{}}},
     *      summary="Update post",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass post details",
     *          @OA\JsonContent(
     *              required={"title","description","category_id"},
     *              @OA\Property(property="title", type="string", example="Food"),
     *              @OA\Property(property="description", type="string", example="This is a food category"),
     *              @OA\Property(property="category_id", type="number", example=1),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object", @OA\Property(property="id", type="number", example=1),
     *                  @OA\Property(property="title", type="string", example="Food"),
     *                  @OA\Property(property="description", type="string", example="This is a food category"),
     *                  @OA\Property(property="category_id", type="number", example=1),
     *                  @OA\Property(property="created_at", type="string"),
     *                  @OA\Property(property="updated_at", type="string")
     *              ),
     *              @OA\Property(property="message", type="string", example="Category updated"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="object",
     *                  @OA\Property(property="title", type="array",
     *                      @OA\Items(type="string", example="The title has already been taken."),
     *                  )
     *               )
     *          )
     *      ),
     *  )
     */
    public function update(Request $request, Post $post)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $post->title = $input['title'];
        $post->description = $input['description'];
        $post->save();

        return $this->sendResponse(new PostResource($post), 'Post updated.');
    }

    /**
     *  @OA\Delete(
     *      path="/posts/{id}",
     *      operationId="deletePost",
     *      tags={"Posts"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete post",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="string", example="" ),
     *              @OA\Property(property="message", type="string", example="Post deleted"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *      ),
     *  )
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return $this->sendResponse('', 'Post deleted.');
    }
}
