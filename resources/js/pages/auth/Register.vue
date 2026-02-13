<script setup lang="ts">
import PasswordInput from '@/components/auth/PasswordInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    note: '',
});

const submit = () => {
    form.post('/register', {
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

    <div class="flex min-h-screen items-center justify-center bg-gray-100 p-4">
        <div class="w-full max-w-md overflow-hidden rounded-lg bg-white p-6 shadow-md">
            <h2 class="mb-6 text-center text-2xl font-bold text-gray-900">Registrierung anfragen</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                    <input
                        id="name"
                        type="text"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">E-Mail</label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Passwort</label>
                    <PasswordInput
                        id="password"
                        class="block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        button-class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">
                        Passwort bestätigen
                    </label>
                    <PasswordInput
                        id="password_confirmation"
                        class="block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        button-class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>

                <div>
                    <label for="note" class="mb-1 block text-sm font-medium text-gray-700">
                        Anmerkungen für den Administrator (optional)
                    </label>
                    <textarea
                        id="note"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.note"
                        rows="3"
                        placeholder="z.B. gewünschte Rolle, Grund der Registrierung ..."
                    />
                    <div v-if="form.errors.note" class="mt-1 text-sm text-red-600">{{ form.errors.note }}</div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <Link
                        href="/login"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Bereits registriert?
                    </Link>

                    <button
                        class="ml-4 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25"
                        :disabled="form.processing"
                    >
                        ANFRAGE SENDEN
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
