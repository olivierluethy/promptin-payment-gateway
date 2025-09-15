<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;
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

        // E-Mail verschicken
        Mail::to($user->email)->send(new WelcomeMail($user));

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

        if (!$user || !Hash::check($request->password, $user->password)) {
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Benutzer mit dieser E-Mail existiert nicht.']
            ]);
        }

        // Token erzeugen
        $token = Str::random(60);

        // Token in DB speichern (password_resets)
        \DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Mail verschicken
        Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));

        return response()->json([
            'message' => 'Password reset email sent!'
        ], 200);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        // Überprüfe, ob das aktuelle Passwort korrekt ist
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Das aktuelle Passwort ist falsch.'],
            ]);
        }

        // Aktualisiere das Passwort
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Optional: Widerrufe alle bestehenden Tokens nach Passwortänderung
        $user->tokens()->delete();
        \DB::table('refresh_tokens')
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Passwort erfolgreich geändert. Bitte melde dich erneut an.',
        ]);
    }
    public function resetPasswordConfirm(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $passwordReset = \DB::table('password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>=', now()->subHours(1)) // Token valid for 1 hour
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            throw ValidationException::withMessages([
                'email' => ['Ungültiger oder abgelaufener Token.']
            ]);
        }

        $user = User::where('email', $request->email)->first();

        // Check if the new password is the same as the current password
        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Das neue Passwort darf nicht mit dem aktuellen Passwort übereinstimmen.']
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Token löschen
        \DB::table('password_resets')->where('email', $request->email)->delete();

        // Render the confirmation page
        return view('auth.password_reset_success', [
            'user' => $user
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Fetch the user's active subscription (assuming a subscriptions table)
        $subscription = \DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Mock plan data if no subscription exists (adjust based on your setup)
        $plan = null;
        if ($subscription) {
            $plan = \DB::table('plans')
                ->where('id', $subscription->plan_id)
                ->first();
        }

        return view('auth.dashboard', [
            'user' => $user,
            'plan' => $plan
        ]);
    }
}