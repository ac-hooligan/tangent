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
    public function index()
    {
        $posts = Post::all();
        return $this->sendResponse(PostResource::collection($posts), 'Posts fetched.');
    }

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
            return $this->sendError('Category does not exist.');
        }
        $authUser = Auth::user();
        $input['user_id'] = $authUser->id;

        $post = Post::create($input);
        return $this->sendResponse(new PostResource($post), 'Post created.');
    }

    public function show($id)
    {
        $post = Post::find($id);
        if (is_null($post)) {
            return $this->sendError('Post does not exist.');
        }
        return $this->sendResponse(new PostResource($post), 'Post fetched.');
    }

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

    public function destroy(Post $post)
    {
        $post->delete();
        return $this->sendResponse([], 'Post deleted.');
    }
}
