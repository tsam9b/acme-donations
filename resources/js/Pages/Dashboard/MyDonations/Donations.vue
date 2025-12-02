<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DonationsList from '@/Pages/Dashboard/Donations/Partials/DonationsList.vue'
import { Head, usePage } from '@inertiajs/vue3'

const props = defineProps({
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

// Use server-provided donations directly (no client-side filtering)
const donationsForUser = props.donations

</script>

<template>
	<Head title="My Donations" />

	<AuthenticatedLayout>
		<template #header>
			<h2 class="text-xl font-semibold leading-tight text-gray-100">My Donations</h2>
		</template>

		<div class="py-12">
			<div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
				<div class="bg-gray-800 p-4 shadow sm:rounded-lg sm:p-8">
                     <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-100">
                            My Donations
                        </h2>
                        <p class="mt-1 text-sm text-gray-400">
                            View and manage donations you've made to campaigns.
                        </p>
                    </header>
					<DonationsList :donations="donationsForUser" :filters="filters" :campaigns="campaigns" />
				</div>
			</div>
		</div>
	</AuthenticatedLayout>
</template>
