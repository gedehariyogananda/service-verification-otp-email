<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AuthenticateMail;
use App\Mail\ForgotMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgot']]);
    }

    // ----------------------------- Authenticate ---------------------------------//

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validate->errors(),
            ], 422);
        }

        try {
            $userCheck = User::where('email', $request->email)->first();
            if (!$userCheck) {
                return response()->json([
                    'success' => false,
                    'message' => 'not registered'
                ]);
            }

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'email or password not registered',
                ], 401);
            }

            if ($userCheck->is_verified_register == 0) {
                $userCheck['is_verified_register'] = false;
            } else {
                $userCheck['is_verified_register'] = true;
            }

            $mappedDataUser = [
                'name' => $userCheck ? $userCheck->name : null,
                'email' => $userCheck ? $userCheck->email : null,
                'is_verified_register' => $userCheck ? $userCheck->is_verified_register : null,
                'email_verified_at' => $userCheck ? $userCheck->email_verified_at : null,
                'token' => $token
            ];

            if ($userCheck['is_verified_register'] == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Please verify your email first before login, check your email for verification',
                    'data' => $mappedDataUser
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User login successfully',
                'data' => $mappedDataUser
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
            ], 422);
        }

        try {
            $validTokenRegister = rand(100000, 999999);

            // set state 1 jam expired
            $validTokenRegisterExpired = now()->addHours(1);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'otp_register' => $validTokenRegister,
                'otp_register_expired_at' => $validTokenRegisterExpired
            ]);

            $token = Auth::guard('api')->login($user);

            // send email notification
            $get_user_email = $user['email'];
            $get_user_name = $user['name'];
            Mail::to($user['email'])->send(new AuthenticateMail($get_user_email, $get_user_name, $validTokenRegister));

            if ($user->is_verified_register == 0) {
                $user['is_verified_register'] = false;
            } else {
                $user['is_verified_register'] = true;
            }

            $mappedDataUser = [
                'name' => $user ? $user->name : null,
                'email' => $user ? $user->email : null,
                'is_verified_register' => $user ? $user->is_verified_register : null,
                'email_verified_at' => $user ? $user->email_verified_at : null,
                'token' => $token
            ];

            return response()->json([
                'success' => true,
                'message' => 'User created successfully, verified email send',
                'data' => $mappedDataUser
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtpRegister(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'otp' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
            ], 422);
        }

        try {
            $initUser = User::where('id', auth()->user()->id)->first();
            $theToken = $initUser->otp_register;

            if ($request->otp != $theToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'the otp not correct, try again check your email addreses'
                ]);
            }

            if ($initUser->otp_register_expired_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'the otp has expired, click the button to try again otp and check your email addreses'
                ]);
            }

            User::where('id', auth()->user()->id)->update([
                'is_verified_register' => true,
                'email_verified_at' => now()
            ]);

            $user = User::where('id', auth()->user()->id)->first();

            if ($user->is_verified_register == 1) {
                $user['is_verified_register'] = true;
            } else {
                $user['is_verified_register'] = false;
            }

            $mappedDataUser = [
                'name' => $user ? $user->name : null,
                'email' => $user ? $user->email : null,
                'is_verified_register' => $user ? $user->is_verified_register : null,
                'email_verified_at' => $user ? $user->email_verified_at : null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'The otp has verified',
                'data' => $mappedDataUser
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function newOtp()
    {
        $user = User::where('id', auth()->user()->id)->first();

        try {
            $validTokenRegister = rand(100000, 999999);

            // send new otp email notification
            $get_user_email = $user['email'];
            $get_user_name = $user['name'];
            Mail::to($user['email'])->send(new AuthenticateMail($get_user_email, $get_user_name, $validTokenRegister));

            $updateOtp = $user->update([
                'otp_register' => $validTokenRegister,
                'otp_register_expired_at' => now()->addHours(1)
            ]);

            if ($updateOtp) {
                return response()->json([
                    'success' => true,
                    'message' => 'new otp has been sent to your email'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'failed to send new otp'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------- FORGOT PASSWORD -------------------------------------//

    public function forgot(Request $request)
    {
        $validate = Validator::make(request()->all(), [
            'email' => 'required|email'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found'
                ]);
            }

            // create token jwt auth bisa tidak login
            $token = JWTAuth::fromUser($user);


            $validTokenForgot = rand(100000, 999999);

            // set state 1 jam expired
            $validTokenForgotExpired = now()->addHours(1);

            $user->update([
                'otp_forgot' => $validTokenForgot,
                'otp_forgot_expired_at' => $validTokenForgotExpired
            ]);

            // send email notification
            $get_user_email = $user['email'];
            $get_user_name = $user['name'];
            Mail::to($user['email'])->send(new ForgotMail($get_user_email, $get_user_name, $validTokenForgot));

            return response()->json([
                'success' => true,
                'message' => 'Check your email for verification code',
                'data' => [
                    'token' => $token,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtpForgot(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'otp' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
            ], 422);
        }

        try {
            $initUser = User::where('id', auth()->user()->id)->first();
            $theToken = $initUser->otp_forgot;

            if ($request->otp != $theToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'the otp not correct, try again check your email addreses'
                ]);
            }

            if ($initUser->otp_forgot_expired_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'the otp has expired, click the button to try again otp and check your email addreses'
                ]);
            }

            $successVerify = $initUser->update([
                'is_verified_forgot' => true
            ]);

            if ($initUser->is_verified_forgot == 1) {
                $initUser['is_verified_forgot'] = true;
            } else {
                $initUser['is_verified_forgot'] = false;
            }

            $mappedDataUser = [
                'name' => $initUser ? $initUser->name : null,
                'email' => $initUser ? $initUser->email : null,
                'is_verified_forgot' => $initUser ? $initUser->is_verified_forgot : null,
            ];

            if ($successVerify) {
                return response()->json([
                    'success' => true,
                    'message' => 'The otp has verified',
                    'data' => $mappedDataUser
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify otp'
                ]);
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // the point changes password service
    public function reset(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
            ], 422);
        }

        try {
            $user = User::where('id', auth()->user()->id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }

            $user->update([
                'password' => Hash::make($request->password),
                'is_verified_forgot' => false,
                'otp_forgot' => null,
                'otp_forgot_expired_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password has been changed, try again login in new password'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ------------------------------------- LOGOUT ---------------------------------- //

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }
}
