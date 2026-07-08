<script setup lang="ts">
import ProductsNav from '../components/ProductsNav.vue';
import { Link } from '@inertiajs/vue3';
import productCategoryRoutes from '@/routes/product-categories';
</script>

<template>
    <ProductsNav current-page="product-categories" />

    <div class="header">
        <h1>Categories</h1>

        <div class="search">
            <input type="text" placeholder="Search Products by name">
        </div>

        <div class="action">
            <Link :href="productCategoryRoutes.create().url">New</Link>
        </div>
    </div>
</template>