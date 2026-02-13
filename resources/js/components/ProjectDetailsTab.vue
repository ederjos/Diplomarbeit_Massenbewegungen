<script lang="ts" setup>
import { ProjectDetails } from '@/@types/project';
import { User } from '@/@types/user';

defineProps<{
    project: ProjectDetails;
    contactPersons: User[];
}>();
</script>

<template>
    <section class="p-4">
        <div class="rounded-lg bg-white p-6 shadow-md">
            <h2 class="mb-4 text-2xl font-bold text-slate-700">Projektdetails</h2>
            <table class="w-full max-w-2xl">
                <tbody>
                    <tr class="border-b border-slate-200">
                        <td class="w-48 py-3 pr-4 font-semibold text-slate-600">ID</td>
                        <td class="py-3 text-slate-800">{{ project.id || '—' }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Bezeichnung</td>
                        <td class="py-3 text-slate-800">{{ project.name || '—' }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Letzte Geschäftszahl</td>
                        <td class="py-3 text-slate-800">{{ project.last_file_number || '—' }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Auftraggeber</td>
                        <td class="py-3 text-slate-800">{{ project.client || '—' }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Sachbearbeiter</td>
                        <td class="py-3 text-slate-800">{{ project.clerk || '—' }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Intervall</td>
                        <td class="py-3 text-slate-800">{{ project.period || '—' }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Typ</td>
                        <td class="py-3 text-slate-800">{{ project.type || '—' }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Durchschnittl. Bewegung</td>
                        <td class="py-3 text-slate-800">
                            {{ project.averageYearlyMovement.toFixed(4) || '—' }} cm/Jahr
                        </td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-3 pr-4 font-semibold text-slate-600">Gemeinde</td>
                        <td class="py-3 text-slate-800">{{ project.municipality || '—' }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 align-top font-semibold text-slate-600">Anmerkung</td>
                        <td class="py-3 text-slate-800">{{ project.comment || '—' }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 align-top font-semibold text-slate-600">Ansprechpersonen</td>
                        <td class="py-3 text-slate-800">
                            <ul v-if="contactPersons.length > 0" class="list-inside list-disc space-y-1">
                                <li v-for="user in contactPersons" :key="user.id">
                                    <u
                                        ><a :href="'mailto:' + user.email"
                                            >{{ user.name }} ({{ user.role?.name ?? 'no role assigned' }})</a
                                        ></u
                                    >
                                </li>
                            </ul>
                            <span v-else>—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
