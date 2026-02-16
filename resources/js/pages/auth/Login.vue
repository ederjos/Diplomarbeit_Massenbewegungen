<script setup lang="ts">
import PasswordInput from '@/components/auth/PasswordInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
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

    <div class="flex min-h-screen items-center justify-center bg-gray-100 p-4">
        <div class="w-full max-w-md overflow-hidden rounded-lg bg-white p-6 shadow-md">
            <h2 class="mb-6 text-center text-2xl font-bold text-gray-900">Einloggen</h2>

            <div v-if="status" class="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">E-Mail</label>
                    <input
                        id="email"
                        type="email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Passwort</label>
                    <PasswordInput
                        id="password"
                        class="block w-full rounded-md border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                </div>

                <div class="block">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm text-gray-600">Angemeldet bleiben</span>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <Link
                        href="/register"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Noch keinen Account? Registrieren
                    </Link>

                    <button
                        class="ml-4 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25"
                        :disabled="form.processing"
                    >
                        EINLOGGEN
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
