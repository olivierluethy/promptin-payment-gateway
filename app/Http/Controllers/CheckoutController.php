<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Models\Plan;

class CheckoutController extends Controller
{
    public function checkout(Request $request, $planId)
    {
        $user = $request->user();

        $plan = Plan::findOrFail($planId);

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

        // Checkout Session erstellen
        $session = $stripe->checkout->sessions->create([
            'customer' => $user->stripe_customer_id,
            'line_items' => [
                [
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ]
            ],
            'mode' => 'subscription',
            'success_url' => config('app.url') . '/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.url') . '/cancel',
        ]);

        return response()->json([
            'checkout_url' => $session->url,
        ]);
    }

    /**
     * Success Page für Frontend
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        $stripe = new StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['subscription']
        ]);

        $user = $request->user();
        $plan = Plan::where('stripe_price_id', $session->line_items->data[0]->price->id ?? null)->first();

        if ($plan && $session->subscription) {
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => null, // ggf. aus Stripe-Daten ableiten
            ]);
        }

        return view('checkout.success', [
            'message' => 'Vielen Dank für Ihren Einkauf!',
            'session_id' => $sessionId,
        ]);
    }


    /**
     * Cancel Page (optional)
     */
    public function cancel()
    {
        return view('checkout.cancel', [
            'message' => 'Der Checkout wurde abgebrochen.',
        ]);
    }
}
