<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
// Partials
import BillsOverviewStats from './Partials/BillsOverviewStats.vue';
import EmptyBillsState from './Partials/EmptyBillsState.vue';
import BillsGrid from './Partials/BillsGrid.vue';
import AddBillModal from './Partials/AddBillModal.vue';
import EditBillModal from './Partials/EditBillModal.vue';

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

const closeModals = () => {
    showAddBillModal.value = false;
    showEditBillModal.value = false;
    selectedBill.value = null;
};

// Form update handlers for modals
const updateAddBillForm = (newForm) => {
    Object.assign(addBillForm, newForm);
};

const updateEditBillForm = (newForm) => {
    Object.assign(editBillForm, newForm);
};

const handleDeleteBillFromModal = () => {
    deleteBill(selectedBill.value);
    closeModals();
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
                <BillsOverviewStats
                    v-if="hasBills"
                    :stats="stats"
                />

                <!-- Empty State -->
                <EmptyBillsState
                    v-if="!hasBills"
                    @open-add-bill-modal="openAddBillModal"
                />

                <!-- Bills List -->
                <BillsGrid
                    v-if="hasBills"
                    :recurring-bills="recurringBills"
                    @edit-bill="openEditBillModal"
                />
            </div>
        </div>

        <!-- Modal Components -->
        <AddBillModal
            :show="showAddBillModal"
            :form="addBillForm"
            @close="closeModals"
            @submit="submitAddBill"
            @update:form="updateAddBillForm"
        />

        <EditBillModal
            :show="showEditBillModal"
            :form="editBillForm"
            @close="closeModals"
            @submit="submitEditBill"
            @update:form="updateEditBillForm"
            @delete-bill="handleDeleteBillFromModal"
        />
    </AuthenticatedLayout>
</template>
