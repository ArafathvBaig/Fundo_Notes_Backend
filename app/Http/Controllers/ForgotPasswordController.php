<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Mail\Mailer;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $email = $request->only('email');

        //validate email
        $validator = Validator::make($email, [
            'email' => 'required|email'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => "404",
                'message' => "Not a Registered Email"
            ]);
        }

        $token = JWTAuth::attempt($email);
        if ($user) {
            $mail = new Mailer();
            $mail->sendEmail($email, $token);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Reset Password Token Sent to your Email.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->only('new_password', 'password_confirmation');

        //validate token
        $validator = Validator::make($data, [
            'new_password' => 'required|string|min:6|max:15',
            'password_confirmation' => 'required|same:new_password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => "Password doesn't match"
            ]);
        }

        $user = Auth::user();
        $user = User::where('email', $user->email)->first();

        // if (!$user) {
        //     return response()->json([
        //         'status' => "404",
        //         'message' => "Not a Registered Email"
        //     ]);
        // }
        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json([
            'status' => 201,
            'message' => 'password reset successful.'
        ]);
    }
}
