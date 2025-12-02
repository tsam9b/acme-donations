<script setup>
import { reactive, watch } from 'vue'

const DEFAULT = { welcome: { title: '', subtitle: '' } }
const props = defineProps({ value: { type: Object, default: () => ({ welcome: { title: '', subtitle: '' } }) } })
const emit = defineEmits(['update'])

// Build an initial plain object ensuring welcome exists
const initial = JSON.parse(JSON.stringify(props.value || {}))
if (!initial.welcome) initial.welcome = { title: '', subtitle: '' }

// Use a reactive deep-cloned local copy so nested fields are reactive in the template
const local = reactive(initial)

// Emit updates whenever local changes; emit a deep-cloned value to avoid leaking reactive internals
watch(local, () => {
  const out = JSON.parse(JSON.stringify(local || DEFAULT))
  if (!out.welcome) out.welcome = { title: '', subtitle: '' }
  emit('update', out)
}, { deep: true })
</script>

<template>
  <section>
    <div class="space-y-4">
      <div>
        <label class="block text-sm text-gray-300">Welcome title</label>
        <textarea v-model="local.welcome.title" class="mt-1 block w-full rounded bg-gray-700 text-gray-100 p-2" rows="3"></textarea>
      </div>

      <div>
        <label class="block text-sm text-gray-300">Welcome subtitle</label>
        <input v-model="local.welcome.subtitle" class="mt-1 block w-full rounded bg-gray-700 text-gray-100 p-2" />
      </div>
    </div>
  </section>
</template>
