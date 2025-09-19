<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WebhookController extends CashierWebhookController
{
    /**
     * Nutzt Cashier, um das Webhook Event zu verarbeiten.
     * Keine manuelle Stripe::constructEvent-Logik nötig!
     */
    public function handle($request)
    {
        var_dump($request);die;
        return parent::handleWebhook($request);
    }

    /**
     * Handle checkout.session.completed (Subscription oder One-Time Payment)
     */
    public function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'];

        if (empty($session['subscription'])) {
            Log::info('CheckoutSessionCompleted: One-Time Payment', ['session' => $session]);
            return $this->successMethod();
        }

        $user = $this->getUserByStripeId($session['customer']);
        if (!$user) {
            Log::warning('CheckoutSessionCompleted: User not found', ['session' => $session]);
            return $this->missingUser($payload);
        }

        $stripeSubscription = $this->stripe->subscriptions->retrieve($session['subscription']);
        $priceId = $stripeSubscription->items->data[0]->price->id;

        $plan = Plan::where('stripe_price_id', $priceId)->first();
        if (!$plan) {
            Log::error('CheckoutSessionCompleted: Plan not found', ['priceId' => $priceId, 'user_id' => $user->id]);
            return $this->invalidPayload('Plan not found');
        }

        Subscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ],
            [
                'name' => 'default',
                'stripe_subscription_id' => $stripeSubscription->id,
                'stripe_price' => $plan->stripe_price_id,
                'status' => $stripeSubscription->status,
                'billing_type' => $plan->default_billing_type,
                'starts_at' => Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'expires_at' => Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                'trial_ends_at' => $stripeSubscription->trial_end ? Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                'canceled_at' => $stripeSubscription->canceled_at ? Carbon::createFromTimestamp($stripeSubscription->canceled_at) : null,
            ]
        );

        Log::info('CheckoutSessionCompleted: Subscription saved', [
            'user_id' => $user->id,
            'subscription_id' => $stripeSubscription->id
        ]);

        return $this->successMethod();
    }

    /**
     * Handle customer.subscription.created
     */
    public function handleCustomerSubscriptionCreated(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if (!$user) {
            return $this->missingUser($payload);
        }

        $priceId = $payload['data']['object']['items']['data'][0]['price']['id'];
        $plan = Plan::where('stripe_price_id', $priceId)->first();
        if (!$plan) {
            return $this->invalidPayload('Plan not found');
        }

        Subscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ],
            [
                'name' => 'default',
                'stripe_subscription_id' => $payload['data']['object']['id'],
                'stripe_price' => $plan->stripe_price_id,
                'status' => $payload['data']['object']['status'],
                'billing_type' => $plan->default_billing_type,
                'starts_at' => Carbon::createFromTimestamp($payload['data']['object']['current_period_start']),
                'expires_at' => Carbon::createFromTimestamp($payload['data']['object']['current_period_end']),
                'trial_ends_at' => $payload['data']['object']['trial_end'] ? Carbon::createFromTimestamp($payload['data']['object']['trial_end']) : null,
                'canceled_at' => $payload['data']['object']['canceled_at'] ? Carbon::createFromTimestamp($payload['data']['object']['canceled_at']) : null,
            ]
        );

        return $this->successMethod();
    }

    /**
     * Handle customer.subscription.updated
     */
    public function handleCustomerSubscriptionUpdated(array $payload)
    {
        return $this->handleCustomerSubscriptionCreated($payload);
    }

    /**
     * Handle customer.subscription.deleted
     */
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        $subscription = Subscription::where('stripe_subscription_id', $payload['data']['object']['id'])->first();
        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => Carbon::now(),
                'expires_at' => Carbon::createFromTimestamp($payload['data']['object']['current_period_end']),
            ]);
        }

        return $this->successMethod();
    }
}
