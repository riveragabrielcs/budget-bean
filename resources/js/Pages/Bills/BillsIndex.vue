<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    recurringBills: Array,
    stats: Object,
});

// Modal states
const showAddBillModal = ref(false);
const showEditBillModal = ref(false);
const selectedBill = ref(null);

// Forms
const addBillForm = useForm({
    name: '',
    amount: '',
    bill_date: '',
    description: '',
});

const editBillForm = useForm({
    name: '',
    amount: '',
    bill_date: '',
    description: '',
});

// Computed properties
const hasBills = computed(() => props.recurringBills.length > 0);

// Methods
const openAddBillModal = () => {
    addBillForm.reset();
    showAddBillModal.value = true;
};

const openEditBillModal = (bill) => {
    selectedBill.value = bill;
    editBillForm.name = bill.name;
    editBillForm.amount = bill.amount;
    editBillForm.bill_date = bill.bill_date || '';
    editBillForm.description = bill.description || '';
    showEditBillModal.value = true;
};

const submitAddBill = () => {
    addBillForm.post(route('bills.store'), {
        onSuccess: () => {
            showAddBillModal.value = false;
            addBillForm.reset();
        },
    });
};

const submitEditBill = () => {
    editBillForm.patch(route('bills.update', selectedBill.value.id), {
        onSuccess: () => {
            showEditBillModal.value = false;
            editBillForm.reset();
            selectedBill.value = null;
        },
    });
};

const deleteBill = (bill) => {
    if (confirm(`Are you sure you want to delete "${bill.name}"? This action cannot be undone.`)) {
        useForm().delete(route('bills.destroy', bill.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
};

const closeModals = () => {
    showAddBillModal.value = false;
    showEditBillModal.value = false;
    selectedBill.value = null;
};
</script>

<template>
    <Head title="My Recurring Bills" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <h2 class="text-2xl font-bold leading-tight text-emerald-800">
                        My Recurring Bills
                    </h2>
                    <span class="ml-3 text-2xl">💳</span>
                </div>
                <button
                    v-if="hasBills"
                    @click="openAddBillModal"
                    class="mt-4 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center"
                >
                    <span class="mr-2">➕</span>
                    Add New Bill
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Bills Overview Stats (if has bills) -->
                <div v-if="hasBills" class="mb-8">
                    <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-emerald-100">
                        <div class="p-6 bg-gradient-to-br from-emerald-50 via-green-50 to-amber-50 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full opacity-20 -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-amber-100 rounded-full opacity-20 translate-y-12 -translate-x-12"></div>

                            <div class="relative">
                                <h3 class="text-xl font-semibold text-emerald-800 mb-4">Your Monthly Bills Overview</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-emerald-700">{{ stats.total_bills }}</div>
                                        <div class="text-sm text-stone-600">Total Bills</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(stats.total_monthly_amount) }}</div>
                                        <div class="text-sm text-stone-600">Monthly Total</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-stone-700">{{ formatCurrency(stats.average_bill_amount) }}</div>
                                        <div class="text-sm text-stone-600">Average Bill</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!hasBills" class="text-center py-16">
                    <div class="bg-white shadow-lg sm:rounded-xl border border-emerald-100 p-12">
                        <div class="w-24 h-24 bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-emerald-200">
                            <span class="text-4xl text-emerald-500">💳</span>
                        </div>
                        <h3 class="text-2xl font-semibold text-emerald-800 mb-4">Set Up Your Recurring Bills</h3>
                        <p class="text-lg text-stone-600 max-w-2xl mx-auto leading-relaxed mb-8">
                            Add your monthly recurring bills like rent, utilities, subscriptions, and loans.
                            This will help you automatically track your monthly expenses without having to enter them each time! 💰
                        </p>
                        <button
                            @click="openAddBillModal"
                            class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-semibold py-4 px-8 rounded-xl transition duration-200 flex items-center mx-auto shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                        >
                            <span class="mr-3 text-lg">➕</span>
                            Add Your First Bill
                        </button>
                    </div>
                </div>

                <!-- Bills List -->
                <div v-if="hasBills">
                    <h3 class="text-xl font-semibold text-emerald-800 mb-6 flex items-center">
                        <span class="mr-2">📋</span>
                        Your Recurring Bills ({{ recurringBills.length }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="bill in recurringBills"
                            :key="bill.id"
                            class="bg-white border border-emerald-100 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden"
                        >
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">💳</span>
                                        <div>
                                            <h4 class="font-semibold text-emerald-800 text-lg">{{ bill.name }}</h4>
                                            <p v-if="bill.next_due_date" class="text-sm text-emerald-600">Next due: {{ bill.next_due_date }}</p>
                                            <p v-else-if="bill.formatted_bill_date" class="text-sm text-emerald-600">Due: {{ bill.formatted_bill_date }} of each month</p>
                                        </div>
                                    </div>
                                    <button
                                        @click="openEditBillModal(bill)"
                                        class="text-stone-400 hover:text-emerald-600 transition-colors p-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div v-if="bill.description" class="mb-4">
                                    <p class="text-sm text-stone-600">{{ bill.description }}</p>
                                </div>

                                <div class="mb-4">
                                    <div class="text-center p-4 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg border border-amber-100">
                                        <div class="text-2xl font-bold text-amber-700">{{ formatCurrency(bill.amount) }}</div>
                                        <div class="text-sm text-stone-600">per month</div>
                                    </div>
                                </div>

                                <div class="space-y-2 text-xs text-stone-500">
                                    <div class="flex justify-between">
                                        <span>Added:</span>
                                        <span>{{ bill.created_at }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Bill Modal -->
        <div v-if="showAddBillModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                            <span class="mr-2">➕</span>
                            Add New Bill
                        </h3>
                        <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitAddBill" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Bill Name *</label>
                            <input
                                v-model="addBillForm.name"
                                type="text"
                                placeholder="e.g., Electricity, Gym Membership, Student Loan"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                            <div v-if="addBillForm.errors.name" class="text-red-600 text-sm mt-1">{{ addBillForm.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Monthly Amount *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-stone-500">$</span>
                                <input
                                    v-model="addBillForm.amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="150.00"
                                    class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    required
                                />
                            </div>
                            <div v-if="addBillForm.errors.amount" class="text-red-600 text-sm mt-1">{{ addBillForm.errors.amount }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Due Date (Optional)</label>
                            <select
                                v-model="addBillForm.bill_date"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option value="">Select day of month...</option>
                                <option v-for="day in 31" :key="day" :value="day">{{ day }}{{ day === 1 ? 'st' : day === 2 ? 'nd' : day === 3 ? 'rd' : 'th' }} of each month</option>
                            </select>
                            <div v-if="addBillForm.errors.bill_date" class="text-red-600 text-sm mt-1">{{ addBillForm.errors.bill_date }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Description (Optional)</label>
                            <textarea
                                v-model="addBillForm.description"
                                placeholder="Any notes about this bill..."
                                rows="3"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                            <div v-if="addBillForm.errors.description" class="text-red-600 text-sm mt-1">{{ addBillForm.errors.description }}</div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="closeModals"
                                class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="addBillForm.processing"
                                class="flex-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                            >
                                <span v-if="addBillForm.processing">Adding...</span>
                                <span v-else>➕ Add Bill</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Bill Modal -->
        <div v-if="showEditBillModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-emerald-800 flex items-center">
                            <span class="mr-2">✏️</span>
                            Edit Bill
                        </h3>
                        <button @click="closeModals" class="text-stone-400 hover:text-stone-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitEditBill" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Bill Name</label>
                            <input
                                v-model="editBillForm.name"
                                type="text"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                required
                            />
                            <div v-if="editBillForm.errors.name" class="text-red-600 text-sm mt-1">{{ editBillForm.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Monthly Amount</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-stone-500">$</span>
                                <input
                                    v-model="editBillForm.amount"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="w-full pl-8 pr-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    required
                                />
                            </div>
                            <div v-if="editBillForm.errors.amount" class="text-red-600 text-sm mt-1">{{ editBillForm.errors.amount }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Due Date</label>
                            <select
                                v-model="editBillForm.bill_date"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                                <option value="">No specific date</option>
                                <option v-for="day in 31" :key="day" :value="day">{{ day }}{{ day === 1 ? 'st' : day === 2 ? 'nd' : day === 3 ? 'rd' : 'th' }} of each month</option>
                            </select>
                            <div v-if="editBillForm.errors.bill_date" class="text-red-600 text-sm mt-1">{{ editBillForm.errors.bill_date }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-2">Description</label>
                            <textarea
                                v-model="editBillForm.description"
                                rows="3"
                                class="w-full px-4 py-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            ></textarea>
                            <div v-if="editBillForm.errors.description" class="text-red-600 text-sm mt-1">{{ editBillForm.errors.description }}</div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="deleteBill(selectedBill)"
                                class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                            >
                                Delete
                            </button>
                            <button
                                type="button"
                                @click="closeModals"
                                class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 px-4 rounded-lg transition duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="editBillForm.processing"
                                class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50"
                            >
                                <span v-if="editBillForm.processing">Saving...</span>
                                <span v-else>Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
