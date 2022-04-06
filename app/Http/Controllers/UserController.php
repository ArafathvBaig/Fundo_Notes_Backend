<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
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

        return response()->json(['status'=> 201,
        'message' => 'User Successfully Registered']);
    }
}
