<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    // =====================
    // Relationships
    // =====================
    public function plans()
    {
        return $this->hasMany(Plan::class);
    }
}
