<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import PaymentMethodModal from '@/Components/PaymentMethodModal.vue'
import { formatAmount, getStatusClass, getProgressPercentage } from '@/Utils/campaignHelpers.js'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    campaign: {
        type: Object,
        default: () => ({})
    },
    predefinedAmounts: {
        type: Array,
        default: () => [10, 25, 50, 100]
    }
})

const emit = defineEmits(['close', 'donated'])

const form = useForm({
    amount: '',
    campaign_id: null,
    donor_name: '',
    donor_email: '',
    message: '',
    // keep a payment_method key for future use
    payment_method: null,
})

const donationAmount = ref('')
const isSubmitting = ref(false)

// New payment modal state
const paymentModalVisible = ref(false)
const selectedPaymentMethod = ref(null)

// Safe campaign object for templates (avoid null errors when modal is closed)
const campaignData = computed(() => props.campaign || {})

const progressPercentage = computed(() => {
    return getProgressPercentage(campaignData.value)
})

const remainingAmount = computed(() => {
    return Math.max(0, (campaignData.value.goal_amount || 0) - (campaignData.value.current_amount || 0))
})

const toastVisible = ref(false)
const toastMessage = ref('')

const showToast = (message = '', ms = 3000) => {
    toastMessage.value = message
    toastVisible.value = true
    setTimeout(() => {
        toastVisible.value = false
    }, ms)
}

// Confirmation popup state
const confirmVisible = ref(false)
const confirmMessage = ref('')

const showConfirmation = (message = 'Donation confirmed') => {
    confirmMessage.value = message
    confirmVisible.value = true
}

const closeConfirmation = () => {
    confirmVisible.value = false
}

// Refactored submitDonation: accepts an optional paymentMethod selected from the payment modal
const submitDonation = (paymentMethod = null) => {
    form.amount = donationAmount.value;

    if (!form.amount || parseFloat(form.amount) <= 0) return

    // ensure campaign id is set on the Inertia form
    form.campaign_id = props.campaign?.id || form.campaign_id;

    if (paymentMethod) {
        form.payment_method = paymentMethod
    }

    isSubmitting.value = true

    form.post(route('donations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('Thank you for your donation!')
            showConfirmation('Thank you — your donation was received.')
            emit('donated')
            setTimeout(() => {
                isSubmitting.value = false
                close()
            }, 1200)
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0]
            showToast(firstError || 'An error occurred')
            isSubmitting.value = false
        },
        onFinish: () => {
            form.reset()
            donationAmount.value = ''
            form.payment_method = null
            selectedPaymentMethod.value = null
        }
    })
}

// New: open payment-method selection modal; if not authenticated redirect to login
const openPaymentModal = () => {
    const page = usePage();
    if (!page.props?.auth?.user) {
        router.get(route('login'))
        return;
    }

    // ensure campaign id is set (so PaymentMethodModal could show campaign info if needed)
    form.campaign_id = props.campaign?.id || form.campaign_id;

    paymentModalVisible.value = true
}

const close = () => {
    form.reset();
    donationAmount.value = '';
    isSubmitting.value = false;
    paymentModalVisible.value = false;
    emit('close');
}

const page = usePage()

const amounts = computed(() => {
    // prop takes precedence, then shared page prop, then default
    if (Array.isArray(props.predefinedAmounts) && props.predefinedAmounts.length > 0) return props.predefinedAmounts
    if (Array.isArray(page.props.predefinedAmounts) && page.props.predefinedAmounts.length > 0) return page.props.predefinedAmounts
    return [10,25,50,100]
})

const setQuickAmount = (amount) => {
    donationAmount.value = Number(amount).toFixed(2).toString();
}

const isSelected = (amount) => {
    // compare numeric values to avoid type coercion issues
    const a = Number(donationAmount.value);
    const b = Number(amount);
    return !isNaN(a) && !isNaN(b) && a === b;
}
</script>

<template>
    <Modal :show="show" @close="close" max-width="lg">

        <div class="px-6 py-4 border-b border-gray-600 bg-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-100">
                    Support This Campaign
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

        <div class="px-6 py-4 bg-gray-800 text-gray-100 max-h-96 overflow-y-auto">

            <div class="mb-6">
                <div class="flex justify-between items-start mb-3">
                    <h2 class="text-xl font-bold text-gray-100">{{ campaignData.name || 'Campaign' }}</h2>
                    <span :class="getStatusClass(campaignData.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                        {{ campaignData.status || '' }}
                    </span>
                </div>

                <p class="text-gray-300 text-sm mb-4">{{ campaignData.description }}</p>

                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-400">Raised: ${{ formatAmount(campaignData.current_amount) }}</span>
                        <span class="text-gray-400">Goal: ${{ formatAmount(campaignData.goal_amount) }}</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div
                            class="bg-[#FF2D20] h-3 rounded-full transition-all duration-300"
                            :style="{ width: `${Math.min(progressPercentage, 100)}%` }"
                        ></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>{{ progressPercentage }}% funded</span>
                        <span v-if="remainingAmount > 0">${{ formatAmount(remainingAmount) }} to go</span>
                        <span v-else class="text-green-400">Goal reached!</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs text-gray-400 mb-4">
                    <div>
                        <strong>Start Date:</strong> {{ campaignData.start_date ? new Date(campaignData.start_date).toLocaleDateString() : 'Not set' }}
                    </div>
                    <div>
                        <strong>End Date:</strong> {{ campaignData.end_date ? new Date(campaignData.end_date).toLocaleDateString() : 'No end date' }}
                    </div>
                </div>
            </div>

            <form @submit.prevent="openPaymentModal" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Donation Amount</label>

                    <div class="grid grid-cols-4 gap-2 mb-3">
                        <button
                            v-for="amount in amounts"
                            :key="amount"
                            type="button"
                            @click="setQuickAmount(amount)"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-md border transition-colors',
                                isSelected(amount)
                                    ? 'bg-[#FF2D20] border-[#FF2D20] text-white'
                                    : 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600'
                            ]"
                        >
                            ${{ amount }}
                        </button>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 text-sm">$</span>
                        </div>
                        <input
                            v-model="donationAmount"
                            type="number"
                            step="0.01"
                            min="1"
                            placeholder="Enter custom amount"
                            class="block w-full pl-8 pr-3 py-2 border border-gray-600 rounded-md leading-5 bg-gray-700 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:border-[#FF2D20] sm:text-sm"
                            required
                        />
                    </div>
                </div>

            </form>
        </div>

        <div class="px-6 py-4 border-t border-gray-600 bg-gray-800 flex justify-end space-x-3">
            <button
                type="button"
                @click="close"
                class="inline-flex justify-center rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-300 shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 focus:ring-offset-gray-800"
            >
                Cancel
            </button>

            <button
                type="button"
                @click="openPaymentModal"
                :disabled="!donationAmount || parseFloat(donationAmount) <= 0 || isSubmitting"
                class="inline-flex justify-center rounded-md border border-transparent bg-[#FF2D20] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                </svg>
                <span v-if="isSubmitting">Processing...</span>
                <span v-else>Donate ${{ donationAmount ? formatAmount(donationAmount) : '0.00' }}</span>
            </button>
        </div>
    </Modal>

    <!-- Payment method modal -->
    <PaymentMethodModal :show="paymentModalVisible" @close="paymentModalVisible = false" @pay="(method) => { paymentModalVisible = false; submitDonation(method) }" :campaign="campaignData" />

    <!-- Toast -->
    <div v-if="toastVisible" class="fixed right-4 bottom-6 z-50">
        <div class="max-w-sm w-full bg-gray-800 border border-gray-600 text-white px-4 py-3 rounded shadow-lg">
            {{ toastMessage }}
        </div>
    </div>

    <!-- Confirmation Modal (simple) -->
    <Modal :show="confirmVisible" @close="closeConfirmation" max-width="sm">
        <div class="px-6 py-4 bg-gray-800 text-gray-100">
            <h3 class="text-lg font-semibold">Donation Confirmed</h3>
            <p class="text-sm text-gray-300 mt-2">{{ confirmMessage }}</p>
        </div>
        <div class="px-6 py-4 bg-gray-800 flex justify-end">
            <button
                @click="closeConfirmation"
                class="inline-flex justify-center rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-600"
            >
                Close
            </button>
        </div>
    </Modal>
</template>
