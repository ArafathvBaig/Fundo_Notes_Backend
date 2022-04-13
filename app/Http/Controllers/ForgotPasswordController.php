<?php

namespace App\Http\Controllers;

use App\Exceptions\FundoNotesException;
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
                'message' => "Not a Registered Email"
            ], 404);
        }

        $token = JWTAuth::fromUser($user);
        if ($user) {
            $mail = new Mailer();
            $check = $mail->sendEmail($user, $token);
            if (!$check) {
                return response()->json([
                    'message' => 'Email Not Sent'
                ], 424);
            } else {
                return response()->json([
                    'message' => 'Reset Password Token Sent to your Email',
                ], 200);
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
     *  @OA\Response(response=201, description="Password Reset Successful"),
     *  security={
     *      {"Bearer": {}}
     *  }
     * )
     * 
     * Reset User Password
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        try {
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
            if (!$user) {
                Log::error('User Not Found With This Email');
                return response()->json([
                    'message' => "User Not Found With This Email"
                ], 400);
            } else {
                $user = User::where('email', $user->email)->first();
                $user->password = bcrypt($request->new_password);
                $user->save();
                Log::info('Reset Successful: Email Id: ' . $user->email);
                return response()->json([
                    'message' => 'Password Reset Successful'
                ], 201);
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }
}
