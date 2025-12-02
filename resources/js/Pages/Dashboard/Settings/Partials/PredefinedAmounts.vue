<script setup>
import { ref, watch } from 'vue'
const props = defineProps({ value: { type: Array, default: () => [] } })
const emit = defineEmits(['update'])

const local = ref(Array.isArray(props.value) ? [...props.value] : [])

// Emit update whenever local changes
watch(local, (v) => { emit('update', v) }, { deep: true })

const addAmount = () => local.value.push(10)
const removeAmount = (i) => local.value.splice(i, 1)
</script>

<template>
    <section>
        <div class="space-y-3">
            <div v-for="(amt, i) in local" :key="i" class="flex items-center gap-2">
                <input type="number" v-model.number="local[i]" class="w-24 px-2 py-1 rounded bg-gray-700 text-gray-100" />
                <button @click="removeAmount(i)" class="px-2 py-1 bg-red-600 rounded text-white">Remove</button>
            </div>

            <div>
                <button @click="addAmount" class="px-3 py-2 bg-gray-700 text-gray-100 rounded">Add Amount</button>
            </div>
        </div>
    </section>
</template>
