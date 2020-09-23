<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Notifications\EmailVerifyQueueing;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use App\Jobs\SendEmail;
use Carbon\Carbon;
use App\User;
use JWTAuth;
use Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $messages = [
            'required'  => 'The :attribute field is empty.',
            'unique'    => 'Email already taken.',
        ];
        $rules = [            
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ];

        try {

            $validatedData = Validator::make($request->all(), $rules, $messages);
            
            if($validatedData->fails()) {
                return response()->json([
                    'message' => $validatedData->errors()->first()
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'activation_code' => Str::random(40)
            ]);
            
            $user->save();
            
            // SendEmail::dispatch($user);
            SendEmail::dispatch($user)->delay(now()->addSeconds(3));

            return response()->json([
                'message' => 'User successfully registered! Check your email to verify the account.'
            ], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => $th
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function login(Request $request)
    {
        try {

            $validatedData = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $jwt_token = null;
    
            if ($validatedData->fails()) {
                return response()->json([$validatedData->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            if (!$jwt_token = JWTAuth::attempt($validatedData->validated())) {
                return response()->json([
                    'message' => 'Invalid credentials.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json([
                'access_token' => $jwt_token
            ], Response::HTTP_CREATED);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verifyEmail(string $activationCode)
    {
        try {
        
            $user = app(User::class)->where('activation_code', $activationCode)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'The activation code does not exist for any user in our system.'
                ], Response::HTTP_NOT_FOUND);
            }
    
            $user->email_verified_at = Carbon::now();
            $user->remember_token = sha1($activationCode);
            $user->activation_code = null;
            $user->save();
    
            return response()->json([
                'message' => 'Email verification successful.'
            ], Response::HTTP_ACCEPTED);
            
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
