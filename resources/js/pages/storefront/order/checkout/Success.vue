<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, Package } from '@lucide/vue';
import { usePriceFormatter } from '@/composables/usePriceFormatter';

const { formatPrice } = usePriceFormatter();

defineProps<{
    order: {
        order_number: string;
        customer_name: string;
        customer_email: string;
        total: number;
        items: { name: string; price: number; quantity: number; thumbnail_url?: string }[];
    };
}>();
</script>

<template>
    <div class="max-w-2xl mx-auto px-4 py-12">
        <div class="flex flex-col items-center text-center mb-8">
            <CheckCircle2 class="w-16 h-16 text-green-500 mb-4" />
            <h1 class="text-2xl lg:text-3xl font-bold mb-2">Order Placed!</h1>
            <p class="text-muted-foreground">
                Thank you, {{ order.customer_name }}. We've received your order.
            </p>
        </div>

        <div class="border border-border rounded-lg p-5 space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Order number</span>
                <span class="font-mono font-medium">{{ order.order_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Confirmation sent to</span>
                <span>{{ order.customer_email }}</span>
            </div>

            <div class="border-t border-border pt-4 space-y-3">
                <div
                    v-for="(item, idx) in order.items"
                    :key="idx"
                    class="flex items-center gap-3"
                >
                    <div class="w-12 h-12 rounded bg-accent flex items-center justify-center shrink-0 overflow-hidden">
                        <img v-if="item.thumbnail_url" :src="item.thumbnail_url" :alt="item.name" class="w-full h-full object-cover" />
                        <Package v-else class="w-5 h-5 text-muted-foreground" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium">{{ item.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ item.quantity }} × Ksh. {{ formatPrice(item.price) }}
                        </p>
                    </div>
                    <span class="text-sm font-semibold">
                        Ksh. {{ formatPrice(item.price * item.quantity) }}
                    </span>
                </div>
            </div>

            <div class="border-t border-border pt-3 flex justify-between font-semibold">
                <span>Total</span>
                <span>Ksh. {{ formatPrice(order.total) }}</span>
            </div>
        </div>

        <div class="mt-6 flex justify-center gap-3">
            <Link href="/shop" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Continue Shopping
            </Link>
            <Link href="/" class="px-4 py-2 border border-border rounded-md hover:bg-accent">
                Go Home
            </Link>
        </div>
    </div>
</template>