<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DashboardStat from './components/DashboardStat.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

interface Props {
    stats: {
        total_products: number;
        total_product_categories: number;
        total_users: number;
        total_admins: number;
        total_cashiers: number;
        total_callbacks: number;
        total_unread_callbacks: number;
    }
};

defineProps<Props>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="Dashboard AdminDashboard space-y-12">
        <section class="header">
            <div class="flex items-center gap-4">
                <p>Hi {{ user.name }}</p>
                <span class="text-xs text-blue-900 bg-blue-100 py-1 px-2 rounded-sm">{{ user.role_label }}</span>
            </div>
        </section>

        <section class="stats-wrapper">
            <h2 class="mb-4 font-medium">Platform Statistics</h2>
            
            <div class="stats grid gap-8 lg:grid-cols-6">
                <DashboardStat :stat="stats.total_users" label="Users">
                    <template #extras>
                        <span class="text-sm text-muted-foreground">
                            {{ stats.total_admins }} Admins & {{ stats.total_cashiers }} Cashiers
                        </span>
                    </template>
                </DashboardStat>

                <DashboardStat :stat="stats.total_products" label="Products">
                    <template #extras>
                        <span class="text-sm text-muted-foreground">
                            {{ stats.total_product_categories }} Categories
                        </span>
                    </template>
                </DashboardStat>

                <DashboardStat :stat="stats.total_callbacks" label="Callback Requests">
                    <template #extras>
                        <span class="text-sm text-muted-foreground">
                            {{ stats.total_unread_callbacks }} Unread
                        </span>
                    </template>
                </DashboardStat>
            </div>
        </section>
    </div>
</template>