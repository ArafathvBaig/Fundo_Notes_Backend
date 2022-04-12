<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *  path="/api/register",
     *  summary="register",
     *  description="Register a new user",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"first_name","last_name","email","password","password_confirmation"},
     *              @OA\Property(property="first_name", type="string"),
     *              @OA\Property(property="last_name", type="string"),
     *              @OA\Property(property="email", type="email"),
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="password_confirmation", type="string")
     *          ),  
     *      ),
     *  ),
     *  @OA\Response(response=201, description="User Successfully Registered")
     * )
     * 
     * Register New User
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $data = $request->only('first_name', 'last_name', 'email', 'password', 'password_confirmation');
        $validator = Validator::make($data, [
            'first_name' => 'required|string|min:3',
            'last_name' => 'required|string|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:15',
            'password_confirmation' => 'required|same:password'
        ]);

        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 200);
        // }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            return response()->json([
                'message' => 'The email has already been taken.'
            ], 401);
        }

        User::createUser($request);

        return response()->json([
            'status' => 201,
            'message' => 'User Successfully Registered'
        ],201);
    }

    /**
     * @OA\Post(
     *  path="/api/login",
     *  summary="login",
     *  description="Login by email, password",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"email","password"},
     *              @OA\Property(property="email", type="email"),
     *              @OA\Property(property="password", type="string"),
     *          ),  
     *      ),
     *  ),
     *  @OA\Response(response=404, description="Not a Registered Email"),
     *  @OA\Response(response=402, description="Wrong Password"),
     *  @OA\Response(response=500, description="Could not create token"),
     *  @OA\Response(response=201, description="Login Successful")
     * )
     * 
     * login user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:15'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        //Request is validated
        //Create token
        $user = User::where('email', $request->email)->first();
        try {
            if (!$user) {
                return response()->json([
                    // 'status' => 404,
                    'message' => 'Not a Registered Email'
                ], 404);
            } elseif (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    // 'status' => 402,
                    'message' => 'Wrong Password'
                ], 402);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'status' => 500,
                'message' => 'Could not create token',
            ], 500);
        }

        //Token created, return with success response and jwt token
        $token = JWTAuth::attempt($credentials);
        return response()->json([
            //'status' => 201,
            'success' => 'Login Successful',
            //'token' => $token
        ], 201);
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }
}
