<template>
  <Head title="Home" />
  <div class="flex justify-center flex-col items-center gap-8">
    <LeafletComponent />
    <ProjectTimeline class="w-full max-w-4xl" />
  </div>
</template>

<script lang="ts" setup>
import { Head /*, useForm, router*/ } from '@inertiajs/vue3'
// import { ref } from 'vue'
import LeafletComponent from '../components/LeafletComponent.vue'
import ProjectTimeline from '../components/ProjectTimeline.vue'
</script>
