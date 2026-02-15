<!-- This component exists to avoid code duplication for password inputs, especially the show/hide functionality. -->

<script setup lang="ts">
import { ref } from 'vue';

defineOptions({
    inheritAttrs: false,
});

// workaround to allow the v-model to be "forwarded" to the input element
const model = defineModel<string>();

defineProps<{
    buttonClass: string;
}>();

const hidden = ref(true);
</script>

<template>
    <div class="relative">
        <input v-model="model" v-bind="$attrs" :type="hidden ? 'password' : 'text'" />

        <button type="button" :class="buttonClass" @click="hidden = !hidden" tabindex="-1">
            <!-- Modified from "https://preline.co/docs/toggle-password.html" -->
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="h-5 w-5"
            >
                <g v-if="hidden">
                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                    <line x1="2" x2="22" y1="2" y2="22" />
                </g>
                <g v-else>
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </g>
            </svg>
        </button>
    </div>
</template>
