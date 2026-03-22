<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import { create as register } from '@/actions/App/Http/Controllers/RegistrationRequestController';
import { store as login } from '@/actions/Laravel/Fortify/Http/Controllers/AuthenticatedSessionController';
import PasswordInput from '@/components/auth/PasswordInput.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.submit(login(), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<!--
    Gemini 3 Pro, 2026-02-10
    "Please add some simple Tailwind Styling to Login.vue and Register.vue."
-->
<template>
    <Head title="Log in" />

    <main class="flex min-h-screen items-center justify-center bg-gray-100 p-4">
        <div class="w-full max-w-md overflow-hidden rounded-lg bg-white p-6 shadow-md">
            <h1 class="mb-6 text-center text-2xl font-bold text-gray-900">Einloggen</h1>

            <div v-if="status" class="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700">
                {{ status }}
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">E-Mail</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autofocus
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
                        autocomplete="current-password"
                        :aria-describedby="form.errors.password ? 'password-error' : undefined"
                        :aria-invalid="form.errors.password ? true : false"
                    />
                    <div v-if="form.errors.password" id="password-error" role="alert" class="mt-1 text-sm text-red-600">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div>
                    <label class="flex items-center">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm text-gray-600">Angemeldet bleiben</span>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <Link
                        :href="register()"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Noch keinen Account? Registrieren
                    </Link>

                    <button
                        type="submit"
                        class="ml-4 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25"
                        :disabled="form.processing"
                    >
                        EINLOGGEN
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>
