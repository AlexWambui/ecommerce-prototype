<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Order\Actions\CreateEcommerceOrderAction;
use Modules\Order\Http\Requests\EcommerceOrderRequest;
use Modules\Order\Services\CartService;

// use Modules\Order\Http\Resources\OrderResource;

class EcommerceOrderController extends Controller
{
    public function __construct(
        protected CreateEcommerceOrderAction $createEcommerceOrder,
        protected CartService $cart,
    ) {}

    /**
     * Place an order from the public storefront.
     *
     * This endpoint is publicly accessible. Guests may check out
     * without an account; the actor will be null in that case and
     * the order's audit trail will be marked is_system = true.
     */
    public function store(EcommerceOrderRequest $request)
    {
        $summary = $this->cart->summary();

        if (empty($summary['items'])) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        try {
            $order = $this->createEcommerceOrder->execute(
                $request->validated(),
                Auth::user(),
            );
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['cart' => $e->getMessage()]);
        }

        $this->cart->clear();

        return to_route('checkout.success', $order->order_number);

        // // Return the created order as JSON. The frontend can then redirect
        // // to its own confirmation page using the order uuid / number.
        // return (new OrderResource($order))
        //     ->response()
        //     ->setStatusCode(201);

        // return to_route('shop.orders.confirmation', $order);
    }
}
