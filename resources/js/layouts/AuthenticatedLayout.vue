<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
</script>

<template>
    <div class="flex justify-center flex-col items-center gap-8 p-4">
        <div class="w-full max-w-4xl flex justify-between items-center">
            <div>
                <slot name="header-left" />
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-700" v-if="user">
                    Eingeloggt als {{ user.name }}
                </span>
                <Link href="/logout" method="post" as="button" class="text-sm text-red-600 hover:text-red-800 font-semibold">
                    Logout
                </Link>
            </div>
        </div>

        <slot />
    </div>
</template>
