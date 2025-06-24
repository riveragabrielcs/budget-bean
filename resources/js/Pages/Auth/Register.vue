<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Welcome Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <span class="text-3xl font-bold text-emerald-800 tracking-tight">
                    Budget<span class="text-amber-600">Bean</span>
                </span>
                <span class="ml-2 text-2xl">🌱</span>
            </div>
            <h2 class="text-xl font-semibold text-stone-700">Start your financial journey!</h2>
            <p class="text-stone-600 mt-1">Plant the seeds of your financial future</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="name" value="Full Name" class="text-stone-700 font-medium" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your full name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" class="text-stone-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="you@example.com"
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
                    autocomplete="new-password"
                    placeholder="Choose a strong password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
                <p class="mt-2 text-xs text-stone-500">Must be at least 8 characters long</p>
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="text-stone-700 font-medium"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
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
                        Creating account...
                    </span>
                    <span v-else class="flex items-center">
                        <span class="mr-2">🌱</span>
                        Create Account
                    </span>
                </PrimaryButton>

                <!-- Terms Notice (Fun Project So Not Being So Serious) -->
                <div class="text-center">
                    <p class="text-xs text-stone-500 leading-relaxed">
                        By creating an account,<br>you're agreeing to build an awesome Garden. 🌻
                    </p>
                </div>
            </div>
        </form>

        <!-- Sign In Link -->
        <div class="mt-8 text-center">
            <p class="text-stone-600">
                Already have an account?
                <Link
                    :href="route('login')"
                    class="font-medium text-emerald-600 hover:text-emerald-700 underline ml-1"
                >
                    Sign in
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
