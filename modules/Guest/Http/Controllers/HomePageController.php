<?php

namespace Modules\Guest\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class HomePageController extends Controller
{
    public function index()
    {
        $dummyProducts = [
            [
                'id' => 1,
                'name' => 'Premium Wireless Headphones',
                'slug' => 'premium-wireless-headphones',
                'description' => 'High-quality wireless headphones with noise cancellation and 30-hour battery life.',
                'price' => 199.99,
                'compare_price' => 299.99,
                'stock' => 45,
                'sku' => 'WH-001',
                'category' => 'Electronics',
                'tags' => ['wireless', 'audio', 'headphones'],
                'images' => [
                    ['url' => '/assets/images/products/headphones-1.jpg', 'alt' => 'Black headphones'],
                    ['url' => '/assets/images/products/headphones-2.jpg', 'alt' => 'Headphones side view'],
                ],
                'rating' => 4.8,
                'reviews_count' => 127,
                'is_featured' => true,
                'is_new' => false,
                'is_on_sale' => true,
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Smart Fitness Watch',
                'slug' => 'smart-fitness-watch',
                'description' => 'Track your fitness goals with this advanced smart watch featuring heart rate monitor and GPS.',
                'price' => 149.50,
                'compare_price' => 199.00,
                'stock' => 23,
                'sku' => 'FW-002',
                'category' => 'Wearables',
                'tags' => ['fitness', 'smart', 'watch'],
                'images' => [
                    ['url' => '/storage/product-images/test-1.jpg', 'alt' => 'Blue water bottle'],
                    ['url' => '/storage/product-images/test-2.jpg', 'alt' => 'Blue water bottle'],
                ],
                'rating' => 4.5,
                'reviews_count' => 89,
                'is_featured' => true,
                'is_new' => true,
                'is_on_sale' => false,
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Organic Cotton T-Shirt',
                'slug' => 'organic-cotton-t-shirt',
                'description' => 'Comfortable and sustainable organic cotton t-shirt, perfect for everyday wear.',
                'price' => 29.99,
                'compare_price' => null,
                'stock' => 120,
                'sku' => 'CS-003',
                'category' => 'Clothing',
                'tags' => ['organic', 'sustainable', 'cotton'],
                'images' => [
                    ['url' => '/assets/images/products/shirt-1.jpg', 'alt' => 'White cotton t-shirt'],
                ],
                'rating' => 4.2,
                'reviews_count' => 56,
                'is_featured' => false,
                'is_new' => true,
                'is_on_sale' => false,
                'created_at' => now()->subDays(10),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Professional Chef Knife Set',
                'slug' => 'professional-chef-knife-set',
                'description' => '5-piece professional chef knife set with premium stainless steel and ergonomic handles.',
                'price' => 89.99,
                'compare_price' => 129.99,
                'stock' => 15,
                'sku' => 'CK-004',
                'category' => 'Kitchen',
                'tags' => ['kitchen', 'knife', 'cooking'],
                'images' => [
                    ['url' => '/assets/images/products/knife-1.jpg', 'alt' => 'Chef knife set'],
                    ['url' => '/assets/images/products/knife-2.jpg', 'alt' => 'Knife close-up'],
                ],
                'rating' => 4.9,
                'reviews_count' => 203,
                'is_featured' => true,
                'is_new' => false,
                'is_on_sale' => true,
                'created_at' => now()->subDays(15),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Wireless Charging Pad',
                'slug' => 'wireless-charging-pad',
                'description' => 'Fast wireless charging pad compatible with all Qi-enabled devices. Sleek and portable design.',
                'price' => 39.99,
                'compare_price' => null,
                'stock' => 78,
                'sku' => 'WC-005',
                'category' => 'Electronics',
                'tags' => ['wireless', 'charging', 'accessory'],
                'images' => [
                    ['url' => '/assets/images/products/charger-1.jpg', 'alt' => 'Wireless charging pad'],
                ],
                'rating' => 4.3,
                'reviews_count' => 34,
                'is_featured' => false,
                'is_new' => true,
                'is_on_sale' => false,
                'created_at' => now()->subDays(3),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Eco-Friendly Water Bottle',
                'slug' => 'eco-friendly-water-bottle',
                'description' => 'Insulated stainless steel water bottle that keeps drinks hot for 12 hours or cold for 24 hours.',
                'price' => 34.95,
                'compare_price' => 45.00,
                'stock' => 95,
                'sku' => 'WB-006',
                'category' => 'Home & Living',
                'tags' => ['eco-friendly', 'bottle', 'sustainable'],
                'images' => [
                    ['url' => '/storage/product-images/test-1.jpg', 'alt' => 'Blue water bottle'],
                    ['url' => '/storage/product-images/test-2.jpg', 'alt' => 'Blue water bottle'],
                ],
                'rating' => 4.7,
                'reviews_count' => 158,
                'is_featured' => true,
                'is_new' => false,
                'is_on_sale' => true,
                'created_at' => now()->subDays(8),
                'updated_at' => now(),
            ],
        ];

        // Featured products
        $featuredProducts = array_filter($dummyProducts, fn($product) => $product['is_featured']);
        
        // New arrivals
        $newProducts = array_filter($dummyProducts, fn($product) => $product['is_new']);
        
        // On sale products
        $saleProducts = array_filter($dummyProducts, fn($product) => $product['is_on_sale']);

        return Inertia::render('guest/homepage/Index', [
            'products' => $dummyProducts,
            'featuredProducts' => array_values($featuredProducts),
            'newProducts' => array_values($newProducts),
            'saleProducts' => array_values($saleProducts),
        ]);
    }
}