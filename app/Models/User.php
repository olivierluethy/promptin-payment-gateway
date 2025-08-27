<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'stripe_customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'stripe_customer_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // =====================
    // Relationships
    // =====================
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
