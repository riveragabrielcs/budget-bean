<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <div class="space-y-6">
            <form
                @submit.prevent="form.patch(route('profile.update'))"
                class="space-y-6"
            >
                <div>
                    <InputLabel for="name" value="Name" class="text-stone-700 font-medium" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" class="text-stone-700 font-medium" />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-2 block w-full border-stone-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div v-if="mustVerifyEmail && user.email_verified_at === null">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <span class="text-xl mr-2">📧</span>
                            <div>
                                <p class="text-amber-800 font-medium">
                                    Your email address is unverified.
                                </p>
                                <p class="text-amber-700 text-sm mt-1">
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="underline hover:text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 rounded"
                                    >
                                        Click here to re-send the verification email.
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-show="status === 'verification-link-sent'"
                        class="mt-3 bg-green-50 border border-green-200 rounded-lg p-4"
                    >
                        <div class="flex items-center">
                            <span class="text-xl mr-2">✅</span>
                            <p class="text-green-800 font-medium">
                                A new verification link has been sent to your email address.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <PrimaryButton
                        :disabled="form.processing"
                        class="bg-emerald-500 hover:bg-emerald-600 focus:bg-emerald-600 focus:ring-emerald-500"
                    >
                        <span class="flex items-center">
                            <span class="mr-2">💾</span>
                            Save Changes
                        </span>
                    </PrimaryButton>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <div
                            v-if="form.recentlySuccessful"
                            class="bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2"
                        >
                            <p class="text-emerald-700 text-sm font-medium flex items-center">
                                <span class="mr-2">🌱</span>
                                Saved successfully!
                            </p>
                        </div>
                    </Transition>
                </div>
            </form>
        </div>
    </section>
</template>
