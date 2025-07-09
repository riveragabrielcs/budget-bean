<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                    Profile Settings
                </h2>
                <span class="ml-3 text-2xl">👤</span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-8 sm:px-6 lg:px-8">
                <!-- Profile Information Section -->
                <div class="bg-white shadow-md border border-emerald-100 sm:rounded-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4 border-b border-emerald-100">
                        <h3 class="text-lg font-semibold text-emerald-800 flex items-center">
                            <span class="mr-2">🌱</span>
                            Account Information
                        </h3>
                        <p class="text-sm text-stone-600 mt-1">
                            Keep your garden profile fresh and up to date
                        </p>
                    </div>
                    <div class="p-6">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>
                </div>

                <!-- Password Section -->
                <div class="bg-white shadow-md border border-emerald-100 sm:rounded-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 border-b border-amber-100">
                        <h3 class="text-lg font-semibold text-emerald-800 flex items-center">
                            <span class="mr-2">🔐</span>
                            Security Settings
                        </h3>
                        <p class="text-sm text-stone-600 mt-1">
                            Protect your financial garden with a strong password
                        </p>
                    </div>
                    <div class="p-6">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white shadow-md border border-rose-200 sm:rounded-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-rose-50 to-red-50 px-6 py-4 border-b border-rose-200">
                        <h3 class="text-lg font-semibold text-rose-800 flex items-center">
                            <span class="mr-2">⚠️</span>
                            Danger Zone
                        </h3>
                        <p class="text-sm text-stone-600 mt-1">
                            Permanently remove your account and all garden data
                        </p>
                    </div>
                    <div class="p-6">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
