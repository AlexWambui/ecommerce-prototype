<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

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

const props = defineProps<{
    product: Product;
}>();

const currentImageIndex = ref(0);
const isHovered = ref(false);
let autoSlideInterval: ReturnType<typeof setInterval> | null = null;

const currentImage = computed(() => {
    return props.product.images[currentImageIndex.value] || props.product.images[0];
});

const hasMultipleImages = computed(() => {
    return props.product.images && props.product.images.length > 1;
});

const nextImage = () => {
    if (hasMultipleImages.value) {
        currentImageIndex.value = (currentImageIndex.value + 1) % props.product.images.length;
    }
};

const prevImage = () => {
    if (hasMultipleImages.value) {
        currentImageIndex.value = currentImageIndex.value === 0 
            ? props.product.images.length - 1 
            : currentImageIndex.value - 1;
    }
};

const goToImage = (index: number) => {
    currentImageIndex.value = index;
};

const startAutoSlide = () => {
    if (hasMultipleImages.value) {
        autoSlideInterval = setInterval(nextImage, 3000);
    }
};

const stopAutoSlide = () => {
    if (autoSlideInterval) {
        clearInterval(autoSlideInterval);
        autoSlideInterval = null;
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const getStars = (rating: number) => {
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    return { fullStars, hasHalfStar };
};

onMounted(() => {
    startAutoSlide();
});

onUnmounted(() => {
    stopAutoSlide();
});
</script>

<template>
    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 group"
         @mouseenter="stopAutoSlide"
         @mouseleave="startAutoSlide">
        
        <!-- Image Carousel -->
        <div class="relative overflow-hidden bg-gray-100 aspect-square">
            <img 
                :src="currentImage?.url || '/assets/images/placeholder-product.jpg'" 
                :alt="currentImage?.alt || product.name"
                class="w-full h-full object-cover transition-transform duration-500"
                :class="{ 'scale-105': isHovered }"
                @mouseenter="isHovered = true"
                @mouseleave="isHovered = false"
                loading="lazy"
            />
            
            <!-- Navigation Arrows -->
            <button v-if="hasMultipleImages" 
                    @click.stop="prevImage"
                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white shadow-lg">
                <svg class="w-4 h-4 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button v-if="hasMultipleImages" 
                    @click.stop="nextImage"
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/90 backdrop-blur-sm p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white shadow-lg">
                <svg class="w-4 h-4 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Dots Indicator -->
            <div v-if="hasMultipleImages" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                <button v-for="(image, index) in product.images" :key="index"
                        @click.stop="goToImage(index)"
                        class="transition-all duration-300"
                        :class="[
                            'rounded-full',
                            currentImageIndex === index 
                                ? 'w-3 h-1.5 bg-white' 
                                : 'w-1.5 h-1.5 bg-white/60'
                        ]">
                </button>
            </div>

            <!-- Badges -->
            <div class="absolute top-2 left-2 flex flex-col gap-1">
                <span v-if="product.is_on_sale" 
                      class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg">
                    SALE
                </span>
                <span v-if="product.is_new" 
                      class="bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg">
                    NEW
                </span>
                <span v-if="product.is_featured && !product.is_on_sale && !product.is_new" 
                      class="bg-yellow-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-lg">
                    FEATURED
                </span>
            </div>

            <!-- Wishlist Button -->
            <button class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-md hover:bg-white transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 hover:text-red-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>

            <!-- Image Counter -->
            <span v-if="hasMultipleImages" 
                  class="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm">
                {{ currentImageIndex + 1 }} / {{ product.images.length }}
            </span>
        </div>

        <!-- Product Info -->
        <div class="p-4">
            <div class="mb-1">
                <span class="text-xs text-gray-500 uppercase tracking-wider">{{ product.category }}</span>
            </div>
            
            <h3 class="text-base font-semibold text-gray-900 hover:text-indigo-600 transition-colors line-clamp-1">
                <a :href="`/products/${product.slug}`">{{ product.name }}</a>
            </h3>
            
            <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                {{ product.description }}
            </p>

            <!-- Rating -->
            <div class="flex items-center gap-1 mb-3">
                <div class="flex items-center">
                    <span v-for="star in getStars(product.rating).fullStars" :key="star" class="text-yellow-400 text-sm">★</span>
                    <span v-if="getStars(product.rating).hasHalfStar" class="text-yellow-400 text-sm">☆</span>
                    <span v-for="star in 5 - Math.ceil(product.rating)" :key="'empty-' + star" class="text-gray-300 text-sm">★</span>
                </div>
                <span class="text-xs text-gray-500 ml-1">({{ product.reviews_count }})</span>
            </div>

            <!-- Price & Stock -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold text-gray-900">{{ formatPrice(product.price) }}</span>
                    <span v-if="product.compare_price" class="text-sm text-gray-400 line-through">
                        {{ formatPrice(product.compare_price) }}
                    </span>
                </div>
                
                <div class="flex items-center gap-1">
                    <span :class="[
                        'inline-block w-2 h-2 rounded-full',
                        product.stock > 10 ? 'bg-green-500' :
                        product.stock > 0 ? 'bg-yellow-500' : 'bg-red-500'
                    ]"></span>
                    <span class="text-xs text-gray-500">
                        {{ product.stock > 0 ? `${product.stock}` : 'Out of stock' }}
                    </span>
                </div>
            </div>

            <!-- Add to Cart Button -->
            <button :disabled="product.stock === 0"
                    :class="[
                        'w-full mt-3 py-2.5 px-4 rounded-lg font-medium transition-all duration-200',
                        product.stock > 0 
                            ? 'bg-indigo-600 hover:bg-indigo-700 text-white hover:shadow-lg active:scale-95' 
                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                    ]">
                {{ product.stock > 0 ? 'Add to Cart' : 'Out of Stock' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-square {
    aspect-ratio: 1 / 1;
}

/* Smooth transitions */
.group {
    transition: all 0.3s ease;
}
</style>