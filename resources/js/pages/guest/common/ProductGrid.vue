<script setup lang="ts">
import ProductCard from './ProductCard.vue';
import ProductCardSkeleton from './ProductCardSkeleton.vue';

interface ProductImage {
    url: string;
    alt: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    compare_price: number | null;
    stock: number;
    sku: string;
    category: string;
    tags: string[];
    images: ProductImage[];
    rating: number;
    reviews_count: number;
    is_featured: boolean;
    is_new: boolean;
    is_on_sale: boolean;
    created_at: string;
    updated_at: string;
}

defineProps<{
    title?: string;
    products: Product[];
    columns?: 2 | 3 | 4;
    showViewAll?: boolean;
    viewAllLink?: string;
    loading?: boolean;
    skeletonCount?: number;
}>();

const emit = defineEmits<{
    (e: 'viewAll'): void;
}>();
</script>

<template>
    <section class="py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div v-if="title" class="flex justify-between items-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">
                    {{ title }}
                </h2>
                <a v-if="showViewAll" 
                   :href="viewAllLink || '#'" 
                   @click.prevent="emit('viewAll')"
                   class="text-indigo-600 hover:text-indigo-700 font-medium text-sm sm:text-base transition-colors">
                    View All →
                </a>
            </div>

            <!-- Grid -->
            <div :class="[
                'grid gap-4 sm:gap-6',
                columns === 2 ? 'grid-cols-1 sm:grid-cols-2' :
                columns === 3 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' :
                'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'
            ]">
                <!-- Skeletons -->
                <ProductCardSkeleton v-if="loading" v-for="n in (skeletonCount || 8)" :key="'skeleton-' + n" />
                
                <!-- Products -->
                <ProductCard 
                    v-else
                    v-for="product in products" 
                    :key="product.id"
                    :product="product"
                />
            </div>

            <!-- Empty State -->
            <div v-if="!loading && products.length === 0" class="text-center py-16">
                <div class="text-gray-400 text-6xl mb-4">📦</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No products found</h3>
                <p class="text-gray-400">Check back later for new arrivals!</p>
            </div>
        </div>
    </section>
</template>