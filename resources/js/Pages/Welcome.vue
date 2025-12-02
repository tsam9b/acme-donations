<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CampaignCard from '@/Components/CampaignCard.vue';
import DonationModal from '@/Components/DonationModal.vue';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from '@/Utils/debounce.js';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    campaigns: { type: [Array, Object], default: () => [] },
    predefinedAmounts: { type: Array, default: () => [10, 25, 50, 100] },
    availableStatuses: { type: Array, default: () => ['active', 'inactive', 'completed', 'cancelled'] },
    welcomeTitle: { type: String, default: 'Welcome to Acme Donations!' },
    welcomeSubtitle: { type: String, default: 'Support a campaign and make a difference.' },
    filters: { type: Object, default: () => ({}) }
});

// Reactive filter variables
const searchQuery = ref(window.$page?.props?.filters?.search ?? (new URLSearchParams(window.location.search).get('search') || ''));
const perPage = ref(window.$page?.props?.filters?.per_page ?? (new URLSearchParams(window.location.search).get('per_page') || 12));
const endDateFilter = ref(window.$page?.props?.filters?.end_date ?? (new URLSearchParams(window.location.search).get('end_date') || ''));
const statusFilter = ref(window.$page?.props?.filters?.status ?? (new URLSearchParams(window.location.search).get('status') || 'all'));

// Modal state
const showDonationModal = ref(false);
const selectedCampaign = ref(null);
const refreshing = ref(false);

const hasActiveFilters = computed(() => !!(searchQuery.value || endDateFilter.value || (statusFilter.value && statusFilter.value !== 'all')));

const getCurrentFilters = () => ({
    search: searchQuery.value || null,
    end_date: endDateFilter.value || null,
    status: statusFilter.value && statusFilter.value !== 'all' ? statusFilter.value : null,
    per_page: perPage.value || 12
});

const debouncedSearch = debounce((filters) => {
    router.get('/', filters, { preserveState: true, replace: true });
}, 300);

watch(searchQuery, () => debouncedSearch(getCurrentFilters()));
watch([endDateFilter, perPage, statusFilter], () => {
    router.get('/', getCurrentFilters(), { preserveState: true, replace: true });
});

const clearFilters = () => {
    searchQuery.value = '';
    endDateFilter.value = '';
};

const openDonationModal = (campaign) => {
    // Prevent donations to inactive, cancelled, or completed campaigns
    if (campaign.status === 'inactive' || campaign.status === 'cancelled' || campaign.status === 'completed') {
        return;
    }
    selectedCampaign.value = campaign;
    showDonationModal.value = true;
};

const closeDonationModal = () => {
    showDonationModal.value = false;
    selectedCampaign.value = null;
};

const handleDonation = () => {
    refreshing.value = true;
    router.get('/', getCurrentFilters(), {
        preserveState: true,
        replace: true,
        onStart: () => { refreshing.value = true; },
        onFinish: () => { refreshing.value = false; }
    });
};
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 relative">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
            alt=""
        />
        <div class="relative flex min-h-screen flex-col items-center justify-top selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <!-- Header -->
                <header class="grid grid-cols-2 lg:grid-cols-3 items-top gap-2 py-10">
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <svg class="h-12 w-auto text-white lg:h-16 lg:text-[#FF2D20]" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- SVG Path omitted for brevity -->
                        </svg>
                    </div>
                    <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                Log in
                            </Link>
                            <Link v-if="canRegister" :href="route('register')" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                                Register
                            </Link>
                        </template>
                    </nav>
                </header>

                <!-- Main -->
                <main class="mt-6">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-black dark:text-white mb-4">{{ welcomeTitle }}</h1>
                        <p class="text-gray-600 dark:text-gray-300">{{ welcomeSubtitle }}</p>
                    </div>

                    <!-- Filters -->
                    <div class="mb-8">
                        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-4 items-start sm:items-center justify-between">
                            <!-- Search -->
                            <div class="flex-1 min-w-0">
                                <div class="relative w-full">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search campaigns by name..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:border-[#FF2D20] sm:text-sm"
                                    />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <button @click="searchQuery=''" class="text-gray-400 hover:text-gray-200 focus:outline-none">&times;</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="flex flex-wrap gap-3 items-center">
                                <select v-model="perPage" class="block px-5 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:border-[#FF2D20]">
                                    <option value="6">6 per page</option>
                                    <option value="12">12 per page</option>
                                    <option value="24">24 per page</option>
                                    <option value="48">48 per page</option>
                                </select>

                                <select v-model="statusFilter" class="block px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:border-[#FF2D20]">
                                    <option value="all">All statuses</option>
                                    <option v-for="status in availableStatuses" :key="status" :value="status">
                                        {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                    </option>
                                </select>

                                <input v-model="endDateFilter" type="date" class="block px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-gray-100 sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:border-[#FF2D20]" />

                                <button @click="clearFilters" class="w-full sm:w-auto px-3 py-2 text-sm font-medium text-gray-300 bg-gray-600 border border-gray-600 rounded-md hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 focus:ring-offset-gray-800 transition duration-150">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Campaign Grid -->
                    <div v-if="$page.props.campaigns?.data?.length > 0 || campaigns.length > 0" class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">
                        <CampaignCard
                            v-for="campaign in ($page.props.campaigns?.data || campaigns)"
                            :key="campaign.id"
                            :campaign="campaign"
                            @click="openDonationModal(campaign)"
                            :class="[
                                'transition-transform duration-200',
                                campaign.status === 'active' ? 'cursor-pointer hover:scale-105' : 'opacity-60 cursor-not-allowed'
                            ]"
                        />
                    </div>

                    <!-- Empty state -->
                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-black dark:text-white mb-2">
                            {{ hasActiveFilters ? 'No campaigns match your filters' : 'No Active Campaigns' }}
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            {{ hasActiveFilters ? 'Try adjusting your search criteria or clear the filters.' : 'There are currently no active donation campaigns. Check back later!' }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <button v-if="hasActiveFilters" @click="clearFilters" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition duration-150">
                                Clear Filters
                            </button>
                            <Link v-if="canLogin && !$page.props.auth.user" :href="route('login')" class="inline-flex items-center px-4 py-2 bg-[#FF2D20] text-white font-semibold rounded-lg hover:bg-red-700 transition duration-150">
                                Login to Create Campaign
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="$page.props.campaigns?.links" class="mt-8">
                        <nav class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <component :is="$page.props.campaigns.prev_page_url ? 'button' : 'span'" :disabled="!$page.props.campaigns.prev_page_url" @click="$page.props.campaigns.prev_page_url && router.get($page.props.campaigns.prev_page_url)" :class="['relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md', $page.props.campaigns.prev_page_url ? 'bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600' : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700']">
                                    Previous
                                </component>
                                <component :is="$page.props.campaigns.next_page_url ? 'button' : 'span'" :disabled="!$page.props.campaigns.next_page_url" @click="$page.props.campaigns.next_page_url && router.get($page.props.campaigns.next_page_url)" :class="['ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md', $page.props.campaigns.next_page_url ? 'bg-gray-700 text-gray-200 hover:bg-gray-600 border border-gray-600' : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700']">
                                    Next
                                </component>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-400">
                                        Showing {{ $page.props.campaigns.from || 0 }} to {{ $page.props.campaigns.to || 0 }} of {{ $page.props.campaigns.total || 0 }} campaigns
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <component v-for="(link, index) in $page.props.campaigns.links" :key="index" :is="link.url ? 'button' : 'span'" @click="link.url && router.get(link.url)" :class="[
                                            'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === $page.props.campaigns.links.length - 1 ? 'rounded-r-md' : '',
                                            link.active ? 'bg-[#FF2D20] border-[#FF2D20] text-white z-10' : link.url ? 'bg-gray-700 border-gray-600 text-gray-200 hover:bg-gray-600' : 'bg-gray-800 border-gray-700 text-gray-500 cursor-not-allowed',
                                            'border'
                                        ]" :disabled="!link.url" v-html="link.label"/>
                                    </nav>
                                </div>
                            </div>
                        </nav>
                    </div>
                </main>
            </div>

            <!-- Footer -->
            <footer class="py-16 text-center text-sm text-black dark:text-white/70">ACME Corp 2025</footer>

            <!-- Donation Modal -->
            <DonationModal :show="showDonationModal" :campaign="selectedCampaign" :predefined-amounts="predefinedAmounts" @close="closeDonationModal" @donated="handleDonation" />
        </div>
    </div>
</template>
