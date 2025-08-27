<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    // List all products with their plans
    public function index()
    {
        return Product::with('plans')->get();
    }
}
