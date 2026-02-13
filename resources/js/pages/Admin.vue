<script setup lang="ts">
import type { RegistrationRequest } from '@/@types/registration-request';
import type { Role } from '@/@types/user';
import RegistrationRequestCard from '@/components/admin/RegistrationRequestCard.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    registrationRequests: RegistrationRequest[];
    roles: Role[];
}>();
</script>

<!--
    Claude Sonnet 4.5, 2026-02-12
    "Please add some simple Tailwind Styling to Admin.vue."
-->
<template>
    <Head title="Admin" />
    <AuthenticatedLayout>
        <div class="w-full max-w-6xl space-y-10">
            <section>
                <h1 class="mb-4 text-2xl font-bold text-gray-900">Registrierungsanfragen</h1>

                <div
                    v-if="registrationRequests.length === 0"
                    class="rounded-md bg-gray-50 p-6 text-center text-gray-500"
                >
                    Keine ausstehenden Registrierungsanfragen.
                </div>

                <div v-else class="space-y-4">
                    <RegistrationRequestCard
                        v-for="request in registrationRequests"
                        :key="request.id"
                        :request="request"
                        :roles="roles"
                    />
                </div>
            </section>

            <section>
                <h1 class="mb-4 text-2xl font-bold text-gray-900">Benutzer & Rollen verwalten</h1>
                <div class="rounded-md border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-400">
                    <p class="text-lg font-medium">Noch nicht implementiert</p>
                    <p class="mt-1 text-sm">
                        Benutzer- und Rollenverwaltung wird verfügbar, sobald das Rechte-System überarbeitet wurde.
                    </p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
