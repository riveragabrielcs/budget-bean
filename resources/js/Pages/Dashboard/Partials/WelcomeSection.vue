<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const welcomeMessage = computed(() => {
    const user = usePage().props.auth?.user;

    if (user) {
        return {
            title: "Welcome back! 🌱",
            subtitle: "Ready to grow your savings like a healthy garden?"
        };
    } else {
        return {
            title: "Welcome to BudgetBean! 🌱",
            subtitle: "Feel free to test out the app and see how it works. Create an account to save your financial garden's progress!"
        };
    }
});
</script>

<template>
    <!-- Welcome Card -->
    <div class="overflow-hidden bg-white shadow-lg sm:rounded-xl border border-emerald-100 mb-6">
        <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
            <!-- Subtle decorative elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

            <div class="flex items-center relative">
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-emerald-800">
                        {{ welcomeMessage.title }}
                    </h3>
                    <p class="text-stone-600 mt-2 text-lg">
                        {{ welcomeMessage.subtitle }}
                    </p>
                </div>
                <div class="hidden sm:block">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center shadow-md border border-amber-200">
                        <span class="text-3xl">💰</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
