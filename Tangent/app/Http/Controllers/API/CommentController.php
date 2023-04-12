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
    public function index()
    {
        $comments = Post::all();
        return $this->sendResponse(CommentResource::collection($comments), 'Comments fetched.');
    }

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

        $comment = Post::find($input['post_id']);
        if (is_null($comment)) {
            return $this->sendError('Post does not exist.');
        }
        $authUser = Auth::user();
        $input['user_id'] = $authUser->id;

        $comment = Comment::create($input);
        return $this->sendResponse(new CommentResource($comment), 'Comment created.');
    }

    public function show($id)
    {
        $comment = Comment::find($id);
        if (is_null($comment)) {
            return $this->sendError('Comment does not exist.');
        }
        return $this->sendResponse(new CommentResource($comment), 'Comment fetched.');
    }

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

        return $this->sendResponse(new CommentResource($comment), 'Comment updated.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return $this->sendResponse([], 'Post deleted.');
    }
}
