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
    public function login(Request $request) {

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

            // Check if the account is soft-deleted
            if ($user->deleted_at !== null) {
                return response()->json([
                    'message' => 'This account has been deleted. Please contact the administrator for assistance.'
                ], 403);
            }

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
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'No bearer token provided or token is invalid.'
                ], 401);
            }

            $token = $user->currentAccessToken();

            if ($token) {
                $token->delete();
            } else {
                return response()->json([
                    'message' => 'Token not found or already invalidated.'
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

    public function changePassword(Request $request)
    {
        try {
            $validator = ValidationHelper::validate($request, [
                'current_password' => 'required',
                'new_password' => 'required|min:6',
            ]);

            if ($validator) return $validator;

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 401);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json(['message' => 'Password changed successfully.'], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while changing the password.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateInformation(Request $request)
    {
        try {
            $validator = ValidationHelper::validate($request, [
                'first_name' => 'required|string',
                'middle_name' => 'nullable|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:accounts,email,' . $request->user()->id,
                'user_role' => 'required|in:teacher,admin,student',
            ]);

            if ($validator) return $validator;

            $user = $request->user();

            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name ?? '';
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->user_role = $request->user_role;
            $user->save();

            return response()->json(['message' => 'User information updated successfully.'], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating user information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateInformationById(Request $request, $id)
    {
        try {
            $validator = ValidationHelper::validate($request, [
                'first_name' => 'required|string',
                'middle_name' => 'nullable|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:accounts,email,' . $id,
                'user_role' => 'required|in:teacher,admin,student',
            ]);

            if ($validator) return $validator;

            $user = Account::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name ?? '';
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->user_role = $request->user_role;
            $user->save();

            return response()->json(['message' => 'User information updated successfully.'], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating user information.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $validator = ValidationHelper::validate($request, [
                'status' => 'required|in:active,inactive',
            ]);

            if ($validator) return $validator;

            $user = Account::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->status = $request->status;
            $user->save();

            return response()->json(['message' => 'User status updated successfully.'], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while changing status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createAccount(Request $request)
    {
        try {
            $validator = ValidationHelper::validate($request, [
                'email' => 'required|email|unique:accounts,email',
                'password' => 'required|min:6|confirmed',
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'user_role' => 'required|in:teacher,admin,student',
                'status' => 'required|in:active,inactive'
            ]);

            if ($validator) return $validator;

            $account = Account::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?? '',
                'last_name' => $request->last_name,
                'user_role' => $request->user_role,
                'status' => $request->status
            ]);

            return response()->json([
                'message' => 'Account created successfully',
                'account_id' => $account->id
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllAccounts(Request $request)
    {
        try {
            $query = Account::query();

            if($request->has('is_deleted')) {
                if($request->is_deleted === "false") $query->whereNull('deleted_at');
                else $query->whereNotNull('deleted_at');
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('middle_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%");
                });
            }

            // Optional filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Optional filter by user_role
            if ($request->has('user_role') && $request->user_role) {
                $query->where('user_role', $request->user_role);
            }

            // Optional exclusion of user_role
            if ($request->has('exclude_user_role') && $request->exclude_user_role) {
                $query->where('user_role', '!=', $request->exclude_user_role);
            }

            // Exclude the current account
            if ($request->user()) {
                $query->where('id', '!=', $request->user()->id);
            }

            // Sort by name (first_name, middle_name, last_name)
            $query->orderBy('first_name')
                  ->orderBy('middle_name')
                  ->orderBy('last_name');

            // Paginate results
            $accounts = $query->paginate(10);

            return response()->json($accounts, 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving accounts.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function softDeleteAccount(Request $request, $id)
    {
        try {
            $user = Account::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->deleted_at = now();
            $user->save();

            return response()->json(['message' => 'Account deactivated successfully.'], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deactivating the account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function restoreAccount(Request $request, $id)
    {
        try {
            $user = Account::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            if ($user->deleted_at === null) {
                return response()->json(['message' => 'Account is not deleted.'], 400);
            }

            $user->deleted_at = null;
            $user->save();

            return response()->json(['message' => 'Account restored successfully.'], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while restoring the account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
