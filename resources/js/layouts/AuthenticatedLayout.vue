<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { index as admin } from '@/actions/App/Http/Controllers/AdminController';
import { index as home } from '@/actions/App/Http/Controllers/ProjectController';
import { destroy as logout } from '@/actions/Laravel/Fortify/Http/Controllers/AuthenticatedSessionController';

const page = usePage();
const user = computed(() => page.props.auth.user!);

const isHome = computed(() => page.url === home.definition.url);
const isAdmin = computed(() => user.value.permissions.isAdmin);
</script>

<!--
    Gemini 3 Pro, 2026-02-10
    "Please add some simple Tailwind Styling to AuthenticatedLayout.vue. <main> should remain without any styling, so that it can be styled by the individual pages."
-->
<template>
    <header class="flex justify-center p-4">
        <nav aria-label="Hauptnavigation" class="flex w-full max-w-4xl items-center justify-between">
            <div>
                <Link v-if="!isHome" :href="home()" class="font-medium text-indigo-600 hover:text-indigo-800">
                    Zur Startseite
                </Link>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-700"> Eingeloggt als {{ user.name }} </span>
                <Link v-if="isAdmin" :href="admin()" class="font-medium text-indigo-600 hover:text-indigo-800">
                    Admin-Bereich
                </Link>
                <!-- `cursor-pointer` is required because <Link> renders a <button> tag with method="post" -->
                <Link
                    :href="logout()"
                    method="post"
                    as="button"
                    class="cursor-pointer font-medium text-red-600 hover:text-red-800"
                >
                    Ausloggen
                </Link>
            </div>
        </nav>
    </header>

    <main class="flex flex-col items-center gap-8 p-4">
        <!-- inner content is provided by the parent component -->
        <slot />
    </main>
</template>
