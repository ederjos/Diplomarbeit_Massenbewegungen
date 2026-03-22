<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import { store as register } from '@/actions/App/Http/Controllers/RegistrationRequestController';
import { create as login } from '@/actions/Laravel/Fortify/Http/Controllers/AuthenticatedSessionController';
import PasswordInput from '@/components/auth/PasswordInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    note: '',
});

const submit = () => {
    form.submit(register(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<!--
    Gemini 3 Pro, 2026-02-10
    "Please add some simple Tailwind Styling to Login.vue and Register.vue."
-->
<template>
    <Head title="Registrieren" />

    <main class="flex min-h-screen items-center justify-center bg-gray-100 p-4">
        <div class="w-full max-w-md overflow-hidden rounded-lg bg-white p-6 shadow-md">
            <h1 class="mb-6 text-center text-2xl font-bold text-gray-900">Registrierung anfragen</h1>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autofocus
                        autocomplete="name"
                        :aria-describedby="form.errors.name ? 'name-error' : undefined"
                        :aria-invalid="form.errors.name ? true : false"
                    />
                    <div v-if="form.errors.name" id="name-error" role="alert" class="mt-1 text-sm text-red-600">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">E-Mail</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autocomplete="username"
                        :aria-describedby="form.errors.email ? 'email-error' : undefined"
                        :aria-invalid="form.errors.email ? true : false"
                    />
                    <div v-if="form.errors.email" id="email-error" role="alert" class="mt-1 text-sm text-red-600">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Passwort</label>
                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        class="block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autocomplete="new-password"
                        :aria-describedby="form.errors.password ? 'password-error' : undefined"
                        :aria-invalid="form.errors.password ? true : false"
                    />
                    <div v-if="form.errors.password" id="password-error" role="alert" class="mt-1 text-sm text-red-600">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">
                        Passwort bestätigen
                    </label>
                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        class="block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autocomplete="new-password"
                        :aria-describedby="
                            form.errors.password_confirmation ? 'password-confirmation-error' : undefined
                        "
                        :aria-invalid="form.errors.password_confirmation ? true : false"
                    />
                    <div
                        v-if="form.errors.password_confirmation"
                        id="password-confirmation-error"
                        role="alert"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>

                <div>
                    <label for="note" class="mb-1 block text-sm font-medium text-gray-700">
                        Anmerkungen für den Administrator (optional)
                    </label>
                    <textarea
                        id="note"
                        v-model="form.note"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        rows="3"
                        placeholder="z.B. gewünschte Rolle, Grund der Registrierung ..."
                        :aria-describedby="form.errors.note ? 'note-error' : undefined"
                        :aria-invalid="form.errors.note ? true : false"
                    />
                    <div v-if="form.errors.note" id="note-error" role="alert" class="mt-1 text-sm text-red-600">
                        {{ form.errors.note }}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <Link
                        :href="login()"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Bereits registriert?
                    </Link>

                    <button
                        type="submit"
                        class="ml-4 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25"
                        :disabled="form.processing"
                    >
                        ANFRAGE SENDEN
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>
