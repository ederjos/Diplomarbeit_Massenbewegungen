<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-8 p-4">
        <div class="flex w-full max-w-4xl items-center justify-between">
            <div>
                <Link v-if="page.url !== '/'" href="/" class="text-blue-500 hover:underline">Zur Startseite</Link>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-700" v-if="user"> Eingeloggt als {{ user.name }} </span>
                <Link
                    v-if="user?.role?.name === 'admin'"
                    href="/admin"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                >
                    Admin
                </Link>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="text-sm font-semibold text-red-600 hover:text-red-800"
                >
                    Logout
                </Link>
            </div>
        </div>

        <slot />
    </div>
</template>
