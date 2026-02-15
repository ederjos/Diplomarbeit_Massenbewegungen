<script setup lang="ts">
import { ProjectDetails } from '@/@types/project';
import { User } from '@/@types/user';
import DetailRow from '@/components/ui/DetailRow.vue';
import { formatDate } from '@/utils/date';

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
                    <DetailRow label="ID" :value="project.id ?? '—'" />
                    <DetailRow label="Bezeichnung" :value="project.name ?? '—'" />
                    <DetailRow label="Letzte Geschäftszahl" :value="project.lastFileNumber ?? '—'" />
                    <DetailRow label="Auftraggeber" :value="project.client ?? '—'" />
                    <DetailRow label="Sachbearbeiter" :value="project.clerk ?? '—'" />
                    <DetailRow label="Intervall" :value="project.period ?? '—'" />
                    <DetailRow label="Typ" :value="project.type ?? '—'" />
                    <DetailRow
                        label="Durchschnittl. Bewegung"
                        :value="
                            project.averageYearlyMovement ? project.averageYearlyMovement.toFixed(4) + ' cm/Jahr' : '—'
                        "
                    />
                    <DetailRow label="Status" :value="project.isActive ? 'Aktiv' : 'Inaktiv'" />
                    <DetailRow label="Erste Messung" :value="formatDate(project.firstMeasurement)" />
                    <DetailRow label="Letzte Messung" :value="formatDate(project.lastMeasurement)" />
                    <DetailRow label="Gemeinde" :value="project.municipality ?? '—'" />
                    <DetailRow label="Anmerkung" :value="project.comment ?? '—'" />
                    <DetailRow :label="contactPersons.length === 1 ? 'Ansprechperson' : 'Ansprechpersonen'">
                        <ul
                            v-if="contactPersons.length > 0"
                            :class="contactPersons.length === 1 ? 'list-none' : 'list-inside list-disc space-y-1'"
                        >
                            <li v-for="user in contactPersons" :key="user.id">
                                <u
                                    ><a :href="'mailto:' + user.email"
                                        >{{ user.name }} ({{ user.role?.name ?? 'no role assigned' }})</a
                                    ></u
                                >
                            </li>
                        </ul>
                        <span v-else>—</span>
                    </DetailRow>
                </tbody>
            </table>
        </div>
    </section>
</template>
