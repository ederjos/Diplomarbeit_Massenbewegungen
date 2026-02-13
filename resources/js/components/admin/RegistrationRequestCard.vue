<script setup lang="ts">
import type { RegistrationRequest } from '@/@types/registration-request';
import type { Role } from '@/@types/user';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    request: RegistrationRequest;
    roles: Role[];
}>();

const formattedCreatedAt = computed(() => new Date(props.request.created_at).toLocaleString());

const selectedRoleId = ref<number | null>(null);

const approveForm = useForm({
    role_id: null as number | null,
});

const approve = () => {
    if (!selectedRoleId.value) {
        return;
    }
    approveForm.role_id = selectedRoleId.value;
    approveForm.post(`/admin/registration-requests/${props.request.id}/approve`);
};

const rejectForm = useForm({});

const reject = () => {
    if (!confirm(`Registrierungsanfrage von "${props.request.name}" wirklich ablehnen?`)) {
        return;
    }
    rejectForm.delete(`/admin/registration-requests/${props.request.id}`);
};
</script>

<!--
    Claude Sonnet 4.5, 2026-02-12
    "Please add some simple Tailwind Styling to Admin.vue."
-->
<template>
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <p class="text-lg font-semibold text-gray-900">{{ request.name }}</p>
                <p class="text-sm text-gray-600">{{ request.email }}</p>
                <p class="text-xs text-gray-400">Angefragt am {{ formattedCreatedAt }}</p>
                <div v-if="request.note" class="mt-2 rounded bg-gray-50 p-3 text-sm text-gray-700">
                    <span class="font-medium">Anmerkung:</span> {{ request.note }}
                </div>
            </div>

            <div class="flex items-end gap-3 sm:flex-col sm:items-end">
                <div class="flex items-center gap-2">
                    <label :for="'role-' + request.id" class="text-sm font-medium text-gray-700"> Rolle: </label>
                    <select
                        :id="'role-' + request.id"
                        v-model="selectedRoleId"
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option :value="null" disabled>Auswählen...</option>
                        <option v-for="role in roles" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button
                        @click="approve()"
                        :disabled="!selectedRoleId || approveForm.processing"
                        class="inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                    >
                        Genehmigen
                    </button>
                    <button
                        @click="reject()"
                        :disabled="rejectForm.processing"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                    >
                        Ablehnen
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
