<script setup lang="ts">
/**
 * Claude 4.5 Sonnet, 2026-02-16
 * "Please implement the suggested ErrorBoundary around LealfetMap and ProjectTimeline for the reasons mentioned above."
 */

/**
 * Why this is textbook-correct:
 * Leaflet.js risks: Browser incompatibility, WebGL failures, tile loading errors, geolocation API failures
 * Chart.js risks: Invalid date formats, rendering canvas errors, memory issues with large datasets
 * Isolation benefit: If mapping fails, users can still view project details, comments, measurements
 */

import { onErrorCaptured, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        componentName?: string;
        showDetails?: boolean;
    }>(),
    {
        componentName: 'Component',
        showDetails: false,
    },
);

const error = ref<Error | null>(null);

onErrorCaptured((err, instance, info) => {
    error.value = err as Error;
    console.error(`Error in ${props.componentName}:`, err, info);
    // Return false to stop error propagation
    return false;
});

function reset() {
    error.value = null;
}
</script>

<template>
    <div
        v-if="error"
        class="flex h-full w-full items-center justify-center rounded-lg border border-red-300 bg-red-50 p-8"
    >
        <div class="max-w-2xl text-center">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="mx-auto mb-4 h-12 w-12 text-red-500"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                />
            </svg>
            <h3 class="mb-2 text-xl font-bold text-red-800">Ein Fehler ist aufgetreten</h3>
            <p class="mb-4 text-red-700">
                {{ componentName }} konnte nicht geladen werden. Bitte versuchen Sie es erneut.
            </p>
            <div v-if="showDetails && error.message" class="mb-4 rounded bg-white p-4 text-left">
                <p class="mb-2 font-mono text-sm text-gray-700">{{ error.message }}</p>
                <details v-if="error.stack" class="mt-2">
                    <summary class="cursor-pointer text-sm text-gray-600 hover:text-gray-800">Stack Trace</summary>
                    <pre class="mt-2 overflow-auto text-xs text-gray-600">{{ error.stack }}</pre>
                </details>
            </div>
            <button
                @click="reset"
                class="rounded-md bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
            >
                Erneut versuchen
            </button>
        </div>
    </div>
    <slot v-else></slot>
</template>
