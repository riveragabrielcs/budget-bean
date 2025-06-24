<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <div class="mb-6">
            <p class="text-stone-600 leading-relaxed">
                Once your account is deleted, all of your financial data, savings goals, and garden progress will be permanently removed. Please download any information you wish to keep before proceeding.
            </p>
        </div>

        <div class="bg-rose-50 border border-rose-200 rounded-lg p-4">
            <div class="flex items-start">
                <span class="text-2xl mr-3 mt-1">🚨</span>
                <div>
                    <h4 class="font-semibold text-rose-800 mb-2">
                        This action cannot be undone
                    </h4>
                    <p class="text-rose-700 text-sm">
                        Deleting your account will permanently remove your entire financial garden, including all savings goals, expense history, and personal data.
                    </p>
                </div>
            </div>
        </div>

        <DangerButton
            @click="confirmUserDeletion"
            class="bg-rose-600 hover:bg-rose-700 focus:bg-rose-700 focus:ring-rose-500"
        >
            <span class="flex items-center">
                <span class="mr-2">🗑️</span>
                Delete Account
            </span>
        </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <span class="text-3xl mr-3">⚠️</span>
                    <h2 class="text-xl font-bold text-rose-800">
                        Delete Your BudgetBean Account?
                    </h2>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 mb-6">
                    <p class="text-rose-800 font-medium mb-2">
                        This will permanently destroy your entire financial garden:
                    </p>
                    <ul class="text-rose-700 text-sm space-y-1 ml-4">
                        <li>• All savings goals and progress</li>
                        <li>• Complete expense history</li>
                        <li>• Budget categories and settings</li>
                        <li>• Account and profile information</li>
                    </ul>
                </div>

                <p class="text-stone-600 mb-6">
                    Please enter your password to confirm you want to permanently delete your account and all associated data.
                </p>

                <div class="mb-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="block w-full border-stone-300 focus:border-rose-500 focus:ring-rose-500 rounded-lg"
                        placeholder="Enter your password to confirm"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3">
                    <SecondaryButton
                        @click="closeModal"
                        class="border-stone-300 text-stone-700 hover:bg-stone-50"
                    >
                        <span class="flex items-center">
                            <span class="mr-2">↩️</span>
                            Cancel
                        </span>
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="bg-rose-600 hover:bg-rose-700 focus:bg-rose-700 focus:ring-rose-500"
                    >
                        <span class="flex items-center">
                            <span class="mr-2">🗑️</span>
                            Delete Account Forever
                        </span>
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
