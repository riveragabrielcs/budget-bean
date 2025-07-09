<script setup>
import {ref} from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import {Link} from '@inertiajs/vue3';
import {useAuthGuard} from '@/Composables/useAuthGuard'
import AuthRequiredModal from '@/Components/AuthRequiredModal.vue'

const {requireAuth, showAuthModal, closeAuthModal} = useAuthGuard()

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-green-50">
            <nav
                class="border-b border-emerald-100 bg-white shadow-sm"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 justify-between items-center">
                        <div class="flex items-center">
                            <!-- Logo & Brand -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center group">
                                    <ApplicationLogo
                                        class="block h-16 w-16 transition-transform group-hover:scale-105"
                                    />
                                    <div class="ml-3 hidden sm:block">
                                        <span class="text-2xl font-bold text-emerald-800 tracking-tight">
                                            Budget<span class="text-amber-600">Bean</span>
                                        </span>
                                        <div class="text-xs text-stone-500 -mt-1">
                                            Grow your finances 🌱
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden xl:ml-12 xl:flex xl:space-x-1">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    class="px-4 py-2 rounded-lg font-medium transition-colors border-2 border-transparent"
                                    :class="route().current('dashboard')
                                        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                        : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                                >
                                    <span class="flex items-center">
                                        <span class="mr-2">🏠</span>
                                        This Month
                                    </span>
                                </NavLink>
                                <NavLink
                                    :href="route('garden.index')"
                                    :active="route().current('garden.index')"
                                    class="px-4 py-2 rounded-lg font-medium transition-colors border-2 border-transparent"
                                    :class="route().current('garden.index')
                                        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                        : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                                >
                                    <span class="flex items-center">
                                        <span class="mr-2">🌻</span>
                                        My Garden
                                    </span>
                                </NavLink>
                                <NavLink
                                    :href="route('bills.index')"
                                    :active="route().current('bills.index')"
                                    class="px-4 py-2 rounded-lg font-medium transition-colors border-2 border-transparent"
                                    :class="route().current('bills.index')
                                        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                        : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                                >
                                    <span class="flex items-center">
                                        <span class="mr-2">💳</span>
                                        My Recurring Bills
                                    </span>
                                </NavLink>
                                <button
                                    @click="requireAuth(() => $inertia.visit(route('past-months.index')))"
                                    class="px-4 py-2 rounded-lg font-medium transition-colors border-2 border-transparent"
                                    :class="route().current('past-months.*')
                                        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                        : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                                >
                                    <span class="flex items-center">
                                        <span class="mr-2">📅</span>
                                        Past Months
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="hidden xl:ms-6 xl:flex xl:items-center">
                            <!-- Authenticated User Dropdown -->
                            <div v-if="$page.props.auth.user" class="relative ms-3">
                                <Dropdown align="right" width="56">
                                    <template #trigger>
                                        <div>
                    <span class="inline-flex rounded-lg">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-medium leading-4 text-stone-600 transition duration-150 ease-in-out hover:text-emerald-600 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            <div
                                class="w-8 h-8 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-lg flex items-center justify-center mr-3">
                                <span class="text-emerald-600 font-bold text-sm">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </span>
                            </div>
                            <span class="max-w-32 truncate">{{
                                    $page.props.auth.user.name
                                }}</span>

                            <svg
                                class="-me-0.5 ms-2 h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </span>
                                        </div>
                                    </template>
                                    <template #content>
                                        <div>
                                            <div class="px-4 py-3 border-b border-emerald-100">
                                                <div class="text-sm font-medium text-emerald-800">
                                                    {{ $page.props.auth.user.name }}
                                                </div>
                                                <div class="text-xs text-stone-500">{{
                                                        $page.props.auth.user.email
                                                    }}
                                                </div>
                                            </div>
                                            <DropdownLink
                                                :href="route('profile.edit')"
                                                class="flex items-center px-4 py-3 text-stone-700 hover:bg-emerald-50 hover:text-emerald-700"
                                            >
                                                <span class="mr-3">👤</span>
                                                Profile
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                                class="flex items-center px-4 py-3 text-stone-700 hover:bg-emerald-50 hover:text-emerald-700 w-full text-left"
                                            >
                                                <span class="mr-3">🚪</span>
                                                Log Out
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Guest User Buttons -->
                            <div v-else class="flex items-center gap-3">
                                <Link
                                    :href="route('login')"
                                    class="bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                                >
                                    <span class="mr-2">🚪</span>
                                    Login
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                                >
                                    <span class="mr-2">🌱</span>
                                    Create Account
                                </Link>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center xl:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-lg p-3 text-stone-500 transition duration-150 ease-in-out hover:bg-emerald-50 hover:text-emerald-600 focus:bg-emerald-50 focus:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="xl:hidden border-t border-emerald-100"
                >
                    <div class="bg-stone-50 px-4 py-3">
                        <div class="space-y-2">
                            <ResponsiveNavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                                class="flex items-center px-4 py-3 rounded-lg font-medium"
                                :class="route().current('dashboard')
                                    ? 'bg-emerald-100 text-emerald-800 border-l-4 border-emerald-500'
                                    : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                            >
                                <span class="mr-3">🏠</span>
                                This Month
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('garden.index')"
                                :active="route().current('garden.index')"
                                class="px-4 py-2 rounded-lg font-medium transition-colors border-2 border-transparent"
                                :class="route().current('garden.index')
                                        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                        : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                            >
                                <span class="flex items-center">
                                    <span class="mr-2">🌻</span>
                                    My Garden
                                </span>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('bills.index')"
                                :active="route().current('bills.index')"
                                class="px-4 py-2 rounded-lg font-medium transition-colors border-2 border-transparent"
                                :class="route().current('bills.index')
                                        ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                        : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                            >
                                <span class="flex items-center">
                                    <span class="mr-2">💳</span>
                                    My Recurring Bills
                                </span>
                            </ResponsiveNavLink>
                            <button
                                @click="requireAuth(() => $inertia.visit(route('past-months.index')))"
                                class="flex items-center px-4 py-3 rounded-lg font-medium text-stone-600 hover:text-emerald-700 hover:bg-emerald-50 w-full text-left"
                                :class="route().current('past-months.index')
                                ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                : 'text-stone-600 hover:text-emerald-700 hover:bg-emerald-50'"
                            >
                                <span class="mr-3">📅</span>
                                Past Months
                            </button>
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-emerald-200 bg-white px-4 py-4">
                        <!-- Authenticated User Section -->
                        <div v-if="$page.props.auth.user">
                            <div class="flex items-center mb-4">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center mr-3">
                <span class="text-emerald-600 font-bold text-lg">
                    {{ $page.props.auth.user.name.charAt(0) }}
                </span>
                                </div>
                                <div>
                                    <div class="text-base font-semibold text-emerald-800">
                                        {{ $page.props.auth.user.name }}
                                    </div>
                                    <div class="text-sm text-stone-500">
                                        {{ $page.props.auth.user.email }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <ResponsiveNavLink
                                    :href="route('profile.edit')"
                                    class="flex items-center px-4 py-3 rounded-lg font-medium text-stone-600 hover:text-emerald-700 hover:bg-emerald-50"
                                >
                                    <span class="mr-3">👤</span>
                                    Profile
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex items-center px-4 py-3 rounded-lg font-medium text-stone-600 hover:text-emerald-700 hover:bg-emerald-50 w-full text-left"
                                >
                                    <span class="mr-3">🚪</span>
                                    Log Out
                                </ResponsiveNavLink>
                            </div>
                        </div>

                        <!-- Guest User Section -->
                        <div v-else>
                            <div class="text-center mb-4">
                                <h3 class="text-lg font-semibold text-emerald-800 mb-2">Join BudgetBean! 🌱</h3>
                                <p class="text-sm text-stone-500">Create an account to save your financial garden's
                                    progress</p>
                            </div>

                            <div class="space-y-3">
                                <Link
                                    :href="route('login')"
                                    class="flex items-center justify-center px-4 py-3 rounded-lg font-medium bg-stone-100 hover:bg-stone-200 text-stone-700 transition duration-200 w-full"
                                >
                                    <span class="mr-3">🚪</span>
                                    Login
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="flex items-center justify-center px-4 py-3 rounded-lg font-medium bg-emerald-100 hover:bg-emerald-200 text-emerald-700 transition duration-200 w-full"
                                >
                                    <span class="mr-3">🌱</span>
                                    Create Account
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow-sm border-b border-emerald-100"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <slot name="header"/>
                </div>
            </header>

            <!-- Flash Messages -->
            <FlashMessage
                :success="$page.props.flash?.success"
                :error="$page.props.flash?.error"
                :info="$page.props.flash?.info"
                :warning="$page.props.flash?.warning"
            />

            <!-- Page Content -->
            <main>
                <slot/>
            </main>
        </div>
        <AuthRequiredModal :show="showAuthModal" @close="closeAuthModal"/>
    </div>
</template>

<style scoped>
/* Custom focus styles for better accessibility with the new color scheme */
button:focus-visible {
    outline: 2px solid theme('colors.emerald.500');
    outline-offset: 2px;
}
</style>
