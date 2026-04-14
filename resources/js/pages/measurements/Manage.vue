<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import type { BaseMeasurement } from '@/types/measurement';
import type { BaseProject } from '@/types/project';

defineProps<{
    project: BaseProject;
    measurements: BaseMeasurement[];
}>();
</script>

<template>
    <Head :title="`Messungen verwalten - ${project.name}`" />

    <p>TODO</p>
</template>
