<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Trash2 } from '@lucide/vue';
import { ref, computed } from 'vue';
import AppPageHeader from '@/components/custom/AppPageHeader.vue';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Pagination from '@/components/custom/Pagination.vue';
import TableResultsSummary from '@/components/custom/Tables/ResultsSummary.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import deliveryLocationRoutes from '@/routes/delivery-locations';

interface DeliveryLocation {
    id: number;
    uuid: string;
    name: string;
    delivery_areas_count: number;
};

interface Props {
    locations: {
        data: DeliveryLocation[];
        links: any[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            links: any[];
        }
    }

    search?: string;
};

const props = defineProps<Props>();

const search = ref(props.search || '');
const handleSearch = (value: string) => {
    router.get(deliveryLocationRoutes.index().url, {
        search: value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const hasActiveFilters = computed(() =>
    !!(search.value)
);
</script>

<template>
    <Head title="Delivery Locations" />

    <AppPageHeader
        resourceName="Locations"
        v-model="search"
        search-placeholder="Search by name..."
        :create-url="deliveryLocationRoutes.create().url"
        create-label="Location"
        @search="handleSearch"
    />

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Areas</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(location, index) in locations.data" :key="location.id">
                    <TableCell class="id">{{ (locations.meta.current_page - 1) * locations.meta.per_page + index + 1 }}</TableCell>
                    <TableCell>{{ location.name }}</TableCell>
                    <TableCell>{{ location.delivery_areas_count ?? '-' }}</TableCell>
                    <TableCell class="actions">
                        <div class="actions-wrapper">
                            <Link :href="deliveryLocationRoutes.show(location.uuid).url" title="Add Areas">
                                <Eye class="icon show" />
                            </Link>

                            <span class="divider">|</span>

                            <Link :href="deliveryLocationRoutes.edit(location.uuid).url" title="Edit this location" class="action edit">
                                <Pencil class="icon edit" />
                            </Link>

                            <span class="divider">|</span>

                            <DeleteConfirmationDialog :url="deliveryLocationRoutes.destroy(location.uuid).url" title="Delete Location?" description="This location and it's associated areas will be deleted permanently!" confirm-text="Delete Location">
                                <template #trigger>
                                    <button class="action delete">
                                        <Trash2 class="icon delete" />
                                    </button>
                                </template>
                            </DeleteConfirmationDialog>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="locations.data.length === 0">
                    <TableCell colspan="5" class="blank-table-row">
                        No locations found!
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="locations.meta" />

    <TableResultsSummary
        :meta="locations.meta"
        item-name="location"
        item-name-plural="locations"
        :show-filter-indicators="true"
        :has-active-filters="hasActiveFilters"
    />
</template>
