<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            \Stripe\Webhook::constructEvent(
                $request->getContent(),
                $request->header('stripe-signature'),
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error('Invalid Stripe webhook signature', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['error' => 'Invalid webhook signature'], 403);
        }

        return $next($request);
    }
}