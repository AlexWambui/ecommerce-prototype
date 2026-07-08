<script setup lang="ts">
import Navbar from '@/components/custom/Navbar/Index.vue';
</script>

<template>
    <Navbar />
    
    <main class="GuestLayout">
        <slot />
    </main>
</template>
