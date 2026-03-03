<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { AuthUser } from '@/@types/user';

const page = usePage();
const user = computed(() => (page.props.auth as { user: AuthUser | null }).user);

const isHome = computed(() => page.url === '/');
const isAdmin = computed(() => user.value?.permissions.isAdmin === true);
</script>

<!--
    Gemini 3 Pro, 2026-02-10
    "Please add some simple Tailwind Styling to AuthenticatedLayout.vue. <main> should remain without any styling, so that it can be styled by the individual pages."
-->
<template>
    <div class="flex flex-col items-center justify-center gap-8 p-4">
        <header class="w-full">
            <nav aria-label="Hauptnavigation" class="flex w-full max-w-4xl items-center justify-between">
                <div>
                    <Link v-if="!isHome" href="/" class="font-medium text-indigo-600 hover:text-indigo-800">
                        Zur Startseite
                    </Link>
                </div>

                <div class="flex items-center gap-4">
                    <span v-if="user" class="text-sm text-gray-700"> Eingeloggt als {{ user.name }} </span>
                    <Link v-if="isAdmin" href="/admin" class="font-medium text-indigo-600 hover:text-indigo-800">
                        Admin-Bereich
                    </Link>
                    <!-- `cursor-pointer` is required because <Link> renders a <button> tag with method="post" -->
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="cursor-pointer font-medium text-red-600 hover:text-red-800"
                    >
                        Ausloggen
                    </Link>
                </div>
            </nav>
        </header>

        <!-- inner content is provided by the parent component -->
        <main class="flex w-full flex-col items-center gap-8">
            <slot />
        </main>
    </div>
</template>
