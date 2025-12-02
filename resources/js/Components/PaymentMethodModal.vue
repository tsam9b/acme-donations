<script setup>
import { ref } from 'vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    campaign: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['close', 'pay'])

// For this dummy UI we'll hardcode some methods
const methods = ref([
    { id: 'card', name: 'Credit / Debit Card', description: 'Pay with card (dummy)' },
    { id: 'paypal', name: 'PayPal', description: 'Pay using PayPal (dummy)' },
    { id: 'dummy', name: 'Dummy Account', description: 'Test / Offline payment' }
])

const selected = ref(methods.value[0].id)

const pay = () => {
    // emit the selected payment method and close the modal
    emit('pay', selected.value)
}

const close = () => {
    emit('close')
}
</script>

<template>
    <Modal :show="show" @close="close" max-width="sm">
        <div class="px-6 py-4 bg-gray-800 text-gray-100">
            <h3 class="text-lg font-semibold">Select Payment Method</h3>
            <p class="text-sm text-gray-300 mt-2">Choose how you'd like to pay for this donation.</p>

            <div class="mt-4 space-y-2">
                <label v-for="m in methods" :key="m.id" class="flex items-center p-3 bg-gray-700 rounded-md cursor-pointer">
                    <input type="radio" :value="m.id" v-model="selected" class="mr-3" />
                    <span class="flex-1">
                        <span class="font-medium text-gray-100">{{ m.name }}</span>
                        <span class="block text-xs text-gray-400">{{ m.description }}</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-800 flex justify-end space-x-2">
            <button @click="close" class="inline-flex items-center rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-600">Cancel</button>
            <button @click="pay" class="inline-flex items-center rounded-md bg-[#FF2D20] px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Pay</button>
        </div>
    </Modal>
</template>
