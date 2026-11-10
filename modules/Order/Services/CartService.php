<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\Session;
use Modules\Product\Models\Product;

class CartService
{
    protected const KEY = 'cart';

    /** @return array<int, array{slug:string,name:string,price:float,quantity:int}> */
    public function all(): array
    {
        return Session::get(self::KEY, []);
    }

    public function add(string $slug, int $quantity = 1): void
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $cart = $this->all();

        foreach ($cart as &$item) {
            if ($item['slug'] === $product->slug) {
                $item['quantity'] += $quantity;
                Session::put(self::KEY, $cart);

                return;
            }
        }
        unset($item);

        $cart[] = [
            'thumbnail_url' => $product->thumbnail_url,
            'slug' => $product->slug,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => $quantity,
        ];

        Session::put(self::KEY, $cart);
    }

    public function updateQuantity(string $slug, int $quantity): void
    {
        $cart = $this->all();

        foreach ($cart as $index => $item) {
            if ($item['slug'] === $slug) {
                if ($quantity < 1) {
                    unset($cart[$index]);
                } else {
                    $cart[$index]['quantity'] = $quantity;
                }
                break;
            }
        }

        Session::put(self::KEY, array_values($cart));
    }

    public function remove(string $slug): void
    {
        $cart = array_values(array_filter(
            $this->all(),
            fn ($item) => $item['slug'] !== $slug
        ));

        Session::put(self::KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }

    /** @return array{items: array, subtotal: float, itemCount: int} */
    public function summary(): array
    {
        $items = $this->all();

        $subtotal = array_reduce(
            $items,
            fn ($carry, $item) => $carry + ($item['price'] * $item['quantity']),
            0.0
        );

        $itemCount = array_sum(array_column($items, 'quantity'));

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'itemCount' => $itemCount,
        ];
    }
}
