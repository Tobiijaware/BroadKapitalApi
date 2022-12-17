<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Functions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }



/**
 * @OA\Post(
 *     path="/api/register",
 *     summary="Create User",
 *     operationId="MpProjects.active",
 *     tags={"Registration"},
 *
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *
 *                 @OA\Property(
 *                     property="firstname",
 *                     description="First Name",
 *                     example="John",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="lastname",
 *                     description="Last Name",
 *                     example="Doe",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="email",
 *                     description="Email",
 *                     example="email@gmail.com",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="phone_number",
 *                     description="Phone Number",
 *                     example="+2347037796307",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="gender",
 *                     description="Gender",
 *                     example="male",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     description="Password",
 *                     example="P@ssword1",
 *                     type="string"
 *                 ),
 *                  @OA\Property(
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
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'password' => 'required|confirmed|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*/',
            'phone_number' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'gender' => 'required'
        ]);

        if($validator->fails()){
            return Functions::sendError($validator->errors()->first(), [], 400);
        }

        $request->request->add(['password' => bcrypt($request->password)]);
        $request->request->remove('password_confirmation');

        $success['user'] = User::create($request->all());

        $success['user']->sendEmailVerificationNotification();

        $success['token'] = $success['user']->createToken('API Token')->accessToken;

        //event(new Registered($success['user']));

        return Functions::sendResponse($success, 'User registered successfully.');

    }





/**
 * @OA\Post(
 *     path="/api/login",
 *     summary="Login User",
 *     tags={"Login"},
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
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response(['error_message' => 'Incorrect Details.
            Please try again']);
        }

        $success['user'] =  auth()->user();

        $success['token'] = auth()->user()->createToken('API Token')->accessToken;

        return Functions::sendResponse($success, 'Login successful.');

    }


    /**
     * @OA\Post(
     *   path="/api/logout",
     *   tags={"Logout"},
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="successful operation"),
     *   @OA\Response(response=406, description="not acceptable"),
     *   @OA\Response(response=500, description="internal server error"),
     *
     * )
     *
     */
    public function logout() {
        auth()->logout();
        return Functions::sendResponse([], 'Logout successful.');
    }
}
