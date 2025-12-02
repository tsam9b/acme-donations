<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CampaignsList from './Partials/CampaignsList.vue';
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
    <Head title="My Campaigns" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-100"
            >
                My Campaigns
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-gray-800 p-4 shadow sm:rounded-lg sm:p-8">
                    <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-100">
                            My Campaigns
                        </h2>
                        <p class="mt-1 text-sm text-gray-400">
                            Manage your donation campaigns. Create, edit, and track your campaigns.
                        </p>
                    </header>
                    <CampaignsList :campaigns="campaigns" :filters="filters" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
