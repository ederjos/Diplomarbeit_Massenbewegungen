<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Admin" />
    <AuthenticatedLayout>
        <div class="w-full max-w-4xl">
            <h1 class="mb-4 text-2xl font-bold">Admin Bereich</h1>
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <p>Willkommen im Admin-Bereich.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
