<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Http\Resources\Category as CategoryResource;

class CategoryController extends BaseController
{
    /**
     *  @OA\Get(
     *      path="/categories",
     *      operationId="getPosts",
     *      tags={"Categories"},
     *      security={{"bearerAuth":{}}},
     *      summary="Get all categories",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="number"),
     *                      @OA\Property(property="name", type="string"),
     *                      @OA\Property(property="created_at", type="string"),
     *                      @OA\Property(property="updated_at", type="string")
     *                   )
     *              ),
     *              @OA\Property(property="message", type="string", example="User created successfully"),
     *          )
     *      )
     *  )
     */
    public function index()
    {
        $categories = Category::all();
        return $this->sendResponse(CategoryResource::collection($categories), 'Categories fetched');
    }

    /**
     *  @OA\Post(
     *      path="/categories",
     *      operationId="postPosts",
     *      tags={"Categories"},
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
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="number", example=1),
     *                      @OA\Property(property="name", type="string", example="test"),
     *                      @OA\Property(property="created_at", type="string", example="04/12/2023"),
     *                      @OA\Property(property="updated_at", type="string", example="04/12/2023")
     *                   )
     *              ),
     *              @OA\Property(property="message", type="string", example="User created successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="object", @OA\Property(property="name", type="array",
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
            'name' => ['required', 'unique:categories,name']
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $category = Category::create($input);
        return $this->sendResponse(new CategoryResource($category), 'Category created.');
    }

    /**
     *  @OA\Get(
     *      path="/categories/{id}",
     *      operationId="getPost",
     *      tags={"Categories"},
     *      security={{"bearerAuth":{}}},
     *      summary="Retrieve category",
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="number", example=1),
     *                      @OA\Property(property="name", type="string", example="test"),
     *                      @OA\Property(property="created_at", type="string", example="04/12/2023"),
     *                      @OA\Property(property="updated_at", type="string", example="04/12/2023")
     *                   )
     *              ),
     *              @OA\Property(property="message", type="string", example="User created successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="message", type="object", @OA\Property(property="name", type="array",
     *                      @OA\Items(type="string", example="The name has already been taken."),
     *                  )
     *               )
     *          )
     *      ),
     *  )
     */
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
