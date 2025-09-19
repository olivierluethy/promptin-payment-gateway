<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    // List user's subscriptions
    public function index(Request $request)
    {
        return $request->user()->subscriptions()->with('plan.product')->get();
    }

    // Checkout / Subscribe via Stripe
    public function checkout(Request $request, $planId)
    {
        $user = $request->user();
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

            return response()->json(['url' => $checkoutSession->url]);
        } catch (\Exception $e) {
            Log::error('Checkout session creation failed', [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Fehler beim Erstellen der Zahlungssitzung.'], 500);
        }
    }
}