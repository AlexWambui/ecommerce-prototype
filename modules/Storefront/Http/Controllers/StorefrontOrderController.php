<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Order\Models\Order;
use Modules\Order\Services\CartService;
use Modules\Delivery\Models\DeliveryLocation;

class StorefrontOrderController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function cart()
    {
        $summary = $this->cart->summary();

        return inertia('storefront/order/cart/Index', [
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'itemCount' => $summary['itemCount'],
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'exists:products,slug'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->cart->add($validated['slug'], $validated['quantity'] ?? 1);

        return redirect()->back();
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $this->cart->updateQuantity($validated['slug'], $validated['quantity']);

        return redirect()->back();
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string'],
        ]);

        $this->cart->remove($validated['slug']);

        return redirect()->back();
    }

    public function clear()
    {
        $this->cart->clear();

        return redirect()->back();
    }

    public function checkout()
    {
        $summary = $this->cart->summary();

        // Redirect to cart if empty
        if (empty($summary['items'])) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $locations = DeliveryLocation::query()
        ->where('is_active', true)
        ->with(['deliveryAreas' => fn ($q) => $q
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get(['id', 'name', 'slug']);

        /** @var User $user */
        $user = Auth::user();

        return inertia('storefront/order/checkout/Index', [
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'itemCount' => $summary['itemCount'],

            'user' => $user?->only('name', 'email'),

            'locations'   => $locations->map(fn ($l) => [
                'id'    => $l->id,
                'name'  => $l->name,
                'areas' => $l->deliveryAreas->map(fn ($a) => [
                    'id'    => $a->id,
                    'name'  => $a->name,
                    'price' => (float) $a->price,
                    'estimated_days' => $a->estimated_days,
                ]),
            ]),
        ]);
    }

    public function checkoutSuccess(string $order)
    {
        $order = Order::where('order_number', $order)->with('orderItems')->firstOrFail();

        return Inertia::render('storefront/order/checkout/Success', [
            'order' => [
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'total' => (float) $order->total_selling_price,
                'items' => $order->orderItems->map(fn ($i) => [
                    'name' => $i->product_name,
                    'price' => (float) $i->selling_price,
                    'quantity' => (float) $i->quantity,
                    'thumbnail_url' => $i->product?->thumbnail_url,
                ]),
            ],
        ]);
    }
}
