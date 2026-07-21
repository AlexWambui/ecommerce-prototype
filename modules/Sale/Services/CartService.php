<?php

namespace Modules\Sale\Services;

use Modules\Sale\Models\Cart;
use Modules\Sale\Models\CartItem;
use Modules\Product\Models\Product;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartService
{
    protected const SESSION_TOKEN_COOKIE = 'cart_session_token';
    protected const COOKIE_EXPIRY_DAYS = 30;

    public function getCart(): Cart
    {
        $sessionToken = $this->getSessionToken();
        $userId = Auth::id();

        $cart = Cart::where(function ($query) use ($sessionToken, $userId) {
            if ($userId) {
                $query->where('user_id', $userId);
            }
            $query->orWhere('session_token', $sessionToken);
        })->first();

        if (!$cart) {
            $cart = $this->createNewCart($sessionToken);
        }

        return $cart;
    }

    public function addItem(int $productId, int $quantity = 1, array $attributes = []): CartItem
    {
        $product = Product::findOrFail($productId);
        $cart = $this->getCart();

        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->where('attributes', json_encode($attributes))
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->subtotal = $existingItem->quantity * $existingItem->unit_price;
            $existingItem->total = $existingItem->subtotal + $existingItem->tax - $existingItem->discount;
            $existingItem->save();
            $cart->recalculate();
            return $existingItem;
        }

        $unitPrice = $product->price ?? 0;
        $subtotal = $unitPrice * $quantity;

        $item = $cart->items()->create([
            'product_id' => $productId,
            'product_name' => $product->name ?? 'Unknown Product',
            'product_sku' => $product->sku ?? 'N/A',
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'attributes' => $attributes,
            'subtotal' => $subtotal,
            'tax' => 0,
            'discount' => 0,
            'total' => $subtotal,
        ]);

        $cart->recalculate();
        return $item;
    }

    public function updateItemQuantity(int $itemId, int $quantity): CartItem
    {
        $item = CartItem::findOrFail($itemId);
        $this->ensureCartOwnership($item->cart);

        $item->quantity = $quantity;
        $item->subtotal = $quantity * $item->unit_price;
        $item->total = $item->subtotal + $item->tax - $item->discount;
        $item->save();
        $item->cart->recalculate();

        return $item;
    }

    public function removeItem(int $itemId): void
    {
        $item = CartItem::findOrFail($itemId);
        $this->ensureCartOwnership($item->cart);
        $cart = $item->cart;
        $item->delete();
        $cart->recalculate();
    }

    public function clearCart(): void
    {
        $cart = $this->getCart();
        $cart->clear();
    }

    public function mergeGuestCartWithUser($user): void
    {
        $sessionToken = $this->getSessionToken();
        
        $guestCart = Cart::where('session_token', $sessionToken)
            ->whereNull('user_id')
            ->first();

        if (!$guestCart || $guestCart->isEmpty()) {
            return;
        }

        $userCart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['session_token' => Str::random(40)]
        );

        $userCart->merge($guestCart);

        if ($guestCart->email) {
            $user->email = $guestCart->email;
            $user->save();
        }
    }

    protected function getSessionToken(): string
    {
        if ($token = Cookie::get(self::SESSION_TOKEN_COOKIE)) {
            return $token;
        }

        $token = Str::random(40);
        Cookie::queue(self::SESSION_TOKEN_COOKIE, $token, self::COOKIE_EXPIRY_DAYS * 24 * 60);
        
        return $token;
    }

    protected function createNewCart(string $sessionToken): Cart
    {
        return Cart::create([
            'session_token' => $sessionToken,
            'user_id' => Auth::id(),
            'expires_at' => now()->addDays(self::COOKIE_EXPIRY_DAYS),
        ]);
    }

    protected function ensureCartOwnership(Cart $cart): void
    {
        $userId = Auth::id();
        $sessionToken = $this->getSessionToken();

        if ($cart->user_id && $cart->user_id !== $userId) {
            abort(403, 'You do not own this cart');
        }

        if (!$cart->user_id && $cart->session_token !== $sessionToken) {
            abort(403, 'You do not own this cart');
        }
    }
}