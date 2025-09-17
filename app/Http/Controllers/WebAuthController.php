<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use App\Models\Plan;

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

        // Beispiel: aktive Subscription aus DB laden
        $subscription = DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $plan = null;
        if ($subscription) {
            $plan = DB::table('plans')
                ->where('id', $subscription->plan_id)
                ->first();
        }

        return view('auth.dashboard', [
            'user' => $user,
            'plan' => $plan,
        ]);
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

    // Formular anzeigen
    public function showForgotPasswordForm()
    {
        return view('auth.forgot_password');
    }

    // API zum Zurücksetzen des Passworts
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
        Mail::to($user->email)->send(new PasswordResetController($token, $user->email));

        return redirect()->back()->with('status', 'Password reset email sent!');
    }

    public function checkout(Request $request, $planId)
    {
        // Benutzer aus der Session holen
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Bitte melde dich an, um fortzufahren.');
        }

        // Plan aus der Datenbank holen
        $plan = Plan::findOrFail($planId);

        // Stripe-Client initialisieren
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        // Stripe Customer anlegen, falls nicht vorhanden
        if (!$user->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        // Checkout-Session erstellen
        $session = $stripe->checkout->sessions->create([
            'customer' => $user->stripe_customer_id,
            'line_items' => [
                [
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => config('app.url') . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.url') . '/cancel',
        ]);

        // Weiterleitung zur Stripe-Checkout-Seite
        return redirect($session->url);
    }
}
