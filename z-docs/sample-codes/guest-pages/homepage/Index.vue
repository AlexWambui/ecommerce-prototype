<script setup lang="ts">
import Hero from './Hero.vue';
import NewArrivals from './NewestArrivals.vue';
import MostPopular from './MostPopular.vue';

defineProps<{
    new_arrivals: any[];
    most_popular: any[];
}>();
</script>

<template>
    <Hero />

    <NewArrivals
        :new_arrivals="new_arrivals"
    />

    <MostPopular
        :most_popular="most_popular"
    />
</template>