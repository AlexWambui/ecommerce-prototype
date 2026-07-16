<script setup lang="ts">
import Hero from './Hero.vue';
import NewArrivals from './NewestArrivals.vue';

defineProps<{
    new_arrivals: any[];
}>();
</script>

<template>
    <Hero />

    <NewArrivals
        :new_arrivals="new_arrivals"
    />
</template>