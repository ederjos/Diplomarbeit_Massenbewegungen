<template>
  <div class="p-6 max-w-2xl mx-auto">
    <Head title="Home" />

    <h1 class="text-3xl font-bold mb-6 text-gray-800 text-center">Your Municipalities</h1>

    <!-- Insert Form -->
    <form @submit.prevent="submit" class="mb-8 flex gap-3">
      <input
        v-model="form.name"
        type="text"
        placeholder="Enter municipality name..."
        class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
      />
      <button
        type="submit"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition"
      >
        Add
      </button>
    </form>

    <!-- List of Municipalities -->
    <ul v-if="municipalities.length" class="space-y-4">
      <li
        v-for="muni in municipalities"
        :key="muni.id"
        class="flex justify-between items-center p-4 bg-white rounded-xl shadow hover:shadow-lg transition"
      >
        <div>
          <h2 class="font-semibold text-lg text-gray-800">{{ muni.id }}. {{ muni.name }}</h2>
        </div>
        <button
          @click="deleteMunicipality(muni.id)"
          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow-sm transition"
        >
          Delete
        </button>
      </li>
    </ul>

    <p v-else class="text-gray-400 text-center mt-6 italic">No municipalities added yet.</p>
  </div>
  <div>
    <Leaflet />
  </div>
</template>

<script lang="ts" setup>
import { Head, useForm, router } from '@inertiajs/vue3'
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
const form = useForm({ // cares about csrf automatically
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
  });
}

// Delete handler
function deleteMunicipality(id: number) {
  if (!confirm('Are you sure you want to delete this municipality?')) return

  // Router like AJAX (without reloading the page)
  router.delete(`/municipalities/${id}`, {
    onSuccess: (page) => {
      const props = page.props as unknown as { municipalities: Municipality[] }
      municipalities.value = props.municipalities
    },
  })
}
</script>
