<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Mail\EmailChangeVerificationMail;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['Die Anmeldedaten sind ungültig.'],
        ]);
    }

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
            'password' => Hash::make($request->password),
        ]);

        Mail::to($user->email)->send(new WelcomeMail($user));

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $subscription = DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        $plan = $subscription ? DB::table('plans')
            ->where('id', $subscription->plan_id)
            ->first() : null;

        return view('auth.dashboard', [
            'user' => $user,
            'plan' => $plan,
        ]);
    }

    public function settings(Request $request)
    {
        return view('auth.settings', [
            'user' => $request->user(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;

        if ($user->email !== $request->email) {
            $token = Str::random(60);
            DB::table('email_verifications')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'new_email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $verificationUrl = route('settings.verifyEmail', [
                'token' => $token,
                'email' => $request->email,
            ]);

            Mail::to($request->email)->send(new EmailChangeVerificationMail($user, $request->email, $verificationUrl));
            $user->save();

            return redirect()->route('settings')->with('status', 'Eine Bestätigungs-E-Mail wurde an deine neue Adresse gesendet. Bitte bestätige, um die Änderung abzuschließen.');
        }

        $user->save();

        $request->session()->put('user', $user);

        return redirect()->route('settings')->with('status', 'Profil erfolgreich aktualisiert.');
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = $request->user();

        $verification = DB::table('email_verifications')
            ->where('user_id', $user->id)
            ->where('new_email', $request->email)
            ->where('created_at', '>=', now()->subHours(24))
            ->first();

        if (!$verification || !Hash::check($request->token, $verification->token)) {
            return redirect()->route('settings')->with('error', 'Ungültiger oder abgelaufener Bestätigungslink.');
        }

        $user->email = $verification->new_email;
        $user->save();

        DB::table('email_verifications')->where('user_id', $user->id)->delete();

        $request->session()->put('user', $user);

        return redirect()->route('settings')->with('status', 'E-Mail-Adresse erfolgreich geändert.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Das aktuelle Passwort ist falsch.'],
            ]);
        }

        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Das neue Passwort darf nicht mit dem aktuellen Passwort übereinstimmen.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('settings')->with('status', 'Passwort erfolgreich geändert.');
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        DB::table('subscriptions')->where('user_id', $user->id)->delete();

        if ($user->stripe_id) {
            try {
                $user->deleteStripeCustomer();
            } catch (\Exception $e) {
                Log::error('Failed to delete Stripe customer', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Dein Konto wurde erfolgreich gelöscht.');
    }

    public function resetPasswordConfirm(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>=', now()->subHours(1))
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            throw ValidationException::withMessages([
                'email' => ['Ungültiger oder abgelaufener Token.']
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Das neue Passwort darf nicht mit dem aktuellen Passwort übereinstimmen.']
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return view('auth.password_reset_success', ['user' => $user]);
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot_password');
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

        $token = Str::random(60);
        \DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));

        return redirect()->back()->with('status', 'Password reset email sent!');
    }

    public function checkout(Request $request, $planId)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Bitte melde dich an, um fortzufahren.');
        }

        $plan = Plan::findOrFail($planId);

        try {
            $checkoutSession = $user->newSubscription('default', $plan->stripe_price_id)
                ->checkout([
                    'success_url' => config('app.url') . '/success?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => config('app.url') . '/cancel',
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                    ],
                ]);

            return redirect($checkoutSession->url);
        } catch (\Exception $e) {
            Log::error('Checkout session creation failed', [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('dashboard')->with('error', 'Fehler beim Erstellen der Zahlungssitzung.');
        }
    }
}