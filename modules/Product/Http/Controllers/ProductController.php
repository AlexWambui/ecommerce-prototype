<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index()
    {
        return inertia('app/products/products/Index');
    }
}