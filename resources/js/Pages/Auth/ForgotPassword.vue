<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <span class="text-3xl font-bold text-emerald-800 tracking-tight">
                    Budget<span class="text-amber-600">Bean</span>
                </span>
                <span class="ml-2 text-2xl">🌱</span>
            </div>
            <h2 class="text-xl font-semibold text-stone-700">Forgot your password?</h2>
            <p class="text-stone-600 mt-1">Don't worry, we'll help you get back to your garden!</p>
        </div>

        <!-- Success Message -->
        <div
            v-if="status"
            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg"
        >
            <div class="flex items-start">
                <span class="text-green-600 text-lg mr-3 mt-0.5">✅</span>
                <div>
                    <p class="text-sm font-medium text-green-800 mb-1">Email Sent Successfully!</p>
                    <p class="text-sm text-green-700">{{ status }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email Address" class="text-stone-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email address"
                />

                <InputError class="mt-2" :message="form.errors.email" />
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
                        Sending email...
                    </span>
                    <span v-else class="flex items-center">
                        <span class="mr-2">📧</span>
                        Send Password Reset Link
                    </span>
                </PrimaryButton>

                <!-- Help Text -->
                <div class="text-center">
                    <p class="text-xs text-stone-500">
                        Didn't receive the email? Check your spam folder or try again in a few minutes.
                    </p>
                </div>
            </div>
        </form>

        <!-- Back to Login -->
        <div class="mt-8 text-center">
            <p class="text-stone-600">
                Remember your password?
                <Link
                    :href="route('login')"
                    class="font-medium text-emerald-600 hover:text-emerald-700 underline ml-1"
                >
                    Back to login
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
