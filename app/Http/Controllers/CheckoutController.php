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

        if (!$user->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

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

}
