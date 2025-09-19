<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePromptinApiDatabaseAndTables extends Migration
{
    public function up()
    {
        // Datenbank erstellen
        DB::statement('CREATE DATABASE IF NOT EXISTS promptin_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // Auf die Datenbank wechseln
        DB::statement('USE promptin_api');

        // Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('two_factor_secret', 255)->nullable();
            $table->string('stripe_id', 255)->nullable()->index();
            $table->timestamps();
        });

        // Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Plans Table
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('name', 50);
            $table->string('stripe_price_id', 255);
            $table->enum('default_billing_type', ['monthly', 'yearly'])->default('monthly');
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Subscriptions Table
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->string('stripe_subscription_id', 255)->unique()->nullable();
            $table->enum('status', ['active', 'canceled', 'past_due', 'trialing', 'incomplete', 'incomplete_expired', 'unpaid', 'paused'])->default('incomplete');
            $table->enum('billing_type', ['monthly', 'yearly'])->default('monthly');
            $table->dateTime('starts_at');
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->index('stripe_subscription_id', 'idx_stripe_subscription_id');
        });

        // Password Resets Table
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email', 255);
            $table->string('token', 255);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->index('email');
        });

        // Email Verifications Table
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('new_email', 255);
            $table->string('token', 255);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'new_email']);
        });

        // Personal Access Tokens Table
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['tokenable_type', 'tokenable_id'], 'personal_access_tokens_tokenable_type_tokenable_id_index');
        });

        // Refresh Tokens Table
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('token', 255)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Sessions Table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');
            $table->index('user_id', 'sessions_user_id_index');
            $table->index('last_activity', 'sessions_last_activity_index');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Tabellen löschen
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('email_verifications');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('products');
        Schema::dropIfExists('users');

        // Datenbank löschen
        DB::statement('DROP DATABASE IF EXISTS promptin_api');
    }
}