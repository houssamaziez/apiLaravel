<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'adrass' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
                'image' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required_with:password|string|min:8',
            ], [
                'password_confirmation.required_with' => 'Password confirmation is required when password is present.',
                'password_confirmation.min' => 'Password confirmation must be at least 8 characters long.',
            ]);

            // Create a new user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'adrass' => $validated['adrass'],
                'image' => $validated['image'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            // Generate a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response
            return response()->json([
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'date'=>$user ,
            ], 201);
        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Return generic error response
            return response()->json([
                'error' => 'User registration failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        // Find the user by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Validate the request data (excluding email and password)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // 'phone' => 'required|string|max:15',
            // 'address' => 'required|string|max:255',
            // Add any other fields you want to allow updating
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Exclude email and password from the request data
        $data = $validator->validated();
        unset($data['email'], $data['password']);

        // Update the user with validated data (excluding email and password)
        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

    public function login(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // Attempt to authenticate the user
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'error' => 'Invalid login details',
                ], 401);
            }

            // Get the authenticated user
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            // Return success response
            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'data'=>$user,
            ], 200);
        } catch (ValidationException $e) {
            // Return validation error response
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Return generic error response
            return response()->json([
                'error' => 'Login failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

   // تسجيل الخروج
public function logout(Request $request)
{
    try {
        // Check if the user is authenticated
        if (!$request->user()) {
            return response()->json([
                'error' => 'User not authenticated',
            ], 401);
        }

        // Revoke the user's current access token
        $token = $request->user()->currentAccessToken();

        if (!$token) {
            return response()->json([
                'error' => 'No active token found',
            ], 404);
        }

        $token->delete();

        // Return success response
        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    } catch (\Exception $e) {
        // Return error response
        return response()->json([
            'error' => 'Logout failed',
            'details' => $e->getMessage()
        ], 500);
    }
}
public function delete($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    // Delete the user
    $user->delete();

    return response()->json([
        'message' => 'User deleted successfully'
    ], 200);
}
}
