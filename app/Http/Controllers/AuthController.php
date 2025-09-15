<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('api-token')->plainTextToken;
        $refreshToken = $this->generateRefreshToken($user);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600 // Access token expires in 1 hour
        ], 201);
    }

    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::make($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.']
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $refreshToken = $this->generateRefreshToken($user);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        \DB::table('refresh_tokens')
            ->where('user_id', $request->user()->id)
            ->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // Get current user
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Refresh token
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        $refreshToken = \DB::table('refresh_tokens')
            ->where('token', $request->refresh_token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$refreshToken) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Invalid or expired refresh token.']
            ]);
        }

        $user = User::find($refreshToken->user_id);
        if (!$user) {
            throw ValidationException::withMessages([
                'refresh_token' => ['User not found.']
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $newRefreshToken = $this->generateRefreshToken($user, $refreshToken->id);

        return response()->json([
            'access_token' => $token,
            'refresh_token' => $newRefreshToken,
            'expires_in' => 3600
        ]);
    }

    // Validate token
    public function validateToken(Request $request)
    {
        return response()->json(['valid' => true, 'user' => $request->user()]);
    }

    // Generate refresh token
    protected function generateRefreshToken($user, $existingTokenId = null)
    {
        $token = Str::random(60);
        $expiresAt = Carbon::now()->addDays(30); // Refresh token valid for 30 days

        if ($existingTokenId) {
            \DB::table('refresh_tokens')
                ->where('id', $existingTokenId)
                ->update([
                    'token' => $token,
                    'expires_at' => $expiresAt,
                    'updated_at' => Carbon::now()
                ]);
        } else {
            \DB::table('refresh_tokens')->insert([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => $expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        return $token;
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset email sent!'], 200);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)]
        ]);
    }
}