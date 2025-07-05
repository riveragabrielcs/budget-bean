<script setup>
import {Link} from '@inertiajs/vue3'
import {defineProps, defineEmits} from 'vue'

defineProps({
    show: Boolean
})

defineEmits(['close'])
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <img
                            src="/images/BudgetBeanAlpha.png"
                            alt="BudgetBean Logo"
                            class="w-20 h-20 object-contain"
                        />
                    </div>
                    <h3 class="text-xl font-semibold text-emerald-800 mb-2">
                        Oops! Account Required 🌾
                    </h3>
                    <p class="text-stone-600 mb-6">
                        To grow your financial garden and track your progress over time, you'll need to plant yourself an account first!
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="$emit('close')"
                            class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                        >
                            Maybe Later
                        </button>
                        <Link
                            :href="route('register')"
                            class="flex-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium py-3 px-4 rounded-lg transition duration-200 text-center"
                        >
                            🌱 Create Account
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
