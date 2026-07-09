<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Pagination from '@/components/custom/Pagination.vue';
import ProductsNav from '../components/ProductsNav.vue';
import type { Product } from '@/types/product';
import productRoutes from '@/routes/products';

import { usePriceFormatter } from '@/composables/usePriceFormatter';

const { formatPrice } = usePriceFormatter();

interface Props {
    products: {
        data: Product[];
        links: any[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            links: any[];
        };
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const status_filter = ref(props.filters?.status || '');

const debouncedSearch = useDebounceFn(() => {
    router.get(productRoutes.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, () => {
    debouncedSearch();
});

const getDisplayRange = computed(() => {
    const { current_page, per_page, total } = props.products.meta;
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(current_page * per_page, total);
    return { start, end, total };
});

const hasActiveFilters = computed(() => 
    !!(search.value || status_filter.value)
);
</script>

<template>
    <ProductsNav current-page="products" />
    
    <div class="header">
        <div class="info">
            <h1 class="title">Products</h1>
        </div>

        <div class="search">
            <Input
                v-model="search"
                type="text"
                placeholder="Search by name or slug..."
            />
        </div>

        <div class="action">
            <Link :href="productRoutes.create().url">
                <Button>New Product</Button>
            </Link>
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Image</TableHead>
                    <TableHead>Product</TableHead>
                    <TableHead>SKU</TableHead>
                    <TableHead>Price (Ksh)</TableHead>
                    <TableHead>Stock</TableHead>
                    <TableHead>Category</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(product, index) in products.data" :key="product.id">
                    <TableCell class="id">{{ (products.meta.current_page - 1) * products.meta.per_page + index + 1 }}</TableCell>
                    <TableCell><img :src="product.thumbnail_url" :alt="product.slug"></TableCell>
                    <TableCell>{{ product.name }}</TableCell>
                    <TableCell>{{ product.sku ?? '-' }}</TableCell>
                    <TableCell>{{ formatPrice(product.price) }}</TableCell> <!-- TODO: Show discounts if available and price as old price -->
                    <TableCell>xx</TableCell> <!-- TODO: Use correct stock count -->
                    <TableCell>{{ product.category_name }}</TableCell>
                    <TableCell class="actions">
                        <div class="actions-wrapper">
                            <Link :href="productRoutes.edit(product.id).url" class="action edit">
                                <Pencil />
                            </Link>
                            <span class="divider">|</span>
                            <DeleteConfirmationDialog 
                                :url="productRoutes.destroy(product.id).url" 
                                title="Delete Product?" 
                                description="This product will be deleted permanently!" 
                                confirm-text="Delete Product"
                            >
                                <template #trigger>
                                    <button class="action delete">
                                        <Trash2 />
                                    </button>
                                </template>
                            </DeleteConfirmationDialog>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="products.data.length === 0">
                    <TableCell colspan="6" class="blank-table-row">
                        No products found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="products.meta" />

    <div class="table-results-summary">
        <p>
            Showing {{ getDisplayRange.start }} to {{ getDisplayRange.end }}
            of {{ getDisplayRange.total }} products
        </p>
        <p v-if="hasActiveFilters" class="filtered-results">
            Filtered results
        </p>
    </div>
</template>