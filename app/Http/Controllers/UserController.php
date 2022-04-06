<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->only('first_name', 'last_name', 'email', 'password', 'password_confirmation');
        $validator = Validator::make($data, ['first_name'=>'required|string|min:3', 
        'last_name' => 'required|string|min:3',
        'email'=> 'required|string|unique:users',
        'password'=> 'required|string|min:6|max:15',
        'password_confirmation'=> 'required|same:password']);

        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()], 200);
        }

        $user = User::create(['first_name'=>$request->first_name,
        'last_name'=>$request->last_name,
        'email'=>$request->email,
        'password'=>bcrypt($request->password)]);

        return response()->json([
            'status'=> 201,
            'message' => 'User Successfully Registered']);
    }

    public function authenticate(Request $request)
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
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
}
