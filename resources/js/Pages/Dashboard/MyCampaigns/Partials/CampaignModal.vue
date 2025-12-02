<template>
    <Modal :show="show" @close="close" max-width="lg">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-600 bg-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-100">
                    {{ isEditing ? 'Edit Campaign' : 'Campaign Details' }}
                </h3>
                <button 
                    @click="close"
                    class="text-gray-400 hover:text-gray-200 focus:outline-none"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4 space-y-4 bg-gray-800">
            <div v-if="!isEditing" class="space-y-4">
                <!-- View Mode -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Campaign Name</label>
                        <div class="text-sm text-gray-100 p-3 bg-gray-700 rounded-md">
                            {{ campaign.name }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                        <div class="text-sm p-3 bg-gray-700 rounded-md">
                            <span :class="getStatusClass(campaign.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                {{ campaign.status }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Goal Amount</label>
                        <div class="text-sm text-gray-100 p-3 bg-gray-700 rounded-md">
                            ${{ formatAmount(campaign.goal_amount) }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Current Amount</label>
                        <div class="text-sm text-gray-100 p-3 bg-gray-700 rounded-md">
                            ${{ formatAmount(campaign.current_amount) }}
                            <span class="text-xs text-gray-400 ml-2">
                                ({{ getProgressPercentage(campaign) }}% of goal)
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Start Date</label>
                        <div class="text-sm text-gray-100 p-3 bg-gray-700 rounded-md">
                            {{ formatDate(campaign.start_date) }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">End Date</label>
                        <div class="text-sm text-gray-100 p-3 bg-gray-700 rounded-md">
                            {{ formatDate(campaign.end_date) }}
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                    <div class="text-sm text-gray-100 p-3 bg-gray-700 rounded-md min-h-[80px]">
                        {{ campaign.description || 'No description provided.' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-400">
                    <div>
                        <strong>Created:</strong> {{ formatDateTime(campaign.created_at) }}
                    </div>
                    <div>
                        <strong>Last Updated:</strong> {{ campaign.updated_at ? formatDateTime(campaign.updated_at) : 'Never' }}
                    </div>
                </div>
            </div>

            <form v-else @submit.prevent="updateCampaign" class="space-y-4">
                <!-- Edit Mode -->
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-300">Campaign Name</label>
                    <input
                        id="edit_name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required
                    />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-400">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label for="edit_description" class="block text-sm font-medium text-gray-300">Description</label>
                    <textarea
                        id="edit_description"
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    />
                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-400">
                        {{ form.errors.description }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_goal_amount" class="block text-sm font-medium text-gray-300">Goal Amount ($)</label>
                        <input
                            id="edit_goal_amount"
                            v-model="form.goal_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                        <div v-if="form.errors.goal_amount" class="mt-1 text-sm text-red-400">
                            {{ form.errors.goal_amount }}
                        </div>
                    </div>

                    <div>
                        <label for="edit_status" class="block text-sm font-medium text-gray-300">Status</label>
                        <select
                            id="edit_status"
                            v-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <div v-if="form.errors.status" class="mt-1 text-sm text-red-400">
                            {{ form.errors.status }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_start_date" class="block text-sm font-medium text-gray-300">Start Date</label>
                        <input
                            id="edit_start_date"
                            v-model="form.start_date"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            required
                        />
                        <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-400">
                            {{ form.errors.start_date }}
                        </div>
                    </div>

                    <div>
                        <label for="edit_end_date" class="block text-sm font-medium text-gray-300">End Date</label>
                        <input
                            id="edit_end_date"
                            v-model="form.end_date"
                            type="date"
                            class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        />
                        <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-400">
                            {{ form.errors.end_date }}
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-600 bg-gray-700 flex justify-end space-x-3">
            <button
                type="button"
                @click="close"
                class="inline-flex justify-center rounded-md border border-gray-500 bg-gray-600 px-4 py-2 text-sm font-medium text-gray-200 shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                {{ isEditing ? 'Cancel' : 'Close' }}
            </button>
            
            <button
                v-if="!isEditing"
                type="button"
                @click="startEditing"
                class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Edit Campaign
            </button>
            
            <button
                v-else
                type="button"
                @click="updateCampaign"
                :disabled="form.processing"
                class="inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
            >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import { formatAmount, formatDate, formatDateTime, getProgressPercentage, getStatusClass } from '@/Utils/campaignHelpers.js'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    campaign: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['close', 'updated'])

const isEditing = ref(false)

const form = useForm({
    name: '',
    description: '',
    goal_amount: '',
    start_date: '',
    end_date: '',
    status: ''
})

// Watch for campaign changes to populate form
watch(
    () => props.campaign,
    (campaign) => {
        if (campaign && Object.keys(campaign).length > 0) {
            form.name = campaign.name || ''
            form.description = campaign.description || ''
            form.goal_amount = campaign.goal_amount || ''
            form.start_date = campaign.start_date || ''
            form.end_date = campaign.end_date || ''
            form.status = campaign.status || 'active'
        }
    },
    { immediate: true }
)

const startEditing = () => {
    isEditing.value = true
}

const updateCampaign = () => {
    form.patch(`/campaigns/${props.campaign.id}`, {
        onSuccess: () => {
            isEditing.value = false
            emit('updated')
            close()
        }
    })
}

const close = () => {
    isEditing.value = false
    form.reset()
    form.clearErrors()
    emit('close')
}
</script>
