<template>
  <div class="p-6 max-w-xl mx-auto">
    <Head title="Home" />

    <h1 class="text-2xl font-bold mb-4">Your Municipalities</h1>

    <!-- Insert Form -->
    <form @submit.prevent="submit" class="mb-6 flex space-x-2">
      <input
        v-model="form.name"
        type="text"
        placeholder="Municipality Name"
        class="border rounded p-2 flex-1"
      />
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        Add
      </button>
    </form>

    <!-- List of Municipalities -->
    <ul v-if="municipalities.length" class="space-y-2">
      <li
        v-for="muni in municipalities"
        :key="muni.id"
        class="border p-3 rounded"
      >
        <h2 class="font-semibold text-lg">{{ muni.id }}. {{ muni.name }}</h2>
      </li>
    </ul>

    <p v-else class="text-gray-500">No municipalities yet.</p>
  </div>
</template>

<script lang="ts" setup>
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

interface Municipality {
  id: number
  name: string
}

// Props passed from Laravel/Inertia
const props = defineProps<{ municipalities: Municipality[] }>()

// Reactive list
const municipalities = ref([...props.municipalities])

// Inertia form
const form = useForm({
  name: '',
})

// Submit handler
function submit() {
  form.post('/municipalities', {
    onSuccess: (page) => {
      // first cast to unknown, then our expected props type
      const props = page.props as unknown as { municipalities: Municipality[] }
      municipalities.value = props.municipalities
      form.reset('name')
    },
  })
}
</script>
