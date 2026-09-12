<div align="center">
  <h1>Promptin Payment Gateway</h1>
  <p><b>Stripe payments and subscriptions backend for Promptin.</b><br/>A Laravel 12 API and web app that handles checkout, plans, subscriptions, webhooks and token-based auth.</p>
  <p>
    <a href="LICENSE"><img alt="License: MIT" src="https://img.shields.io/badge/License-MIT-blue.svg"></a>
    <img alt="Laravel 12" src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white">
    <img alt="PHP 8.2+" src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white">
    <img alt="Stripe" src="https://img.shields.io/badge/Stripe-Cashier-635BFF?logo=stripe&logoColor=white">
  </p>
</div>

---

The payment and subscription backend for the **Promptin** product. It wraps Stripe
through [Laravel Cashier](https://laravel.com/docs/billing) to create checkout
sessions, expose products and plans, manage subscriptions and process Stripe webhooks.
It ships both a token-based JSON API (authenticated with Laravel Sanctum) for external
clients and a set of server-rendered Blade views for auth and checkout flows, including
registration, login, password reset and email verification with transactional emails.

## Features

- **Stripe Checkout** via Laravel Cashier — hosted checkout sessions per plan, with
  success and cancel return pages.
- **Products, plans and subscriptions** — models and endpoints for listing products and
  plans and for viewing a user's subscriptions.
- **Stripe webhooks** — a dedicated webhook endpoint (CSRF-exempt, signature-verified
  middleware) to keep subscription state in sync.
- **Token API auth** — registration, login, logout, token refresh and token validation
  with [Laravel Sanctum](https://laravel.com/docs/sanctum) personal access tokens.
- **Account flows** — password reset, email-change verification and password change.
- **Transactional email** — welcome, password-reset and email-verification mails
  (Blade-templated).
- **Web UI** — Blade views for login, registration, dashboard, settings and the
  checkout result pages.

## Tech stack

- [Laravel 12](https://laravel.com/) (PHP 8.2+)
- [Laravel Cashier](https://laravel.com/docs/billing) 15 + [stripe-php](https://github.com/stripe/stripe-php) 16
- [Laravel Sanctum](https://laravel.com/docs/sanctum) 4 for API tokens
- Blade views, Vite + Tailwind for assets
- SQLite by default (any Laravel-supported database works); database sessions, cache and queue

## Getting started

### Requirements

- PHP 8.2+ and [Composer](https://getcomposer.org/)
- Node.js and npm (for front-end assets)
- A [Stripe](https://stripe.com/) account with API keys

### 1. Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Configure environment

Set your database and mail settings in `.env`. By default the app uses SQLite — create
the file with:

```bash
touch database/database.sqlite
```

Add your Stripe credentials (read by Cashier via `config/services.php` /
`config/cashier`):

```dotenv
STRIPE_KEY=pk_test_...              # publishable key
STRIPE_SECRET=sk_test_...           # secret key
STRIPE_WEBHOOK_SECRET=whsec_...     # from `stripe listen` or the dashboard
CASHIER_CURRENCY=chf                # optional, defaults to usd
```

Configure the mailer for the account and verification emails (during development
`MAIL_MAILER=log` writes them to `storage/logs`).

### 3. Migrate

```bash
php artisan migrate
```

This creates the application tables plus the Cashier and Sanctum tables. A SQL schema
snapshot is also available under `database/`.

### 4. Run

```bash
composer run dev      # serves the app, queue worker, logs and Vite together
# or individually:
php artisan serve
npm run dev
```

### 5. Forward Stripe webhooks (local development)

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

Use the signing secret it prints as `STRIPE_WEBHOOK_SECRET`.

## API endpoints

Base path: `/api`. Authenticated routes require a Sanctum bearer token
(`Authorization: Bearer <token>`).

| Method | Path | Auth | Purpose |
|---|---|---|---|
| POST | `/register` | – | Create an account, return a token |
| POST | `/login` | – | Log in, return a token |
| POST | `/password-reset` | – | Request a password reset |
| POST | `/refresh` | – | Refresh an access token |
| GET | `/products` | – | List products and plans |
| GET | `/me` | token | Current user |
| POST | `/logout` | token | Revoke the current token |
| GET | `/validate-token` | token | Validate the current token |
| GET | `/subscriptions` | token | List the user's subscriptions |
| POST | `/checkout/{planId}` | token | Start a Stripe checkout for a plan |
| POST | `/change-password` | token | Change password |

Web routes include `/login`, `/register`, `/dashboard`, `/settings`, the
`/checkout/{planId}` flow with `/success` and `/cancel`, the password-reset pages, and
`POST /stripe/webhook` for Stripe events.

## License

Released under the [MIT License](LICENSE) © 2026 Olivier Lüthy. You're free to use, modify and distribute this
software, including commercially, as long as the copyright notice and license are included.

## Author

Built by **Olivier Lüthy** — [GitHub](https://github.com/olivierluethy).
