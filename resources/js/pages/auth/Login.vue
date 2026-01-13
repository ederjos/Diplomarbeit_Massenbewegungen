<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
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

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                    <input 
                        id="email" 
                        type="email" 
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                        v-model="form.email" 
                        required 
                        autofocus 
                        autocomplete="username" 
                    />
                    <div v-if="form.errors.email" class="text-red-600 mt-1 text-sm">{{ form.errors.email }}</div>
                </div>

                <div class="mt-4">
                    <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                    <input 
                        id="password" 
                        type="password" 
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                        v-model="form.password" 
                        required 
                        autocomplete="current-password" 
                    />
                    <div v-if="form.errors.password" class="text-red-600 mt-1 text-sm">{{ form.errors.password }}</div>
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <Link
                        v-if="canResetPassword"
                        href="/forgot-password"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Forgot your password?
                    </Link>

                    <button 
                        class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" 
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing"
                    >
                        Log in
                    </button>
                </div>
                
                <div class="flex items-center justify-center mt-4">
                    <Link href="/register" class="text-sm text-gray-600 underline hover:text-gray-900">
                        Need an account? Register
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>
