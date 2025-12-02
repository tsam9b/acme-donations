<script setup>
import { ref, watch } from 'vue'
const props = defineProps({ value: { type: Array, default: () => [] } })
const emit = defineEmits(['update'])

const availableOptions = ['active', 'inactive', 'completed', 'cancelled']
const local = ref(Array.isArray(props.value) ? [...props.value] : [])

const toggle = (opt) => {
    const idx = local.value.indexOf(opt)
    if (idx === -1) local.value.push(opt)
    else local.value.splice(idx, 1)

    // Emit update with consistent order
    const selected = availableOptions.filter(o => local.value.includes(o))
    emit('update', selected)
}
</script>

<template>
    <section>
        <div class="flex flex-wrap gap-2">
            <template v-for="opt in availableOptions" :key="opt">
                <button
                    type="button"
                    :class="[ 'px-3 py-2 rounded', local.includes(opt) ? 'bg-green-600 text-white' : 'bg-gray-700 text-gray-100' ]"
                    @click="toggle(opt)"
                >
                    {{ opt }}
                </button>
            </template>
        </div>
    </section>
</template>

