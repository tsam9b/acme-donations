<script setup>
import { formatAmount, getStatusClass, getProgressPercentage } from '@/Utils/campaignHelpers.js'

defineProps({
    campaign: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <div class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
        <div class="pt-3 sm:pt-5 flex-1">
            <div class="flex justify-between items-start mb-2">
                <h2 class="text-xl font-semibold text-black dark:text-white">
                    {{ campaign.name }}
                </h2>
                <span :class="getStatusClass(campaign.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-3">
                    {{ campaign.status }}
                </span>
            </div>

            <p class="mt-2 text-sm/relaxed text-gray-600 dark:text-gray-300">
                {{ campaign.description || 'No description available.' }}
            </p>

            <div class="mt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Goal:</span>
                    <span class="font-medium text-black dark:text-white">${{ formatAmount(campaign.goal_amount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Raised:</span>
                    <span class="font-medium text-black dark:text-white">${{ formatAmount(campaign.current_amount) }}</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div 
                        class="bg-[#FF2D20] h-2 rounded-full transition-all duration-300" 
                        :style="{ width: `${Math.min(getProgressPercentage(campaign), 100)}%` }"
                    ></div>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 text-right">
                    {{ getProgressPercentage(campaign) }}% of goal reached
                </div>
            </div>

            <div v-if="campaign.end_date" class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Ends: {{ new Date(campaign.end_date).toLocaleDateString() }}
            </div>
        </div>
    </div>
</template>
