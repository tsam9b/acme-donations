<template>
    <section>

        <!-- Combined Search + Filters (single row on desktop, stacked on mobile) -->
        <div class="mb-6 p-3 bg-gray-800 border border-gray-700 rounded-lg">
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center w-full">
                <!-- Search -->
                <div class="flex-1 min-w-0">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search donations by amount or campaign name..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-600 rounded-md leading-5 bg-gray-700 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                        <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button
                                @click="searchQuery = ''"
                                class="text-gray-400 hover:text-gray-200 focus:outline-none"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filters (inline on desktop) -->
                <div class="flex items-end sm:items-center gap-3 w-full sm:w-auto">
                    <div class="w-full sm:w-64">
                        <label class="sr-only">Campaign</label>
                        <select
                            v-model="campaignFilter"
                            class="block w-full px-3 py-2 border border-gray-600 rounded-md leading-5 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">All Campaigns</option>
                            <option v-for="c in availableCampaigns" :key="c.id || c.name" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div class="flex items-end sm:items-center w-full sm:w-auto">
                        <button
                            @click="clearFilters"
                            class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-300 bg-gray-600 border border-gray-600 rounded-md hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition duration-150 whitespace-nowrap"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Donations Table -->
        <div class="overflow-hidden shadow ring-1 ring-gray-600 ring-opacity-5 rounded-lg">
            <div v-if="donationsData.length === 0" class="text-center py-8 bg-gray-800">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-400">
                    {{ hasActiveFilters ? 'No donations found matching your filters.' : 'No donations yet.' }}
                </p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-600 bg-gray-800">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('donor')" class="inline-flex items-center space-x-2">
                                <span>Donor</span>
                                <span v-if="sortBy === 'donor'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('amount')" class="inline-flex items-center space-x-2">
                                <span>Amount</span>
                                <span v-if="sortBy === 'amount'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('campaign_name')" class="inline-flex items-center space-x-2">
                                <span>Campaign</span>
                                <span v-if="sortBy === 'campaign_name'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('donation_date')" class="inline-flex items-center space-x-2">
                                <span>Date</span>
                                <span v-if="sortBy === 'donation_date'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th> -->
                        <!-- <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th> -->
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-600">
                    <tr v-for="donation in donationsData" :key="donation.id" class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                        <div class="text-sm font-medium text-gray-100">{{ donation.donor_user_name || 'Anonymous' }}</div>
                                        <div class="text-sm text-gray-400">{{ donation.donor_user_email || '—' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">${{ formatAmount(donation.amount) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">{{ donation.campaign_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">{{ formatDate(donation.donation_date, '—') }}</td>
                        <!-- <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(donation.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">{{ donation.status }}</span>
                        </td> -->
                        <!-- <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex space-x-2">
                                <button @click="viewDonation(donation)" class="text-blue-400 hover:text-blue-300">View</button>
                                <button @click="refundDonation(donation.id)" class="text-red-400 hover:text-red-300">Refund</button>
                            </div>
                        </td> -->
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="!Array.isArray(donations) && donations.links" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <component :is="donations.prev_page_url ? 'button' : 'span'" :disabled="!donations.prev_page_url" @click="donations.prev_page_url && router.get(donations.prev_page_url)" :class="[
                            'relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md',
                            donations.prev_page_url ? 'bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600' : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700'
                        ]">Previous</component>
                    <component :is="donations.next_page_url ? 'button' : 'span'" :disabled="!donations.next_page_url" @click="donations.next_page_url && router.get(donations.next_page_url)" :class="[
                            'ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md',
                            donations.next_page_url ? 'bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600' : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700'
                        ]">Next</component>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Showing {{ donations.from || 0 }} to {{ donations.to || 0 }} of {{ donations.total || 0 }} results</p>
                    </div>

                    <div class="flex items-center space-x-2 order-2 sm:order-1">
                        <label for="per_page" class="text-sm font-medium text-gray-300 whitespace-nowrap">Show:</label>
                        <select id="per_page" v-model="perPage" class="block px-5 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-400 whitespace-nowrap">per page</span>
                    </div>

                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <component v-for="(link, index) in donations.links" :key="index" :is="link.url ? 'button' : 'span'" @click="link.url && router.get(link.url)" :class="[
                                    'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === donations.links.length - 1 ? 'rounded-r-md' : '',
                                    link.active ? 'bg-blue-600 border-blue-600 text-white z-10' : link.url ? 'bg-gray-700 border-gray-600 text-gray-200 hover:bg-gray-600' : 'bg-gray-800 border-gray-700 text-gray-500 cursor-not-allowed',
                                    'border'
                                ]" :disabled="!link.url" v-html="link.label" />
                        </nav>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Donation Modal -->
        <!-- <DonationModal :show="showModal" :donation="selectedDonation || {}" @close="closeModal" @updated="onDonationUpdated" /> -->
    </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
// import DonationModal from './DonationModal.vue'
import { formatAmount, formatDate } from '@/Utils/campaignHelpers.js'
import { debounce } from '@/Utils/debounce.js'

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
    ,
    showCampaignFilter: {
        type: Boolean,
        default: true
    }
})

// keep original campaigns prop available as fallback
const propCampaigns = computed(() => props.campaigns || [])

const showModal = ref(false)
const selectedDonation = ref(null)
const searchQuery = ref(props.filters?.search || '')
const perPage = ref(props.filters?.per_page || 10)
// const statusFilter = ref(props.filters?.status || 'all')
const endDateFilter = ref(props.filters?.end_date || '')
const campaignFilter = ref(props.filters?.campaign_id || '')
const sortBy = ref(props.filters?.sort_by || '')
const sortDir = ref(props.filters?.sort_dir || 'desc')

// Extract donations data from either array or paginated object
const donationsData = computed(() => {
    if (Array.isArray(props.donations)) {
        return props.donations
    }
    return props.donations?.data || []
})

// Build a list of campaigns present in the fetched donations results.
// Prefer this list for the campaign select, fallback to the campaigns prop.
const availableCampaigns = computed(() => {
    const map = {}
    for (const d of donationsData.value || []) {
        if (!d) continue
        const id = d.campaign_id ?? d.campaign_id
        const name = d.campaign_name || d.campaign || d.campaignName
        if (!name) continue
        const key = id ? String(id) : `name:${name}`
        if (!map[key]) map[key] = { id: id || null, name }
    }
    const arr = Object.values(map)
    return arr.length ? arr : propCampaigns.value
})

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return searchQuery.value || 
        campaignFilter.value ||
        endDateFilter.value
})

// Watch search input and update URL with debouncing
const debouncedSearch = debounce((filters) => {
    // Use named route to perform a fresh request and update results while typing
    router.get(window.location.pathname, 
        filters, 
        { 
            preserveState: false,
            replace: true 
        }
    )
}, 300)

// Helper function to get all current filters
const getCurrentFilters = () => ({
    search: searchQuery.value,
    // status: statusFilter.value !== 'all' ? statusFilter.value : null,
    end_date: endDateFilter.value || null,
    campaign_id: campaignFilter.value || null,
    per_page: perPage.value,
    sort_by: sortBy.value || null,
    sort_dir: sortDir.value || null
})

watch(searchQuery, () => {
    debouncedSearch(getCurrentFilters())
})

// watch(statusFilter, () => {
//     router.get(route(route().current()), 
//         getCurrentFilters(), 
//         { 
//             preserveState: true,
//             replace: true 
//         }
//     )
// })

// Watch campaign selection and update URL
watch(campaignFilter, () => {
    router.get(window.location.pathname,
        getCurrentFilters(),
        {
            preserveState: true,
            replace: true
        }
    )
})

watch(endDateFilter, () => {
    router.get(window.location.pathname, 
        getCurrentFilters(), 
        { 
            preserveState: true,
            replace: true 
        }
    )
})

// Watch per page changes and update URL immediately
watch(perPage, () => {
    router.get(window.location.pathname, 
        getCurrentFilters(), 
        { 
            preserveState: true,
            replace: true 
        }
    )
})

// Watch sort changes and update URL
// When sorting, request fresh data from server (don't preserve client state)
watch([sortBy, sortDir], () => {
    router.get(window.location.pathname, 
        getCurrentFilters(), 
        { 
            preserveState: false,
            replace: true 
        }
    )
})

const clearFilters = () => {
    searchQuery.value = ''
    endDateFilter.value = ''
    // Keep per_page as is
}

const viewDonation = (donation) => {
    selectedDonation.value = donation
    showModal.value = true
}

const toggleSort = (field) => {
    if (sortBy.value === field) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = field
        sortDir.value = 'desc'
    }
}


</script>
