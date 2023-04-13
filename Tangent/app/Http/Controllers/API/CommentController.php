<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Models\Post;
use App\Http\Resources\Comment as CommentResource;

class CommentController extends BaseController
{
    /**
     *  @OA\Get(
     *      path="/comments",
     *      operationId="getComment",
     *      tags={"Comments"},
     *      security={{"bearerAuth":{}}},
     *      summary="Get all comments",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="number"),
     *                      @OA\Property(property="title", type="string"),
     *                      @OA\Property(property="description", type="string"),
     *                      @OA\Property(property="rating", type="string"),
     *                      @OA\Property(property="post_id", type="string"),
     *                      @OA\Property(property="created_at", type="string"),
     *                      @OA\Property(property="updated_at", type="string")
     *                   )
     *              ),
     *              @OA\Property(property="message", type="string", example="Comments fetched"),
     *          )
     *      )
     *  )
     */
    public function index()
    {
        $comments = Comment::all();
        return $this->sendResponse(CommentResource::collection($comments), 'Comments fetched');
    }

    /**
     *  @OA\Post(
     *      path="/comments",
     *      operationId="postComment",
     *      tags={"Comments"},
     *      security={{"bearerAuth":{}}},
     *      summary="Create new category",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass category details",
     *          @OA\JsonContent(
     *              required={"email","content"},
     *              @OA\Property(property="email", type="string", example="Food"),
     *              @OA\Property(property="content", type="string", example="This is a food category"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="number", example=1),
     *                  @OA\Property(property="title", type="string", example="test"),
     *                  @OA\Property(property="description", type="string", example="this is a test"),
     *                  @OA\Property(property="rating", type="number", example=3),
     *                  @OA\Property(property="post_id", type="number", example=3),
     *                  @OA\Property(property="created_at", type="string", example="04/12/2023"),
     *                  @OA\Property(property="updated_at", type="string", example="04/12/2023")
     *              ),
     *              @OA\Property(property="message", type="string", example="Comment created"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="object",
     *                  @OA\Property(property="title", type="array",
     *                      @OA\Items(type="string", example="The name has already been taken."),
     *                  )
     *               )
     *          )
     *      ),
     *  )
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => ['required', 'unique:posts,title'],
            'post_id' => ['required', 'numeric'],
            'description' => 'required',
            'rating' => ['numeric'],
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $Post = Post::find($input['post_id']);
        if (is_null($Post)) {
            return $this->sendError('Post does not exist');
        }
        $authUser = Auth::user();
        $input['user_id'] = $authUser->id;

        $comment = Comment::create($input);
        return $this->sendResponse(new CommentResource($comment), 'Comment created');
    }

    /**
     *  @OA\Get(
     *      path="/comments/{id}",
     *      operationId="getSingleComment",
     *      tags={"Comments"},
     *      security={{"bearerAuth":{}}},
     *      summary="Retrieve comment",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="number", example=1),
     *                  @OA\Property(property="title", type="string", example="Test"),
     *                  @OA\Property(property="description", type="string", example="Lorem Ipsum is simply"),
     *                  @OA\Property(property="rating", type="number", example=4),
     *                  @OA\Property(property="post_id", type="number", example=1),
     *                  @OA\Property(property="created_at", type="string", example="04/12/2023"),
     *                  @OA\Property(property="updated_at", type="string", example="04/12/2023")
     *              ),
     *              @OA\Property(property="message", type="string", example="Comment fetched"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Comment does not exist")
     *          )
     *      ),
     *  )
     */
    public function show($id)
    {
        $comment = Comment::find($id);
        if (is_null($comment)) {
            return $this->sendError('Comment does not exist');
        }
        return $this->sendResponse(new CommentResource($comment), 'Comment fetched');
    }

    /**
     *  @OA\Put(
     *      path="/comments/{id}",
     *      operationId="putComment",
     *      tags={"Comments"},
     *      security={{"bearerAuth":{}}},
     *      summary="Update comments",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass category details",
     *          @OA\JsonContent(
     *              required={"name","content"},
     *              @OA\Property(property="name", type="string", example="Food"),
     *              @OA\Property(property="content", type="string", example="This is a food category"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="number", example=1),
     *                  @OA\Property(property="title", type="string", example="Test"),
     *                  @OA\Property(property="description", type="string", example="Lorem Ipsum is simply"),
     *                  @OA\Property(property="rating", type="number", example=3),
     *                  @OA\Property(property="post_id", type="number", example=3),
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
     *                  @OA\Property(property="name", type="array",
     *                      @OA\Items(type="string", example="The name has already been taken."),
     *                  )
     *               )
     *          )
     *      ),
     *  )
     */
    public function update(Request $request, Comment $comment)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $comment->title = $input['title'];
        $comment->description = $input['description'];
        $comment->save();

        return $this->sendResponse(new CommentResource($comment), 'Comment updated');
    }

    /**
     *  @OA\Delete(
     *      path="/comments/{id}",
     *      operationId="deleteComment",
     *      tags={"Comments"},
     *      security={{"bearerAuth":{}}},
     *      summary="Delete comment",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="string", example="" ),
     *              @OA\Property(property="message", type="string", example="Comment deleted"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *      ),
     *  )
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return $this->sendResponse('', 'Comment deleted');
    }
}
