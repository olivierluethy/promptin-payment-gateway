<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;

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

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        $checkoutSession = $user->checkoutCharge($plan->stripe_price_id);

        return response()->json(['url' => $checkoutSession->url]);
    }
}
