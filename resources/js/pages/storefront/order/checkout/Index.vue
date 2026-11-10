<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ShoppingBag, ArrowLeft, AlertCircle, Truck } from '@lucide/vue';
import { computed, watch } from 'vue';
import { usePriceFormatter } from '@/composables/usePriceFormatter';

const { formatPrice } = usePriceFormatter();

interface CartItem {
    thumbnail_url: string;
    slug: string;
    name: string;
    price: number;
    quantity: number;
}

interface DeliveryArea {
    id: number;
    name: string;
    price: number;
    estimated_days: number;
}

interface DeliveryLocation {
    id: number;
    name: string;
    areas: DeliveryArea[];
}

const props = defineProps<{
    items: CartItem[];
    subtotal: number;
    itemCount: number;
    locations: DeliveryLocation[];
    user?: { name: string; email: string; phone?: string } | null;
}>();

const form = useForm({
    customer_name: props.user?.name ?? '',
    customer_email: props.user?.email ?? '',
    customer_phone: props.user?.phone ?? '',
    delivery_method: 'delivery' as 'delivery' | 'pickup',
    delivery_location_id: null as number | null,
    delivery_area_id: null as number | null,
    delivery_address: '',
    notes: '',
    cart: '',
});

// --- Location / Area selection helpers ---

const selectedLocation = computed(() =>
    props.locations.find((l) => l.id === form.delivery_location_id) ?? null
);

const availableAreas = computed(() => selectedLocation.value?.areas ?? []);

const selectedArea = computed(() =>
    availableAreas.value.find((a) => a.id === form.delivery_area_id) ?? null
);

// When the user changes the location, reset the area if it doesn't belong to the new location
watch(
    () => form.delivery_location_id,
    () => {
        if (form.delivery_area_id && !availableAreas.value.some((a) => a.id === form.delivery_area_id)) {
            form.delivery_area_id = null;
        }
    }
);

// --- Shipping / totals ---

const shippingCost = computed(() => {
    if (form.delivery_method === 'pickup') {
        return 0;
    }

    return selectedArea.value?.price ?? 0;
});

const total = computed(() => props.subtotal + shippingCost.value);

const submit = () => {
    form.post('/checkout', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="checkout-page max-w-6xl mx-auto px-4 py-8 lg:py-12">
        <Link
            href="/cart"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground mb-6"
        >
            <ArrowLeft class="w-4 h-4" />
            Back to cart
        </Link>

        <h1 class="text-2xl lg:text-3xl font-bold mb-6">Checkout</h1>

        <!-- Global error banner -->
        <div
            v-if="Object.keys(form.errors).length > 0"
            class="mb-6 flex items-start gap-2 p-3 border border-red-300 bg-red-50 text-red-700 text-sm rounded-md"
        >
            <AlertCircle class="w-4 h-4 mt-0.5 shrink-0" />
            <span>Please fix the errors below and try again.</span>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Contact info -->
                <section class="border border-border rounded-lg p-5 bg-background space-y-4">
                    <h2 class="text-lg font-semibold">Contact Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name</label>
                            <input
                                v-model="form.customer_name"
                                type="text"
                                class="w-full px-3 py-2 border border-border rounded-md bg-background"
                                placeholder="John Doe"
                            />
                            <p v-if="form.errors.customer_name" class="text-xs text-red-600 mt-1">
                                {{ form.errors.customer_name }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Phone</label>
                            <input
                                v-model="form.customer_phone"
                                type="tel"
                                class="w-full px-3 py-2 border border-border rounded-md bg-background"
                                placeholder="+254 712 345 678"
                            />
                            <p v-if="form.errors.customer_phone" class="text-xs text-red-600 mt-1">
                                {{ form.errors.customer_phone }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input
                            v-model="form.customer_email"
                            type="email"
                            class="w-full px-3 py-2 border border-border rounded-md bg-background"
                            placeholder="john@example.com"
                        />
                        <p v-if="form.errors.customer_email" class="text-xs text-red-600 mt-1">
                            {{ form.errors.customer_email }}
                        </p>
                    </div>
                </section>

                <!-- Delivery method -->
                <section class="border border-border rounded-lg p-5 bg-background space-y-4">
                    <h2 class="text-lg font-semibold">Delivery Method</h2>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.delivery_method" value="delivery" />
                            Delivery
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.delivery_method" value="pickup" />
                            Pickup at shop
                        </label>
                    </div>
                    <p v-if="form.errors.delivery_method" class="text-xs text-red-600">
                        {{ form.errors.delivery_method }}
                    </p>
                </section>

                <!-- Delivery address — only when delivery -->
                <section
                    v-if="form.delivery_method === 'delivery'"
                    class="border border-border rounded-lg p-5 bg-background space-y-4"
                >
                    <h2 class="text-lg font-semibold">Delivery Address</h2>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium mb-1">City / Location</label>
                        <select
                            v-model.number="form.delivery_location_id"
                            class="w-full px-3 py-2 border border-border rounded-md bg-background"
                        >
                            <option :value="null" disabled>Select a location</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.delivery_location_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.delivery_location_id }}
                        </p>
                    </div>

                    <!-- Area -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Area / Neighbourhood</label>
                        <select
                            v-model.number="form.delivery_area_id"
                            :disabled="!form.delivery_location_id"
                            class="w-full px-3 py-2 border border-border rounded-md bg-background disabled:opacity-50"
                        >
                            <option :value="null" disabled>
                                {{ form.delivery_location_id ? 'Select an area' : 'Select a location first' }}
                            </option>
                            <option v-for="area in availableAreas" :key="area.id" :value="area.id">
                                {{ area.name }} — Ksh. {{ formatPrice(area.price) }}
                                ({{ area.estimated_days }} day{{ area.estimated_days > 1 ? 's' : '' }})
                            </option>
                        </select>
                        <p v-if="form.errors.delivery_area_id" class="text-xs text-red-600 mt-1">
                            {{ form.errors.delivery_area_id }}
                        </p>
                        <p
                            v-if="selectedArea"
                            class="text-xs text-muted-foreground mt-1 flex items-center gap-1"
                        >
                            <Truck class="w-3 h-3" />
                            Estimated delivery: {{ selectedArea.estimated_days }}
                            day{{ selectedArea.estimated_days > 1 ? 's' : '' }}
                        </p>
                    </div>

                    <!-- Street address -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Street Address</label>
                        <input
                            v-model="form.delivery_address"
                            type="text"
                            class="w-full px-3 py-2 border border-border rounded-md bg-background"
                            placeholder="123 Kimathi Street"
                        />
                        <p v-if="form.errors.delivery_address" class="text-xs text-red-600 mt-1">
                            {{ form.errors.delivery_address }}
                        </p>
                    </div>
                </section>

                <!-- Notes -->
                <section class="border border-border rounded-lg p-5 bg-background">
                    <h2 class="text-lg font-semibold mb-3">Order Notes (optional)</h2>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full px-3 py-2 border border-border rounded-md bg-background"
                        placeholder="Any special delivery instructions..."
                    ></textarea>
                    <p v-if="form.errors.notes" class="text-xs text-red-600 mt-1">
                        {{ form.errors.notes }}
                    </p>
                </section>
            </div>

            <!-- Right: summary -->
            <div class="lg:col-span-1">
                <div class="border border-border rounded-lg p-5 bg-background sticky top-24 space-y-4">
                    <h2 class="text-lg font-semibold">Order Summary</h2>

                    <div class="space-y-3 max-h-72 overflow-y-auto">
                        <div v-for="item in items" :key="item.slug" class="flex gap-3 items-center">
                            <div class="w-12 h-12 rounded bg-accent flex items-center justify-center shrink-0 overflow-hidden">
                                <img v-if="item.thumbnail_url" :src="item.thumbnail_url" :alt="item.name" class="w-full h-full object-cover" />
                                <ShoppingBag v-else class="w-5 h-5 text-muted-foreground" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium line-clamp-1">{{ item.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ item.quantity }} × Ksh. {{ formatPrice(item.price) }}
                                </p>
                            </div>
                            <span class="text-sm font-semibold">
                                {{ formatPrice(item.price * item.quantity) }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-border pt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Items ({{ itemCount }})</span>
                            <span>Ksh. {{ formatPrice(subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Shipping</span>
                            <span v-if="form.delivery_method === 'pickup'" class="text-muted-foreground">
                                Free (pickup)
                            </span>
                            <span v-else-if="selectedArea">
                                Ksh. {{ formatPrice(shippingCost) }}
                            </span>
                            <span v-else class="text-muted-foreground">
                                Select area
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-border pt-3 flex justify-between font-semibold">
                        <span>Total</span>
                        <span>Ksh. {{ formatPrice(total) }}</span>
                    </div>

                    <!-- Cart-level error (stock conflicts, empty cart, etc.) -->
                    <p v-if="form.errors.cart" class="text-sm text-red-600">
                        {{ form.errors.cart }}
                    </p>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Placing order...</span>
                        <span v-else>Place Order</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>