<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import type { Measurement } from '@/types/measurement';

import { deleteMethod, store, update } from '@/actions/App/Http/Controllers/CommentController';
import { formatDate } from '@/utils/date';

const props = defineProps<{
    projectId: number;
    measurements: Measurement[];
    comparisonId: number | null;
}>();

const page = usePage();
const editingCommentId = ref<number | null>(null);
const isCreateFormVisible = ref(false);

const createForm = useForm({
    content: '',
});

const editForm = useForm({
    content: '',
});

const deleteForm = useForm({});

const canCreateComment = computed(() => {
    const user = page.props?.auth?.user;

    return user !== null && user !== undefined && (user.permissions.isAdmin || user.permissions.canComment);
});

const selectedComparisonMeasurement = computed(() => {
    if (!props.comparisonId) {
        return null;
    }

    return props.measurements.find((measurement) => measurement.id === props.comparisonId) ?? null;
});

function submitCreate(): void {
    if (!selectedComparisonMeasurement.value) {
        return;
    }

    createForm.submit(
        store({
            project: props.projectId,
            measurement: selectedComparisonMeasurement.value.id,
        }),
        {
            onSuccess: () => {
                isCreateFormVisible.value = false;
                createForm.reset();
            },
        },
    );
}

function startEditing(commentId: number, content: string): void {
    editingCommentId.value = commentId;
    editForm.content = content;
    editForm.clearErrors();
}

function cancelEditing(): void {
    editingCommentId.value = null;
    editForm.reset();
    editForm.clearErrors();
}

function submitEdit(): void {
    if (!selectedComparisonMeasurement.value || editingCommentId.value === null) {
        return;
    }

    editForm.submit(
        update({
            project: props.projectId,
            measurement: selectedComparisonMeasurement.value.id,
            comment: editingCommentId.value,
        }),
        {
            onSuccess: cancelEditing,
        },
    );
}

function deleteComment(commentId: number): void {
    if (!selectedComparisonMeasurement.value || !confirm('Diesen Kommentar wirklich löschen?')) {
        return;
    }

    deleteForm.submit(
        deleteMethod.delete({
            project: props.projectId,
            measurement: selectedComparisonMeasurement.value.id,
            comment: commentId,
        }),
    );
}
</script>

<template>
    <!--
    GPT-5.3-Codex, 2026-02-13
    "Taking a look at measurement.ts and then Project.vue, please insert a list of all comments regarding the currently selected comparison measurement showing all data that is loaded, i.e. content, created, updated, author name, author role."
    -->
    <h2 id="comments-heading" class="mb-4 text-xl font-bold text-slate-700">
        Kommentare zur Messepoche {{ selectedComparisonMeasurement?.name }}
    </h2>
    <p v-if="selectedComparisonMeasurement" class="mb-4 text-sm text-slate-600">
        {{ selectedComparisonMeasurement.name }}
        ({{ formatDate(selectedComparisonMeasurement.datetime) }})
    </p>

    <p v-if="!selectedComparisonMeasurement" class="text-slate-500">Keine Vergleichsepoche ausgewählt.</p>

    <template v-else>
        <div v-if="canCreateComment" class="mb-6">
            <button
                v-if="!isCreateFormVisible"
                type="button"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                @click="isCreateFormVisible = true"
            >
                Kommentar hinzufügen
            </button>

            <form v-else class="space-y-3" @submit.prevent="submitCreate">
                <label for="new-comment" class="block text-sm font-semibold text-slate-700">Kommentar hinzufügen</label>
                <textarea
                    id="new-comment"
                    v-model="createForm.content"
                    rows="3"
                    maxlength="5000"
                    class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Kommentar zur Messepoche"
                />
                <p v-if="createForm.errors.content" class="text-sm text-red-600">{{ createForm.errors.content }}</p>
                <div class="flex gap-2">
                    <button
                        type="submit"
                        :disabled="createForm.processing"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ createForm.processing ? 'Wird gespeichert...' : 'Kommentar speichern' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200"
                        @click="isCreateFormVisible = false"
                    >
                        Abbrechen
                    </button>
                </div>
            </form>
        </div>

        <p v-if="selectedComparisonMeasurement.comments.length === 0" class="text-slate-500">
            Keine Kommentare für diese Messepoche vorhanden.
        </p>

        <div v-if="selectedComparisonMeasurement.comments.length > 0" class="space-y-4">
            <article
                v-for="comment in selectedComparisonMeasurement.comments"
                :key="comment.id"
                class="rounded-md border border-slate-200 bg-slate-50 p-4"
            >
                <form v-if="editingCommentId === comment.id" class="space-y-3" @submit.prevent="submitEdit">
                    <textarea
                        v-model="editForm.content"
                        rows="3"
                        maxlength="5000"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="editForm.errors.content" class="text-sm text-red-600">{{ editForm.errors.content }}</p>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Speichern
                        </button>
                        <button
                            type="button"
                            class="rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200"
                            @click="cancelEditing"
                        >
                            Abbrechen
                        </button>
                    </div>
                </form>

                <template v-else>
                    <p class="mb-3 whitespace-pre-wrap text-slate-800">{{ comment.content }}</p>
                    <div class="grid gap-1 text-sm text-slate-600 md:grid-cols-2">
                        <p>
                            <span class="font-semibold text-slate-700">Erstellt:</span>
                            {{ formatDate(comment.createdDatetime, false) }}
                        </p>
                        <p v-if="comment.lastEditedDatetime">
                            <span class="font-semibold text-slate-700">Zuletzt bearbeitet:</span>
                            {{ formatDate(comment.lastEditedDatetime, false) }}
                        </p>
                        <p v-if="comment.lastEditor">
                            <span class="font-semibold text-slate-700">Bearbeitet von:</span>
                            {{ comment.lastEditor.name }}
                        </p>
                        <p>
                            <span class="font-semibold text-slate-700">Autor:</span>
                            {{ comment.user.name }}
                        </p>
                        <p>
                            <span class="font-semibold text-slate-700">Rolle:</span>
                            {{ comment.user.role.name }}
                        </p>
                    </div>
                    <div v-if="comment.canEdit || comment.canDelete" class="mt-3 flex gap-2">
                        <button
                            v-if="comment.canEdit"
                            type="button"
                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                            @click="startEditing(comment.id, comment.content)"
                        >
                            Bearbeiten
                        </button>
                        <button
                            v-if="comment.canDelete"
                            type="button"
                            :disabled="deleteForm.processing"
                            class="text-sm font-semibold text-red-600 hover:text-red-800 disabled:opacity-50"
                            @click="deleteComment(comment.id)"
                        >
                            Löschen
                        </button>
                    </div>
                </template>
            </article>
        </div>
    </template>
</template>
