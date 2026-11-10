<?php

namespace Modules\Order\Actions;

use RuntimeException;
use Modules\Order\Enums\OrderStatusEnum;
use Modules\Order\Models\Order;
use Modules\Order\Services\CartService;
use Modules\Order\Services\OrderService;
use Modules\Product\Models\Product;
use Modules\User\Models\User;
use Modules\Delivery\Models\DeliveryArea;
use Modules\Order\Enums\DeliveryStatusEnum;

class CreateEcommerceOrderAction
{
    public function __construct(
        protected OrderService $orderService,
        protected CartService $cart,
    ) {}

    /**
     * Create an order originating from the Ecommerce (online) channel.
     *
     * The actor is the authenticated customer, or null for guest checkout.
     * When null, the order's user_id is null and audit rows are marked
     * as is_system = true.
     *
     * @param  array  $validated  Output of EcommerceOrderRequest::validated()
     * @param  User|null  $actor  The authenticated customer, or null for guest checkout
     */
    public function execute(array $validated, ?User $actor): Order
    {
        $items = $this->cart->all();

        if (empty($items)) {
            throw new RuntimeException('Your cart is empty.');
        }

        // Map session cart items → cart_items shape OrderService expects
        $cartItems = [];
        foreach ($items as $item) {
            $product = Product::where('slug', $item['slug'])->first();
            if (! $product) {
                throw new RuntimeException("Product {$item['name']} is no longer available.");
            }

            $cartItems[] = [
                'id' => $product->id,
                'price' => (float) $item['price'], // price from cart (as the user saw)
                'quantity' => (int) $item['quantity'],
            ];
        }

        $method = $validated['delivery_method'] === 'delivery' ? 'delivery' : 'shop';

        // Compute shipping server-side — never trust the client
        $shipping_cost = 0.0;
        $location_name = 'shop';
        $area_name     = 'shop';
        $address      = 'shop';

        if ($method === 'delivery') {
            /** @var DeliveryArea|null $area */
            $area = DeliveryArea::query()
                ->where('is_active', true)
                ->find($validated['delivery_area_id']);

            if (! $area) {
                throw new RuntimeException('Selected delivery area is no longer available.');
            }

            // Optional defence-in-depth: ensure area belongs to location
            if ((int) $area->delivery_location_id !== (int) $validated['delivery_location_id']) {
                throw new RuntimeException('Selected area does not belong to the chosen location.');
            }

            $shipping_cost = (float) $area->price;
            $location_name = $area->location?->name ?? '';
            $area_name     = $area->name;
            $address      = $validated['delivery_address'];
        }

        $data = [
            'order_channel' => 'ecommerce',

            'customer' => [
                'name' => $validated['customer_name'],
                'phone' => (string) phone($validated['customer_phone'])->formatE164(),
                'email' => $validated['customer_email'],
            ],

            'delivery' => [
                'method'   => $method,
                'cost'     => $shipping_cost,
                'location' => $location_name,
                'area'     => $area_name,
                'address'  => $address,
                'status'   => DeliveryStatusEnum::PENDING->value,
            ],

            'cart_items' => $cartItems,
            'payments' => [], // COD / pay-on-pickup — no payment yet

            'user_id' => $actor?->id,

            // Ecommerce shop-pickup isn't completed until the customer collects it.
            'initial_order_status' => OrderStatusEnum::PENDING->value,

            'deduct_inventory' => false, // reserve on payment, deduct later
        ];

        return $this->orderService->create($data, $actor);
    }
}
