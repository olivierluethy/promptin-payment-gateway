<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use App\Models\Plan;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('Stripe Webhook received:', $payload);

        $eventType = $payload['type'] ?? null;

        switch ($eventType) {
            case 'invoice.payment_succeeded':
                $customerId = $payload['data']['object']['customer'] ?? null;
                $subscriptionId = $payload['data']['object']['subscription'] ?? null;

                $subscription = Subscription::whereHas('user', function($q) use ($customerId) {
                    $q->where('stripe_customer_id', $customerId);
                })->first();

                if ($subscription) {
                    $subscription->status = 'active';
                    $subscription->expires_at = now()->addMonth(); // adjust if yearly
                    $subscription->save();
                }
                break;

            case 'invoice.payment_failed':
                // Update subscription status
                $customerId = $payload['data']['object']['customer'] ?? null;
                $subscription = Subscription::whereHas('user', function($q) use ($customerId) {
                    $q->where('stripe_customer_id', $customerId);
                })->first();

                if ($subscription) {
                    $subscription->status = 'past_due';
                    $subscription->save();
                }
                break;

            case 'customer.subscription.deleted':
                $customerId = $payload['data']['object']['customer'] ?? null;
                $subscription = Subscription::whereHas('user', function($q) use ($customerId) {
                    $q->where('stripe_customer_id', $customerId);
                })->first();

                if ($subscription) {
                    $subscription->status = 'canceled';
                    $subscription->save();
                }
                break;

            default:
                Log::info('Unhandled Stripe event: ' . $eventType);
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
