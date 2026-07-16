<script setup lang="ts">
import ProductGrid from '@/pages/guest/common/ProductGrid.vue';
import { ref } from 'vue';

const props = defineProps<{
    most_popular: any[];
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
    <div class="most_popular">
        <ProductGrid 
            title="Most Popular" 
            :products="most_popular"
            :columns="4"
            class="bg-gray-50"
        />
    </div>
</template>