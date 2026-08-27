<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { ref, computed } from 'vue';
import AppPageHeader from '@/components/custom/AppPageHeader.vue';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Pagination from '@/components/custom/Pagination.vue';
import TableResultsSummary from '@/components/custom/Tables/ResultsSummary.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { usePriceFormatter } from '@/composables/usePriceFormatter';
import deliveryAreaRoutes from '@/routes/delivery-areas';
import deliveryLocationRoutes from '@/routes/delivery-locations';
import DeliveryNav from '../components/DeliveryNav.vue';

const { formatPrice } = usePriceFormatter();

interface DeliveryLocation {
    id: number;
    uuid: string;
    name: string;
}

interface DeliveryArea {
    id: number;
    uuid: string;
    name: string;
    price: number;
    estimated_days: number;
    is_active: boolean;
};

interface Props {
    delivery_location: DeliveryLocation;

    delivery_areas: {
        data: DeliveryArea[];
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
    <Head title="Delivery Areas" />

    <DeliveryNav current-page="areas" />

    <AppPageHeader
        :resourceName="`Delivery Areas - ${delivery_location.name}`"
        v-model="search"
        search-placeholder="Search by name..."
        :create-url="deliveryAreaRoutes.create(delivery_location.uuid).url"
        create-label="Area"
        @search="handleSearch"
    />

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Shipping Cost</TableHead>
                    <TableHead>Estimated Days</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(area, index) in delivery_areas.data" :key="area.id">
                    <TableCell class="id">{{ (delivery_areas.meta.current_page - 1) * delivery_areas.meta.per_page + index + 1 }}</TableCell>
                    <TableCell :class="{'text-red-600' : !area.is_active}">{{ area.name }}</TableCell>
                    <TableCell>{{ formatPrice(area.price) }}</TableCell>
                    <TableCell>{{ area.estimated_days }}</TableCell>
                    <TableCell class="actions">
                        <div class="actions-wrapper">
                            <Link :href="deliveryAreaRoutes.edit({delivery_location: delivery_location.uuid, delivery_area: area.uuid}).url" class="action edit">
                                <Pencil class="icon edit" />
                            </Link>

                            <span class="divider">|</span>

                            <DeleteConfirmationDialog :url="deliveryAreaRoutes.destroy(area.uuid).url" title="Delete Area?" description="This area will be deleted permanently!" confirm-text="Delete Area">
                                <template #trigger>
                                    <button class="action delete">
                                        <Trash2 class="icon delete" />
                                    </button>
                                </template>
                            </DeleteConfirmationDialog>
                        </div>
                    </TableCell>
                </TableRow>

                <TableRow v-if="delivery_areas.data.length === 0">
                    <TableCell colspan="5" class="blank-table-row">
                        No areas found!
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="delivery_areas.meta" />

    <TableResultsSummary
        :meta="delivery_areas.meta"
        item-name="delivery area"
        item-name-plural="delivery areas"
        :show-filter-indicators="true"
        :has-active-filters="hasActiveFilters"
    />
</template>
