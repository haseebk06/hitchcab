<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Mail\SendOtpMail;
use App\Models\User;

class UserController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'phone_number' => 'required|string|unique:users|digits_between:10,15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $otp = rand(10000, 99999);

        $user = User::create($request->all());
        $user->verificaton_token = $otp;
        $user->save();

        $token = $user->createToken($request->email);

        Mail::to($user->email)->send(new SendOtpMail($user));

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user,
            'token' => $token->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users',
            'password' => 'required|max:40',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'The Password is incorrect',
            ], 422);
        }

        $token = $user->createToken($user->email);

        return response()->json([
            'status' => true,
            'message' => 'User Logged In successfully',
            'data' => $user,
            'token' => $token->plainTextToken
        ], 201);
    }

    public function getUser(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user(),
        ]);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|min:5|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('verificaton_token', $request->otp)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'The OTP is invalid please try again.',
            ], 422);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email already verified.',
            ], 422);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            'status' => true,
            'message' => 'Email Verified Successfully',
        ], 200);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired token',
            ], 401);
        }

        $otp = rand(10000, 99999);

        $user->verificaton_token = $otp;
        $user->save();

        Mail::to($user->email)->send(new SendOtpMail($user));

        return response()->json([
            'status' => true,
            'message' => 'OTP resent successfully',
        ], 200);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|exists:users,email|max:224',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $otp = rand(10000, 99999);

        $token = $user->createToken($request->email);
        $user->verificaton_token = $otp;
        $user->email_verified_at = null;
        $user->save();

        Mail::to($request->email)->send(new SendOtpMail($user));

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'token' => $token->plainTextToken,
        ], 200);
    }

    public function setPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|max:40',
            'confirmPassword' => 'required|same:password',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $setPassword = User::findOrFail($user->id);
        $setPassword->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Passwrod set sucessfully',
        ], 200);
    }

    public function completeYourProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:224',
            'street' => 'required|string|max:224',
            'city' => 'required|string|max:224',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $setPassword = User::findOrFail($user->id);
        $setPassword->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'User updated sucessfully',
        ], 200);
    }

    public function setRole(Request $request)
    {
        $user = $request->user();
        
        $setRole = User::findOrFail($user->id);
        $setRole->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'role added sucessfully',
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ], 204);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully'
        ], 204);
    }
}
