<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\ApiCode;
use App\Helpers\Functions;

class VerificationController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api')->except(['verify']);
    }


    public function verify($userid, Request $request) {
        if (! $request->hasValidSignature()) {
            return $this->respondUnAuthorizedRequest(ApiCode::INVALID_EMAIL_VERIFICATION_URL);
        }

        $user = User::findOrFail($userid);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return Functions::sendResponse([], 'Email Verified');
    }

    public function resend() {
        
        if (auth()->user()->hasVerifiedEmail()) {
            return Functions::sendError("Email has already been verified", [], ApiCode::EMAIL_ALREADY_VERIFIED);
        }

        auth()->user()->sendEmailVerificationNotification();

        return Functions::sendResponse([], 'Email verification link sent on your email');

    }



}
