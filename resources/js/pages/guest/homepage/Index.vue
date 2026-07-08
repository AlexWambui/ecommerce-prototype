<script setup lang="ts">
import Hero from './Hero.vue';
import NewArrivals from './NewestArrivals.vue';

defineProps<{
    products: any[];
    featuredProducts: any[];
    newProducts: any[];
    saleProducts: any[];
}>();
</script>

<template>
    <Hero />

    <NewArrivals
        :featured-products="featuredProducts"
        :new-products="newProducts"
        :sale-products="saleProducts"
    />
</template>