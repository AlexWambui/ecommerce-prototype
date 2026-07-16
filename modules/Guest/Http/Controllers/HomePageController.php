<?php

namespace Modules\Guest\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Modules\Product\Models\Product;
use Modules\Product\Http\Resources\ProductHomePageResource;

class HomePageController extends Controller
{
    public function index()
    {
        $new_arrivals = Product::where('is_new', true)->where('is_active', true)->with('images')->limit(4)->get();

        // dd(ProductHomePageResource::collection($new_arrivals)->resolve());

        return Inertia::render('guest/homepage/Index', [
            'new_arrivals' => ProductHomePageResource::collection($new_arrivals)->resolve()
        ]);
    }
}