<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends BaseController
{
    /**
     *  @OA\Post(
     *      path="/login",
     *      operationId="signin",
     *      tags={"Authentication"},
     *      summary="Log into project",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user details",
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", example="admin@admin.com"),
     *              @OA\Property(property="password", type="string", example="admin123"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object", example={"token":"test", "name":"admin"}),
     *              @OA\Property(property="message", type="string", example="User signed in"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="data", type="object", example={"error":"Unauthorised"}),
     *              @OA\Property(property="message", type="string", example="Unauthorised"),
     *          )
     *      ),
     *  )
     */
    public function signin(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $authUser = Auth::user();
            $success['token'] =  $authUser->createToken('MyAuthApp')->plainTextToken;
            $success['name'] =  $authUser->name;

            return $this->sendResponse($success, 'User signed in');
        } else {
            return $this->sendError('Unauthorised', ['error' => 'Unauthorised']);
        }
    }

    /**
     *  @OA\Post(
     *      path="/register",
     *      operationId="register",
     *      tags={"Authentication"},
     *      summary="Register new account",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user details",
     *          @OA\JsonContent(
     *              required={"name","email","password","confirm_password"},
     *              @OA\Property(property="name", type="string", example="admin"),
     *              @OA\Property(property="email", type="string", example="admin@admin.com"),
     *              @OA\Property(property="password", type="string", example="admin123"),
     *              @OA\Property(property="confirm_password", type="string", example="admin123"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="object", example={"token":"test", "name":"admin"}),
     *              @OA\Property(property="message", type="string", example="User created successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Fail",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="data", type="object", example={"name":"The name has already been taken."})
     *          )
     *      ),
     *  )
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'unique:users,name'],
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyAuthApp')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User created successfully.');
    }
}
