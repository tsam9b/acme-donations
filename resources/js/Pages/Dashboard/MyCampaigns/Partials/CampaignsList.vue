<template>
    <section>

        <!-- Combined Search + Filters + Create (single row on desktop, stacked on mobile) -->
        <div class="mb-6 p-4 bg-gray-800 border border-gray-700 rounded-lg">
            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between w-full">
                <div class="flex-1 w-full">
                    <div class="flex flex-col sm:flex-row gap-4 w-full items-start sm:items-center">
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
                                    placeholder="Search campaigns by name..."
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

                        <!-- Inline Filters -->
                        <div class="flex items-end sm:items-center gap-3 w-full sm:w-auto">
                            <div class="w-full sm:w-48">
                                <label class="sr-only">Status</label>
                                <select
                                    v-model="statusFilter"
                                    class="block w-full px-3 py-2 border border-gray-600 rounded-md leading-5 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="all">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="w-full sm:w-44">
                                <label class="sr-only">End Date</label>
                                <input
                                    v-model="endDateFilter"
                                    type="date"
                                    class="block w-full px-3 py-2 border border-gray-600 rounded-md leading-5 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                />
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

                <!-- Create New Campaign Button -->
                <div class="w-full lg:w-auto">
                    <button
                        @click="showCreateForm = !showCreateForm"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="create-campaign-button">Create New Campaign</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Campaign Form -->
        <div v-if="showCreateForm" class="mb-6 p-6 bg-gray-800 border border-gray-700 rounded-lg">
            <CreateCampaignForm @campaign-created="onCampaignCreated" @cancel="showCreateForm = false" />
        </div>

        <!-- Campaigns Table -->
        <div class="overflow-hidden shadow ring-1 ring-gray-600 ring-opacity-5 rounded-lg">
            <div v-if="campaignsData.length === 0" class="text-center py-8 bg-gray-800">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-400">
                    {{ hasActiveFilters ? 'No campaigns found matching your filters.' : 'No campaigns yet. Create your first campaign!' }}
                </p>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-600 bg-gray-800">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('name')" class="inline-flex items-center space-x-2">
                                <span>Campaign</span>
                                <span v-if="sortBy === 'name'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('goal_amount')" class="inline-flex items-center space-x-2">
                                <span>Goal Amount</span>
                                <span v-if="sortBy === 'goal_amount'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('current_amount')" class="inline-flex items-center space-x-2">
                                <span>Raised</span>
                                <span v-if="sortBy === 'current_amount'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th v-if="showOwner" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('owner')" class="inline-flex items-center space-x-2">
                                <span>Owner</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('status')" class="inline-flex items-center space-x-2">
                                <span>Status</span>
                                <span v-if="sortBy === 'status'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            <button @click="toggleSort('end_date')" class="inline-flex items-center space-x-2">
                                <span>End Date</span>
                                <span v-if="sortBy === 'end_date'">{{ sortDir === 'asc' ? '▲' : '▼' }}</span>
                            </button>
                        </th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-600">
                    <tr v-for="campaign in campaignsData" :key="campaign.id" class="hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-100 cursor-pointer hover:text-blue-400" @click="viewCampaign(campaign)">
                                    {{ campaign.name }}
                                </div>
                                <div class="text-sm text-gray-400">{{ campaign.description?.substring(0, 60) }}...</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                            ${{ formatAmount(campaign.goal_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-100">${{ formatAmount(campaign.current_amount) }}</div>
                            <div class="text-xs text-gray-400">
                                {{ getProgressPercentage(campaign) }}% of goal
                            </div>
                        </td>
                        <td v-if="showOwner" class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-100">{{ campaign.owner_name || '—' }}</div>
                                <div class="text-sm text-gray-400">{{ campaign.owner_email || '—' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(campaign.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                {{ campaign.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                            {{ formatDate(campaign.end_date, 'No end date') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex space-x-2">
                                <button
                                    @click="viewCampaign(campaign)"
                                    class="text-blue-400 hover:text-blue-300"
                                >
                                    View
                                </button>
                                <button
                                    @click="editCampaign(campaign)"
                                    class="text-green-400 hover:text-green-300"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteCampaign(campaign.id)"
                                    class="text-red-400 hover:text-red-300"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="!Array.isArray(campaigns) && campaigns.links" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <component
                        :is="campaigns.prev_page_url ? 'button' : 'span'"
                        :disabled="!campaigns.prev_page_url"
                        @click="campaigns.prev_page_url && router.get(campaigns.prev_page_url)"
                        :class="[
                            'relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md',
                            campaigns.prev_page_url
                                ? 'bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600'
                                : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700'
                        ]"
                    >
                        Previous
                    </component>
                    <component
                        :is="campaigns.next_page_url ? 'button' : 'span'"
                        :disabled="!campaigns.next_page_url"
                        @click="campaigns.next_page_url && router.get(campaigns.next_page_url)"
                        :class="[
                            'ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md',
                            campaigns.next_page_url
                                ? 'bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600'
                                : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700'
                        ]"
                    >
                        Next
                    </component>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-400">
                            Showing {{ campaigns.from || 0 }} to {{ campaigns.to || 0 }} of {{ campaigns.total || 0 }} results
                        </p>
                    </div>
                                    <!-- Per Page Selector -->
                <div class="flex items-center space-x-2 order-2 sm:order-1">
                    <label for="per_page" class="text-sm font-medium text-gray-300 whitespace-nowrap">
                        Show:
                    </label>
                    <select
                        id="per_page"
                        v-model="perPage"
                        class="block px-5 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
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
                            <component
                                v-for="(link, index) in campaigns.links"
                                :key="index"
                                :is="link.url ? 'button' : 'span'"
                                @click="link.url && router.get(link.url)"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === campaigns.links.length - 1 ? 'rounded-r-md' : '',
                                    link.active
                                        ? 'bg-blue-600 border-blue-600 text-white z-10'
                                        : link.url
                                            ? 'bg-gray-700 border-gray-600 text-gray-200 hover:bg-gray-600'
                                            : 'bg-gray-800 border-gray-700 text-gray-500 cursor-not-allowed',
                                    'border'
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Campaign Modal -->
        <CampaignModal
            :show="showModal"
            :campaign="selectedCampaign || {}"
            @close="closeModal"
            @updated="onCampaignUpdated"
        />
    </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import CreateCampaignForm from './CreateCampaignForm.vue'
import CampaignModal from './CampaignModal.vue'
import { formatAmount, formatDate, getProgressPercentage, getStatusClass } from '@/Utils/campaignHelpers.js'
import { debounce } from '@/Utils/debounce.js'

const props = defineProps({
    campaigns: {
        type: [Array, Object],
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
    ,
    showOwner: {
        type: Boolean,
        default: false
    }
})

const showCreateForm = ref(false)
const showModal = ref(false)
const selectedCampaign = ref(null)
const searchQuery = ref(props.filters?.search || '')
const perPage = ref(props.filters?.per_page || 10)
const statusFilter = ref(props.filters?.status || 'all')
const sortBy = ref(props.filters?.sort_by || '')
const sortDir = ref(props.filters?.sort_dir || 'desc')
const endDateFilter = ref(props.filters?.end_date || '')

// Extract campaigns data from either array or paginated object
const campaignsData = computed(() => {
    if (Array.isArray(props.campaigns)) {
        return props.campaigns
    }
    return props.campaigns?.data || []
})

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return searchQuery.value ||
           statusFilter.value !== 'all' ||
           endDateFilter.value
})

// Watch search input and update URL with debouncing
const debouncedSearch = debounce((filters) => {
    router.get(window.location.pathname,
        filters,
        {
            preserveState: true,
            replace: true
        }
    )
}, 300)

// Helper function to get all current filters
const getCurrentFilters = () => ({
    search: searchQuery.value,
    status: statusFilter.value !== 'all' ? statusFilter.value : null,
    end_date: endDateFilter.value || null,
    per_page: perPage.value
    ,
    sort_by: sortBy.value || null,
    sort_dir: sortDir.value || null
})

watch(searchQuery, () => {
    debouncedSearch(getCurrentFilters())
})

watch(statusFilter, () => {
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
    statusFilter.value = 'all'
    endDateFilter.value = ''
    // Keep per_page as is
}

const editCampaign = (campaign) => {
    selectedCampaign.value = campaign
    showModal.value = true
}

const viewCampaign = (campaign) => {
    selectedCampaign.value = campaign
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedCampaign.value = null
}

const onCampaignUpdated = () => {
    // Refresh the page to show updated campaign
    router.reload()
}

const deleteCampaign = (campaignId) => {
    if (confirm('Are you sure you want to delete this campaign?')) {
        router.delete(`/campaigns/${campaignId}`)
    }
}

const onCampaignCreated = () => {
    showCreateForm.value = false
    // Refresh the page to show new campaign
    router.reload()
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
