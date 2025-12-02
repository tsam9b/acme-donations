<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DonationsList from './Partials/DonationsList.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
    donations: {
        type: [Array, Object],
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    campaigns: {
        type: [Array, Object],
        default: () => []
    }
})
</script>

<template>
    <Head title="All Donations" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-100">All Donations</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-gray-800 p-4 shadow sm:rounded-lg sm:p-8">
                    <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-100">
                            All Donations
                        </h2>
                        <p class="mt-1 text-sm text-gray-400">
                            View and manage donations you've received for your campaigns.
                        </p>
                    </header>
                    <DonationsList :donations="donations" :filters="filters" :campaigns="campaigns" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
