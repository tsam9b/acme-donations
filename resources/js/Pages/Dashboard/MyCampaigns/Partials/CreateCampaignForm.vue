<template>
    <section>
        <header class="mb-4">
            <h3 class="text-lg font-medium text-gray-100">
                Create New Campaign
            </h3>
            <p class="mt-1 text-sm text-gray-400">
                Set up a new donation campaign with your goals and details.
            </p>
        </header>

        <form @submit.prevent="createCampaign" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300">Campaign Name</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="e.g., Help Build a School"
                    required
                />
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-400">
                    {{ form.errors.name }}
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-300">Description</label>
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="Describe your campaign and what the funds will be used for..."
                />
                <div v-if="form.errors.description" class="mt-1 text-sm text-red-400">
                    {{ form.errors.description }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="goal_amount" class="block text-sm font-medium text-gray-300">Goal Amount ($)</label>
                    <input
                        id="goal_amount"
                        v-model="form.goal_amount"
                        type="number"
                        step="0.01"
                        required
                        min="0"
                        class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="10000.00"
                    />
                    <div v-if="form.errors.goal_amount" class="mt-1 text-sm text-red-400">
                        {{ form.errors.goal_amount }}
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-300">Status</label>
                    <select
                        id="status"
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
                    <label for="start_date" class="block text-sm font-medium text-gray-300">Start Date</label>
                    <input
                        id="start_date"
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
                    <label for="end_date" class="block text-sm font-medium text-gray-300">End Date</label>
                    <input
                        id="end_date"
                        v-model="form.end_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    />
                    <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-400">
                        {{ form.errors.end_date }}
                    </div>
                </div>

               
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button
                    type="button"
                    @click="$emit('cancel')"
                    class="inline-flex justify-center rounded-md border border-gray-500 bg-gray-600 px-4 py-2 text-sm font-medium text-gray-200 shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ form.processing ? 'Creating...' : 'Create Campaign' }}
                </button>
            </div>
        </form>
    </section>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const emit = defineEmits(['campaign-created', 'cancel'])

const form = useForm({
    name: '',
    description: '',
    goal_amount: '',
    start_date: new Date().toISOString().split('T')[0], // Today's date
    end_date: '',
    status: 'active'
})

const createCampaign = () => {
    form.post('/campaigns', {
        onSuccess: () => {
            form.reset()
            emit('campaign-created')
        },
        onError: (errors) => {
            console.log('Validation errors:', errors)
        }
    })
}
</script>
