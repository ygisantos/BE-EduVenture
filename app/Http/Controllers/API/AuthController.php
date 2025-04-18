<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Helper\ValidationHelper;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function Login(Request $request) {

        try {
            $validator = ValidationHelper::validate($request, [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Returns validation error
            if ($validator) return $validator;

            // Check if email exist on database
            $user = Account::where('email', $request->email)->first();
            if (!$user) return response()->json(['message' => 'The email address you entered is not associated with any account.'], 404);

            // Attempts Login
            if (!Hash::check($request->password, $user->password))
                return response()->json(['message' => 'The provided credentials are incorrect.'], 401);

            // Check if the user is active
            if ($user->status === 'inactive') {
                return response()->json([
                    'message' => 'Your account is currently inactive. Please contact the administrator for assistance.'
                ], 401);
            }

            // Create token
            $token = $user->createToken($request->email)->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'type' => $user->user_role,
                'token' => $token
            ], 200);

        } catch(Exception $e) {
            return response()->json([
                'message' => 'An error occurred while logging in',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Removes token
            $request->user()->currentAccessToken()->delete();
            if (!$request->user()) {
                return response()->json([
                    'message' => 'No bearer token provided or token is invalid.'
                ], 401);
            }
            return response()->json([
                'message' => 'Logout successful'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get the current logged user
    public function getCurrentUser(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'No authenticated user found'
                ], 401);
            }

            return response()->json([
                'user' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
