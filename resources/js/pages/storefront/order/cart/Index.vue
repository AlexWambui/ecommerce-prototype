<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Minus, Plus, Trash2, ShoppingBag, ArrowRight } from '@lucide/vue';
import { computed } from 'vue';
import { usePriceFormatter } from '@/composables/usePriceFormatter';

const { formatPrice } = usePriceFormatter();

interface CartItem {
    thumbnail_url: string;
    slug: string;
    name: string;
    price: number;
    quantity: number;
}

const props = defineProps<{
    items: CartItem[];
    subtotal: number;
    itemCount: number;
}>();

const isEmpty = computed(() => props.items.length === 0);

const updateQuantity = (slug: string, quantity: number) => {
    if (quantity < 1) {
      return;
    }

    router.post('/cart/update', { slug, quantity }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const removeItem = (slug: string) => {
    router.post('/cart/remove', { slug }, {
        preserveScroll: true,
    });
};

const clearCart = () => {
    if (!confirm('Are you sure you want to clear your cart?')) {
      return;
    }

    router.post('/cart/clear', {}, { preserveScroll: true });
};
</script>

<template>
    <div class="cart-page max-w-6xl mx-auto px-4 py-8 lg:py-12">
        <h1 class="text-2xl lg:text-3xl font-bold mb-6">Your Cart</h1>

        <!-- Empty state -->
        <div v-if="isEmpty" class="flex flex-col items-center justify-center py-16 text-center">
            <ShoppingBag class="w-16 h-16 text-muted-foreground mb-4" />
            <p class="text-lg font-medium mb-2">Your cart is empty</p>
            <p class="text-sm text-muted-foreground mb-6">
                Looks like you haven't added anything yet.
            </p>
            <Link
                href="/shop"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
            >
                Continue Shopping
            </Link>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Items list -->
            <div class="lg:col-span-2 space-y-3">
                <div
                    v-for="item in items"
                    :key="item.slug"
                    class="flex gap-4 p-4 border border-border rounded-lg bg-background"
                >
                    <!-- Placeholder thumbnail -->
                    <div class="w-20 h-20 rounded bg-accent flex items-center justify-center shrink-0">
                        <img :src="item.thumbnail_url" :alt="item.name" />
                        <!-- <ShoppingBag class="w-8 h-8 text-muted-foreground" /> -->
                    </div>

                    <div class="flex-1 min-w-0">
                        <Link
                            :href="`/product-details/${item.slug}`"
                            class="text-base font-medium hover:text-indigo-600 line-clamp-2"
                        >
                            {{ item.name }}
                        </Link>
                        <p class="text-sm text-muted-foreground mt-1">
                            Ksh. {{ formatPrice(item.price) }}
                        </p>

                        <div class="flex items-center justify-between mt-3">
                            <!-- Quantity controls -->
                            <div class="flex items-center border border-border rounded-md overflow-hidden">
                                <button
                                    @click="updateQuantity(item.slug, item.quantity - 1)"
                                    class="p-1.5 hover:bg-accent disabled:opacity-40"
                                    :disabled="item.quantity <= 1"
                                    aria-label="Decrease quantity"
                                >
                                    <Minus class="w-4 h-4" />
                                </button>
                                <span class="px-3 py-1 text-sm min-w-10 text-center">
                                    {{ item.quantity }}
                                </span>
                                <button
                                    @click="updateQuantity(item.slug, item.quantity + 1)"
                                    class="p-1.5 hover:bg-accent"
                                    aria-label="Increase quantity"
                                >
                                    <Plus class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="flex items-center gap-4">
                                <span class="text-sm font-semibold">
                                    Ksh. {{ formatPrice(item.price * item.quantity) }}
                                </span>
                                <button
                                    @click="removeItem(item.slug)"
                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-md"
                                    aria-label="Remove item"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    @click="clearCart"
                    class="text-sm text-red-600 hover:underline mt-2"
                >
                    Clear cart
                </button>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="border border-border rounded-lg p-5 bg-background sticky top-24 space-y-4">
                    <h2 class="text-lg font-semibold">Order Summary</h2>

                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Items ({{ itemCount }})</span>
                        <span>Ksh. {{ formatPrice(subtotal) }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-muted-foreground">Shipping</span>
                        <span class="text-muted-foreground">Calculated at checkout</span>
                    </div>

                    <div class="border-t border-border pt-3 flex justify-between font-semibold">
                        <span>Subtotal</span>
                        <span>Ksh. {{ formatPrice(subtotal) }}</span>
                    </div>

                    <Link
                        href="/checkout"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium"
                    >
                        Proceed to Checkout
                        <ArrowRight class="w-4 h-4" />
                    </Link>

                    <Link
                        href="/shop"
                        class="w-full flex items-center justify-center text-sm text-muted-foreground hover:text-foreground"
                    >
                        or continue shopping
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>