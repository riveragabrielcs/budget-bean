<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Welcome Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <span class="text-3xl font-bold text-emerald-800 tracking-tight">
                    Budget<span class="text-amber-600">Bean</span>
                </span>
                <span class="ml-2 text-2xl">🌱</span>
            </div>
            <h2 class="text-xl font-semibold text-stone-700">Welcome back!</h2>
            <p class="text-stone-600 mt-1">Continue growing your financial garden</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg p-3">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email" class="text-stone-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-stone-700 font-medium" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="text-emerald-500 focus:ring-emerald-500" />
                    <span class="ms-2 text-sm text-stone-600">Remember me</span>
                </label>
            </div>

            <div class="space-y-4">
                <PrimaryButton
                    class="w-full justify-center bg-emerald-500 hover:bg-emerald-600 focus:ring-emerald-500"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                    <span v-else class="flex items-center">
                        <span class="mr-2">🌱</span>
                        Log in
                    </span>
                </PrimaryButton>

                <div class="text-center">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-emerald-600 hover:text-emerald-700 underline focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 rounded-md"
                    >
                        Forgot your password?
                    </Link>
                </div>
            </div>
        </form>

        <!-- Sign Up Link -->
        <div class="mt-8 text-center">
            <p class="text-stone-600">
                New to BudgetBean?
                <Link
                    :href="route('register')"
                    class="font-medium text-emerald-600 hover:text-emerald-700 underline ml-1"
                >
                    Create an account
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
