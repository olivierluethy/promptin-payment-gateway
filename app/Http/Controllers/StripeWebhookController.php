<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Rohes JSON Payload von Stripe verwenden
        $payload = json_decode($request->getContent(), true);
        Log::info('Stripe Webhook received:', $payload);

        $eventType = $payload['type'] ?? null;

        switch ($eventType) {

            case 'checkout.session.completed':
                $session = $payload['data']['object'];

                $email = $session['customer_email'] ?? null;
                $stripeCustomerId = $session['customer'] ?? null;
                $priceId = $session['display_items'][0]['price']['id'] ?? null;

                if (!$email || !$stripeCustomerId || !$priceId) {
                    Log::error('Missing data in Stripe session');
                    break;
                }

                // User erstellen, falls nicht vorhanden
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $session['customer_details']['name'] ?? $email,
                        'password' => Hash::make('temporary123'), // temporäres Passwort
                        'stripe_customer_id' => $stripeCustomerId,
                    ]
                );

                // Plan anhand Stripe Price ID ermitteln
                $plan = Plan::where('stripe_price_id', $priceId)->first();

                // Subscription erstellen, falls Plan existiert
                if ($plan) {
                    Subscription::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'plan_id' => $plan->id,
                        ],
                        [
                            'status' => 'active',
                            'starts_at' => now(),
                            'expires_at' => now()->addMonth(), // oder addYear() je nach Plan
                        ]
                    );
                }
                break;

            case 'invoice.payment_succeeded':
                $customerId = $payload['data']['object']['customer'] ?? null;

                $subscription = Subscription::whereHas('user', function($q) use ($customerId) {
                    $q->where('stripe_customer_id', $customerId);
                })->first();

                if ($subscription) {
                    $subscription->status = 'active';
                    $subscription->expires_at = now()->addMonth();
                    $subscription->save();
                }
                break;

            case 'invoice.payment_failed':
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
