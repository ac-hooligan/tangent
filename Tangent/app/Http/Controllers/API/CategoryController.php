<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Http\Resources\Category as CategoryResource;

class CategoryController extends BaseController
{
    public function index()
    {
        $categories = Category::all();
        return $this->sendResponse(CategoryResource::collection($categories), 'Categories fetched.');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => ['required', 'unique:categories,name']
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $category = Category::create($input);
        return $this->sendResponse(new CategoryResource($category), 'Category created.');
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (is_null($category)) {
            return $this->sendError('Category does not exist.');
        }
        return $this->sendResponse(new CategoryResource($category), 'Category fetched.');
    }

    public function update(Request $request, Category $category)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $category->title = $input['name'];
        $category->category = $input['category'];
        $category->save();

        return $this->sendResponse(new CategoryResource($category), 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return $this->sendResponse([], 'Category deleted.');
    }
}
