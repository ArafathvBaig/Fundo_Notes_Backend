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
    public function register(Request $request)
    {
        $data = $request->only('first_name', 'last_name', 'email', 'password', 'password_confirmation');
        $validator = Validator::make($data, [
            'first_name' => 'required|string|min:3',
            'last_name' => 'required|string|min:3',
            'email' => 'required|string|unique:users',
            'password' => 'required|string|min:6|max:15',
            'password_confirmation' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        User::createUser($request);

        return response()->json([
            'status' => 201,
            'message' => 'User Successfully Registered'
        ]);
    }

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
                    'status' => "404",
                    'message' => "Not a Registered Email"
                ]);
            } elseif (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 402,
                    'message' => 'Wrong Password'
                ]);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'status' => 500,
                'message' => 'Could not create token.',
            ]);
        }

        //Token created, return with success response and jwt token
        $token = JWTAuth::attempt($credentials);
        return response()->json([
            'success' => 'Login Successful.',
            'token' => $token
        ]);
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
