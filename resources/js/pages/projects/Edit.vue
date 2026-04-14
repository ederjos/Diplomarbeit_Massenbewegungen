<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import type { BaseMeasurement } from '@/types/measurement';
import type { BaseProject } from '@/types/project';

defineProps<{
    project: BaseProject;
    // needed for reference measurement selection
    measurements: BaseMeasurement[];
}>();
</script>

<template>
    <Head :title="`Projekt &quot;${project.name}&quot; bearbeiten`" />

    <p>TODO</p>
</template>
