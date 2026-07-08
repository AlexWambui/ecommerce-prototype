<script setup lang="ts">
import Hero from './Hero.vue';
import ProductGrid from '@/pages/guest/common/ProductGrid.vue';
import { ref } from 'vue';

const props = defineProps<{
    featuredProducts: any[];
    newProducts: any[];
    saleProducts: any[];
}>();

const isLoading = ref(false);

// Simulate loading
const loadMore = () => {
    isLoading.value = true;
    setTimeout(() => {
        isLoading.value = false;
    }, 2000);
};
</script>

<template>
    <div>
        <!-- New Arrivals -->
        <ProductGrid 
            title="New Arrivals" 
            :products="newProducts" 
            :columns="4"
            class="bg-gray-50"
        />
    </div>
</template>