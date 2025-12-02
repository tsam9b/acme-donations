<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import Messages from './Partials/Headlines.vue'
import PredefinedAmounts from './Partials/PredefinedAmounts.vue'
import AvailableStatus from './Partials/AvailableStatus.vue'

const props = defineProps({
    // server should pass existing settings as an object
    settings: { type: Object, default: () => ({}) }
})

// Provide defaults so the template can safely read nested keys even when server returns empty
const defaultSettings = {
    messages: {},
    predefinedAmounts: [],
    availableStatus: []
}

// Inertia form holds the settings object; posting this will save JSON to DB
const form = useForm({ settings: { ...defaultSettings, ...(props.settings || {}) } })

console.log('defaultSettings', defaultSettings);
console.log('Loaded settings:', form.settings);

const updateMessages = (val) => {
    const base = form.settings || { ...defaultSettings }
    form.settings = { ...base, messages: val }
}

const updatePredefined = (val) => {
    const base = form.settings || { ...defaultSettings }
    form.settings = { ...base, predefinedAmounts: val }
}

const updateStatus = (val) => {
    const base = form.settings || { ...defaultSettings }
    form.settings = { ...base, availableStatus: val }
}

import { ref } from 'vue'

const savedPart = ref(null)


// Save a specific part; on success, set savedPart so UI shows per-part confirmation
const savePart = (partName) => {
    form.post(route('settings.update'), {
        onSuccess: () => {
            savedPart.value = partName
            setTimeout(() => savedPart.value = null, 2000)
        }
    })
}
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-100">Settings</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-gray-800 p-4 shadow sm:rounded-lg sm:p-8">

                    <!-- Messages card -->
                    <div class="bg-gray-900 p-6 rounded mb-6">
                        <h3 class="text-lg font-medium text-gray-100 mb-3">Titles</h3>
                        <p class="text-sm text-gray-400 mb-4">Customize welcome page title and subtitle</p>

                        <Messages
                            :value="(form.settings && form.settings.messages) ? form.settings.messages : {}"
                            @update="updateMessages"
                        />

                        <div class="mt-4 flex items-center justify-start space-x-3">
                            <button
                                @click.prevent="savePart('messages')"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-[#FF2D20] text-white font-semibold rounded-lg hover:bg-red-700 transition duration-150"
                            >
                                Save Titles
                            </button>

                            <p v-if="savedPart === 'messages'" class="mt-1 text-sm text-gray-300">Saved.</p>
                            <div v-if="form.processing && savedPart !== 'messages'" class="text-sm text-gray-400">Saving…</div>
                        </div>
                    </div>

                    <!-- Predefined Amounts card -->
                    <div class="bg-gray-900 p-6 rounded mb-6">
                        <h3 class="text-lg font-medium text-gray-100 mb-3">Predefined Amounts</h3>
                        <p class="text-sm text-gray-400 mb-4">Manage quick-amount buttons shown in the donation modal.</p>

                        <PredefinedAmounts
                            :value="(form.settings && form.settings.predefinedAmounts) ? form.settings.predefinedAmounts : []"
                            @update="updatePredefined"
                        />

                        <div class="mt-4 flex items-center justify-start space-x-3">
                            <button
                                @click.prevent="savePart('predefinedAmounts')"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-[#FF2D20] text-white font-semibold rounded-lg hover:bg-red-700 transition duration-150"
                            >
                                Save Amounts
                            </button>

                            <p v-if="savedPart === 'predefinedAmounts'" class="mt-1 text-sm text-gray-300">Saved.</p>
                            <div v-if="form.processing && savedPart !== 'predefinedAmounts'" class="text-sm text-gray-400">Saving…</div>
                        </div>
                    </div>

                    <!-- Available Statuses card -->
                    <div class="bg-gray-900 p-6 rounded mb-6">
                        <h3 class="text-lg font-medium text-gray-100 mb-3">Available Statuses</h3>
                        <p class="text-sm text-gray-400 mb-4">Only the campaigns with these statuses will appear on frontend</p>

                        <AvailableStatus
                            :value="(form.settings && form.settings.availableStatus) ? form.settings.availableStatus : []"
                            @update="updateStatus"
                        />

                        <div class="mt-4 flex items-center justify-start space-x-3">
                            <button
                                @click.prevent="savePart('availableStatus')"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-[#FF2D20] text-white font-semibold rounded-lg hover:bg-red-700 transition duration-150"
                            >
                                Save Statuses
                            </button>

                            <p v-if="savedPart === 'availableStatus'" class="mt-1 text-sm text-gray-300">Saved.</p>
                            <div v-if="form.processing && savedPart !== 'availableStatus'" class="text-sm text-gray-400">Saving…</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
