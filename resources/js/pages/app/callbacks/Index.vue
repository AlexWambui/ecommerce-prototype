<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, X, Check } from '@lucide/vue';
import Input from '@/components/ui/input/Input.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Pagination from '@/components/custom/Pagination.vue';
import callbackMessageRoutes from '@/routes/callback-messages';

interface CallbackMessage {
    id: number;
    uuid: string;
    name: string;
    phone_number: string;
    message: string;
    is_read: boolean;
    is_resolved: boolean;
}

interface Props {
    callback_messages: {
        data: CallbackMessage[];
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
    router.get(callbackMessageRoutes.list().url, {
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
    const { current_page, per_page, total } = props.callback_messages.meta;
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(current_page * per_page, total);
    return { start, end, total };
});

const hasActiveFilters = computed(() => 
    !!(search.value || status_filter.value)
);
</script>

<template>
    <Head title="Callback Messages" />
    
    <div class="app-header">
        <div class="info">
            <h1 class="title">Callbacks</h1>
        </div>

        <div class="search">
            <Input
                v-model="search"
                type="text"
                placeholder="Search by name, phone number or message..."
            />
        </div>

        <div class="action">
            
        </div>
    </div>

    <div class="table-wrapper">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="id">#</TableHead>
                    <TableHead>Name</TableHead>
                    <TableHead>Phone</TableHead>
                    <TableHead>Message</TableHead>
                    <TableHead>Resolved</TableHead>
                    <TableHead class="actions">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="(callback_message, index) in callback_messages.data" :key="callback_message.id" :class="callback_message.is_read ? '' : 'font-bold'">
                    <TableCell class="id">{{ (callback_messages.meta.current_page - 1) * callback_messages.meta.per_page + index + 1 }}</TableCell>
                    <TableCell>{{ callback_message.name }}</TableCell>
                    <TableCell>{{ callback_message.phone_number }}</TableCell>
                    <TableCell>{{ callback_message.message }}</TableCell>
                    <TableCell>
                        <Check v-if="callback_message.is_resolved" class="text-green-800" />
                        <X v-else class="text-red-600" />
                    </TableCell>
                    <TableCell class="actions">
                        <div class="actions-wrapper">
                            <Link :href="callbackMessageRoutes.edit(callback_message.uuid).url" class="action edit">
                                <Pencil />
                            </Link>
                            <span class="divider">|</span>
                            <DeleteConfirmationDialog 
                                :url="callbackMessageRoutes.destroy(callback_message.uuid).url" 
                                title="Delete Callback?" 
                                description="This callback message will be deleted permanently!" 
                                confirm-text="Delete Callback"
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

                <TableRow v-if="callback_messages.data.length === 0">
                    <TableCell colspan="6" class="blank-table-row">
                        No callback_messages found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <Pagination :meta="callback_messages.meta" />

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