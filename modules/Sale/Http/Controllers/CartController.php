<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Sale\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        $cart->load('items.product');

        return view('sale::cart.index', compact('cart'));
    }

    public function getCartData()
    {
        $cart = $this->cartService->getCart();
        $cart->load('items.product');

        return response()->json([
            'success' => true,
            'data' => $this->formatCartData($cart),
        ]);
    }

    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:999',
            'attributes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = \Modules\Product\Models\Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if (isset($product->stock) && $product->stock < ($request->quantity ?? 1)) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available'
            ], 422);
        }

        $item = $this->cartService->addItem(
            $request->product_id,
            $request->quantity ?? 1,
            $request->attributes ?? []
        );

        $cart = $this->cartService->getCart();

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => $this->formatCartData($cart),
        ]);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item = $this->cartService->updateItemQuantity($itemId, $request->quantity);
        $cart = $this->cartService->getCart();

        return response()->json([
            'success' => true,
            'message' => 'Item updated',
            'data' => $this->formatCartData($cart),
        ]);
    }

    public function removeItem(int $itemId)
    {
        $this->cartService->removeItem($itemId);
        $cart = $this->cartService->getCart();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => $this->formatCartData($cart),
        ]);
    }

    public function clear()
    {
        $this->cartService->clearCart();
        $cart = $this->cartService->getCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'data' => $this->formatCartData($cart),
        ]);
    }

    public function count()
    {
        $cart = $this->cartService->getCart();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $cart->getItemCount(),
                'total' => $cart->total,
            ]
        ]);
    }

    protected function formatCartData($cart): array
    {
        return [
            'id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'attributes' => $item->attributes,
                    'subtotal' => $item->subtotal,
                    'total' => $item->total,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name ?? 'Unknown',
                        'slug' => $item->product->slug ?? null,
                        'image' => $item->product->image ?? null,
                        'stock' => $item->product->stock ?? 0,
                    ] : null,
                ];
            }),
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'shipping' => $cart->shipping,
            'discount' => $cart->discount,
            'total' => $cart->total,
            'items_count' => $cart->getItemCount(),
            'coupon_code' => $cart->coupon_code,
        ];
    }
}