<script setup>
import { ref, watch } from 'vue';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';

const props = defineProps({
    isOpen: Boolean,
    closeBtn: { type: Boolean, default: true },
    showHeader: { type: Boolean, default: true },
    updatePaymentData: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['close', 'submit']);

const formData = ref({
    id: '',
    payment_id: '',
    payment_method: '',
    paid_at: null,
    total: null
});

const paymentMethods = [
    'UPI',
    'Card',
    'Net Banking',
    'Wallet',
    'Cash',
    'Cheque',
    'Bank Transfer'
];

// Watch for changes in updatePaymentData prop to populate form
watch(() => props.updatePaymentData, (newData) => {
    if (newData && Object.keys(newData).length > 0) {
        formData.value.id = newData.id;
        formData.value.total = newData.total;
    }
}, { immediate: true, deep: true });

function closeUpdatePaymentModal() {
    emit('close', true);
}

function updatePaymentConfirm() {
    // Validate form
    if (!formData.value.payment_id || !formData.value.payment_method || !formData.value.paid_at) {
        alert('Please fill in all required fields');
        return;
    }

    // Emit submit event with form data
    emit('submit', formData.value);

    // Close modal
    closeUpdatePaymentModal();
}

function resetForm() {
    formData.value = {
        id: '',
        payment_id: '',
        payment_method: '',
        paid_at: null,
        total: null
    };
}

// Reset form when modal closes
watch(() => props.isOpen, (newVal) => {
    if (!newVal) {
        resetForm();
    }
});
</script>

<template>
    <TransitionRoot appear :show="props.isOpen" as="template">
        <Dialog as="div" class="relative z-50" @close="closeUpdatePaymentModal">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black bg-opacity-40" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95">
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-xl bg-white text-left align-middle shadow-xl transition-all">

                            <!-- Header -->
                            <div v-if="props.showHeader" class="flex justify-between items-center px-6 py-4"
                                style="background: linear-gradient(135deg, #ff5100 0%, #ff7a3d 100%);">
                                <DialogTitle as="h3" class="text-xl font-bold text-white">
                                    Update Payment Details
                                </DialogTitle>

                                <span @click="closeUpdatePaymentModal" v-if="closeBtn"
                                    class="bg-white bg-opacity-20 rounded-full p-1.5 hover:bg-opacity-30 cursor-pointer transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                        <path fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M17 7L7 17M7 7l10 10" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Body -->
                            <form @submit.prevent="updatePaymentConfirm" class="px-6 py-6">
                                <!-- Payment ID Field -->
                                <div class="mb-4">
                                    <label for="payment_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Payment ID <span class="text-red-500">*</span>
                                    </label>
                                    <input id="payment_id" v-model="formData.payment_id" type="text" required
                                        placeholder="Enter payment ID"
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary transition-all" />
                                </div>

                                <!-- Payment Method Field -->
                                <div class="mb-4">
                                    <label for="payment_method" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Payment Method <span class="text-red-500">*</span>
                                    </label>
                                    <select id="payment_method" v-model="formData.payment_method" required
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary transition-all appearance-none bg-white cursor-pointer">
                                        <option value="" disabled>Select payment method</option>
                                        <option v-for="method in paymentMethods" :key="method" :value="method">
                                            {{ method }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Payment Date Field -->
                                <div class="mb-6">
                                    <label for="paid_at" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Payment Date <span class="text-red-500">*</span>
                                    </label>
                                    <input id="paid_at" v-model="formData.paid_at" type="date" required
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary transition-all cursor-pointer" />
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex justify-end gap-3">
                                    <button type="button"
                                        class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all border-2 border-gray-300 text-gray-700 hover:bg-gray-50"
                                        @click="closeUpdatePaymentModal">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all shadow-md hover:shadow-lg"
                                        style="background-color: #ff5100;"
                                        onmouseover="this.style.backgroundColor='#e64900';"
                                        onmouseout="this.style.backgroundColor='#ff5100';">
                                        Update Payment
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<style scoped>
/* Focus ring color for inputs */
input:focus,
select:focus {
    border-color: #ff5100;
}

/* Custom select arrow */
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    padding-right: 2.5rem;
}
</style>