<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Helpers\Functions;
use App\Http\Requests\ResetPasswordRequest;
use App\ApiCode;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{


/**
 * @OA\Post(
 *     path="/api/password/email",
 *     summary="Forgot Password",
 *     tags={"Forgot Password"},
 *
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *
 *                 @OA\Property(
 *                     property="email",
 *                     description="Email",
 *                     example="email@gmail.com",
 *                     type="string"
 *                 ),
 *             )
 *          )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Operation successful",
 *         @OA\MediaType(
 *             mediaType="application/json"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=204,
 *         description="Empty response",
 *         @OA\MediaType(
 *             mediaType="application/json"
 *         )
 *     )
 * )
 */
    public function forgot() {
        $credentials = request()->validate(['email' => 'required|email']);

        Password::sendResetLink($credentials);

        return Functions::sendResponse([], 'Reset password link sent on your email');

    }


/**
 * @OA\Post(
 *     path="/api/password/reset",
 *     summary="Reset Password",
 *     tags={"Password Reset"},
 *
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *
 *                 @OA\Property(
 *                     property="email",
 *                     description="Email",
 *                     example="email@gmail.com",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     description="Password",
 *                     example="P@ssword1",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password_confirmation",
 *                     description="Confirm Password",
 *                     example="P@ssword1",
 *                     type="string"
 *                 ),
 *                @OA\Property(
 *                     property="token",
 *                     description="Token",
 *                     example="",
 *                     type="string"
 *                 ),
 *             )
 *          )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Operation successful",
 *         @OA\MediaType(
 *             mediaType="application/json"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=204,
 *         description="Empty response",
 *         @OA\MediaType(
 *             mediaType="application/json"
 *         )
 *     )
 * )
 */
    public function reset(ResetPasswordRequest $request) {
        $reset_password_status = Password::reset($request->validated(), function ($user, $password) {
            $user->password = $password;
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return Functions::sendError("", [], ApiCode::INVALID_RESET_PASSWORD_TOKEN);
        }

        return Functions::sendResponse([], 'Password has been successfully changed');
    }






    /**
 * @OA\Post(
 *     path="/api/password/changepassword",
 *     summary="Change Password",
 *     tags={"Change Password"},
 *
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *
 *                 @OA\Property(
 *                     property="email",
 *                     description="Email",
 *                     example="email@gmail.com",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     description="Password",
 *                     example="P@ssword1",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password_confirmation",
 *                     description="Confirm Password",
 *                     example="P@ssword1",
 *                     type="string"
 *                 ),
 *             )
 *          )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Operation successful",
 *         @OA\MediaType(
 *             mediaType="application/json"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=204,
 *         description="Empty response",
 *         @OA\MediaType(
 *             mediaType="application/json"
 *         )
 *     )
 * )
 */
    public function changepassword(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|confirmed|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*/',
            ]);

            if($validator->fails()){
                return Functions::sendError($validator->errors()->first(), [], 400);
            }

            if(User::where('email', $request->email)->exists()){
                User::where('email', $request->email)
                ->update([
                    'password' => bcrypt($request->password)
                ]);

                return Functions::sendResponse([], 'Password has been successfully changed');

            }else{
                return Functions::sendError("User doesn't exist", [], 404);
            }


        } catch (\Throwable $e) {
            Log::info("Change Password Error - " . $e->getMessage());
            return Functions::sendError("Operation Failed", [], 404);
        }

    }
}
