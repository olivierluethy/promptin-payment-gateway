<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

use App\Models\User;

class PasswordResetController extends Controller
{
    // Formular "Passwort vergessen?"
    public function requestForm()
    {
        return view('auth.forgot_password');
    }

    // Mail mit Link senden
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Diese E-Mail-Adresse ist nicht registriert.']
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

        return back()->with('status', 'Wir haben dir einen Link zum Zurücksetzen geschickt!');
    }

    // Formular neues Passwort mit Token
    public function resetForm(string $token)
    {
        return view('auth.reset_password', ['token' => $token]);
    }

    // Neues Passwort speichern
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
