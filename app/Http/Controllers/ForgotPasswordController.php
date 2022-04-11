<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Mail\Mailer;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;

class ForgotPasswordController extends Controller
{
    /**
     * @OA\Post(
     *  path="/api/forgotPassword",
     *  summary="Forgot Password",
     *  description="Forgot Password for an user",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"email"},
     *              @OA\Property(property="email", type="email"),
     *          ),  
     *      ),
     *  ),
     *  @OA\Response(response=404, description="Not a Registered Email"),
     *  @OA\Response(response=424, description="Email Not Sent"),
     *  @OA\Response(response=200, description="Reset Password Token Sent to your Email")
     * )
     * 
     * Forgot Password
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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
            ],404);
        }

        $token = JWTAuth::fromUser($user);
        if ($user) {
            $mail = new Mailer();
            $check = $mail->sendEmail($user, $token);
            if (!$check) {
                return response()->json([
                    'status' => 424,
                    'message' => 'Email Not Sent'
                ],424);
            } else {
                return response()->json([
                    'status' => 200,
                    'message' => 'Reset Password Token Sent to your Email',
                ],200);
            }
        }
    }

    /**
     * @OA\Post(
     *  path="/api/resetPassword",
     *  summary="Reset User Password",
     *  description="Reset User Password using the token sent to the mail",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"new_password","password_confirmation"},
     *              @OA\Property(property="new_password", type="string"),
     *              @OA\Property(property="password_confirmation", type="string")
     *          ),  
     *      ),
     *  ),
     *  @OA\Response(response=400, description="User Not found with this Email"),
     *  @OA\Response(response=201, description="Password Reset Successful")
     * )
     * 
     * Reset User Password
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        //validate all credentials
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6|max:15',
            'password_confirmation' => 'required|same:new_password'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        $user = JWTAuth::parseToken()->authenticate();
        $user = User::where('email', $user->email)->first();
        if (!$user) {
            Log::error('User Not found with this Email.', ['Email' => $user->email]);
            return response()->json([
                'status' => "400",
                'message' => "User Not found with this Email"
            ], 400);
        }
        if ($user) {
            $user->password = bcrypt($request->new_password);
            $user->save();
            Log::info('Reset Successful: Email Id: ' . $user->email);
            return response()->json([
                'status' => 201,
                'message' => 'Password Reset Successful'
            ], 201);
        }
    }
}
