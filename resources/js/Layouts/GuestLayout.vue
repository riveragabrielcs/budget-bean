<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-green-50 pt-6 sm:justify-center sm:pt-0"
    >
        <div
            class="mt-6 w-full overflow-hidden bg-white px-8 py-8 shadow-lg border border-emerald-100 sm:max-w-md sm:rounded-xl"
        >
            <!-- Centered Logo -->
            <div class="flex justify-center mb-6">
                <Link href="/" class="hover:opacity-80 transition-opacity duration-200">
                    <ApplicationLogo class="h-24 w-24 fill-current text-emerald-600" />
                </Link>
            </div>

            <!-- Content Slot -->
            <slot />
        </div>
    </div>
</template>
