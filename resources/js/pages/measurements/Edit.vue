<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head :title="`Messung &quot;${measurement.name}&quot; bearbeiten - ${project.name}`" />
    <AuthenticatedLayout>
        <p>TODO</p>
    </AuthenticatedLayout>
</template>
