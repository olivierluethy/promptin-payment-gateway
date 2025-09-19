<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;

class StripeWebhookController extends CashierWebhookController
{
    public function handleCheckoutSessionCompleted(array $payload)
    {
        Log::debug('Processing checkout.session.completed', ['payload' => $payload]);

        $stripeCustomerId = $payload['data']['object']['customer'] ?? null;
        $stripeSubscriptionId = $payload['data']['object']['subscription'] ?? null;
        $priceId = $payload['data']['object']['line_items']['data'][0]['price']['id'] ?? null;

        if (!$stripeCustomerId || !$stripeSubscriptionId || !$priceId) {
            Log::error('Missing data in checkout.session.completed', [
                'stripe_customer_id' => $stripeCustomerId,
                'stripe_subscription_id' => $stripeSubscriptionId,
                'price_id' => $priceId,
                'payload' => $payload,
            ]);
            return response()->json(['error' => 'Missing required data'], 400);
        }

        $user = User::where('stripe_id', $stripeCustomerId)->first();
        if (!$user) {
            Log::error('User not found for stripe_id', [
                'stripe_id' => $stripeCustomerId,
                'payload' => $payload,
            ]);
            return response()->json(['error' => 'User not found'], 404);
        }

        $plan = Plan::where('stripe_price_id', $priceId)->first();
        if (!$plan) {
            Log::error('Plan not found for price_id', [
                'price_id' => $priceId,
                'payload' => $payload,
            ]);
            return response()->json(['error' => 'Plan not found'], 404);
        }

        $stripe = $this->stripe();
        try {
            $subscriptionStripe = $stripe->subscriptions->retrieve(
                $stripeSubscriptionId,
                ['expand' => ['items.data.price']]
            );
        } catch (\Exception $e) {
            Log::error('Failed to retrieve Stripe subscription', [
                'stripe_subscription_id' => $stripeSubscriptionId,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return response()->json(['error' => 'Failed to retrieve subscription'], 500);
        }

        try {
            Subscription::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'stripe_subscription_id' => $stripeSubscriptionId,
                ],
                [
                    'plan_id' => $plan->id,
                    'name' => 'default',
                    'stripe_price' => $plan->stripe_price_id,
                    'status' => $subscriptionStripe->status,
                    'billing_type' => $plan->default_billing_type ?? 'monthly',
                    'starts_at' => Carbon::createFromTimestamp($subscriptionStripe->current_period_start),
                    'expires_at' => Carbon::createFromTimestamp($subscriptionStripe->current_period_end),
                    'trial_ends_at' => $subscriptionStripe->trial_end ? Carbon::createFromTimestamp($subscriptionStripe->trial_end) : null,
                    'canceled_at' => $subscriptionStripe->canceled_at ? Carbon::createFromTimestamp($subscriptionStripe->canceled_at) : null,
                ]
            );

            Log::info('Subscription created/updated via checkout.session.completed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscriptionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update subscription in database', [
                'user_id' => $user->id,
                'stripe_subscription_id' => $stripeSubscriptionId,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return response()->json(['error' => 'Failed to update subscription'], 500);
        }

        return $this->successMethod();
    }

    // Weitere Methoden wie zuvor (handleInvoicePaymentSucceeded, etc.)
}