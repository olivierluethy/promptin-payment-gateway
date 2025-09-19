<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use App\Models\Plan;
use App\Models\Subscription;

class CheckoutController extends Controller
{
    public function checkout(Request $request, $planId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Bitte melde dich an, um fortzufahren.'], 401);
        }

        $plan = Plan::findOrFail($planId);

        if (!$user->stripe_customer_id) {
            // Create a Stripe customer if it doesn't exist
            $stripeCustomer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);

            // Save the Stripe customer ID to the user's record
            $user->stripe_customer_id = $stripeCustomer->id;
            $user->save();
        }

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

            return response()->json([
                'checkout_url' => $checkoutSession->url,
            ]);
        } catch (\Exception $e) {
            Log::error('Checkout session creation failed', [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Fehler beim Erstellen der Zahlungssitzung.'], 500);
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('dashboard')->with('error', 'Ungültige Zahlungssitzung.');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Bitte melde dich an, um fortzufahren.');
        }

        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
            if ($session->customer !== $user->stripe_id) {
                Log::error('Session customer mismatch', [
                    'user_id' => $user->id,
                    'session_customer' => $session->customer,
                    'user_stripe_id' => $user->stripe_id,
                ]);
                return redirect()->route('dashboard')->with('error', 'Ungültige Zahlungssitzung.');
            }

            // Subscription wird über Webhook aktualisiert, daher hier nur Bestätigung anzeigen
            return view('checkout.success', [
                'message' => 'Vielen Dank für Ihren Einkauf! Dein Abonnement ist aktiv.',
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Checkout success processing failed', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('dashboard')->with('error', 'Fehler beim Verarbeiten der Zahlung.');
        }
    }

    public function cancel()
    {
        return view('checkout.cancel', [
            'message' => 'Der Checkout wurde abgebrochen.',
        ]);
    }
}