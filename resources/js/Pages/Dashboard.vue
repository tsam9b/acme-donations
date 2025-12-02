<script setup>
import CampaignsList from './Dashboard/MyCampaigns/Partials/CampaignsList.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    campaigns: {
        type: [Array, Object],
        default: () => []
    },
    predefinedAmounts: {
        type: Array,
        default: () => [10, 25, 50, 100]
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-100">
                Dashboard
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                 <h2 class="text-lg font-medium text-gray-100">
                    My Campaigns
                </h2>
                <div class="bg-gray-800 p-4 shadow sm:rounded-lg sm:p-8">
                    <CampaignsList :campaigns="campaigns" :filters="filters" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
