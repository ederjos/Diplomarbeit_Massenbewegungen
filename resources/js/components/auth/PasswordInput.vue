<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

defineOptions({
    inheritAttrs: false,
});

// workaround to allow the v-model to be "forwarded" to the input element
const model = defineModel<string>();

const hidden = ref(true);
</script>

<template>
    <div class="relative">
        <input v-model="model" v-bind="$attrs" :type="hidden ? 'password' : 'text'" />

        <button
            type="button"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
            @click="hidden = !hidden"
            tabindex="-1"
        >
            <Eye v-if="hidden" class="h-5 w-5" />
            <EyeOff v-else class="h-5 w-5" />
        </button>
    </div>
</template>
