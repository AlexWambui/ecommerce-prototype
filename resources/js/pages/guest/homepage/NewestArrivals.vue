<script setup lang="ts">
import ProductGrid from '@/pages/guest/common/ProductGrid.vue';
import { ref } from 'vue';

const props = defineProps<{
    new_arrivals: any[];
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
    <ProductGrid 
        v-if="new_arrivals?.length"
        title="New Arrivals" 
        :products="new_arrivals" 
        :columns="4"
        class="NewArrivals"
    />
</template>