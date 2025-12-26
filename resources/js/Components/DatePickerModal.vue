<script setup>
import { ref, watch, computed, onBeforeUnmount, nextTick } from 'vue';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import FormDateInput from './FormDateInput.vue';

const props = defineProps({
    isOpen: Boolean,
    label: { type: String, default: 'Select Date' },
    closeBtn: { type: Boolean, default: true },
    showHeader: { type: Boolean, default: true },
    modelValue: [Date, String, null],
    defaultDate: { type: [Date, String], default: null },
    required: { type: Boolean, default: false },
    helperText: String,
    placeholder: { type: String, default: 'Select date' },
    showPaymentTerms: { type: Boolean, default: false },
    paymentTermsValue: { type: Object, default: () => ({ type: 'percentage', value: 100 }) },
    paymentTermsRequired: { type: Boolean, default: false },
    totalAmount: { type: [Number, String], default: 0 },
    minPercentage: { type: Number, default: 20 },
    maxPercentage: { type: Number, default: 100 }
});

const emit = defineEmits(['close', 'update:modelValue', 'submit', 'update:paymentTerms']);

// State
const selectedDate = ref(null);
const paymentType = ref('percentage');
const paymentValue = ref(100);
const selectedPreset = ref(null);
const validationError = ref('');
const validationTimeout = ref(null);
const isInitialized = ref(false);

const presetPercentages = [
    { label: '20%', value: 20 },
    { label: '50%', value: 50 },
    { label: '100%', value: 100 }
];

// Computed values
const totalAmountNum = computed(() => {
    const num = Number(props.totalAmount);
    return isNaN(num) ? 0 : num;
});

const minAmount = computed(() => {
    return (totalAmountNum.value * props.minPercentage) / 100;
});

const maxAmount = computed(() => {
    return totalAmountNum.value;
});

const calculatedAmount = computed(() => {
    const value = Number(paymentValue.value);
    if (isNaN(value) || value < 0) return 0;

    if (paymentType.value === 'percentage') {
        return (totalAmountNum.value * value) / 100;
    }
    return value;
});

const displayAmount = computed(() => {
    return formatCurrency(calculatedAmount.value);
});

const isValidPayment = computed(() => {
    if (!props.showPaymentTerms) return true;

    const amount = calculatedAmount.value;
    const min = minAmount.value;
    const max = maxAmount.value;

    // Handle edge cases
    if (amount < 0 || isNaN(amount)) return false;
    if (min < 0 || max < 0) return false;

    return amount >= min && amount <= max;
});

const isValidDate = computed(() => {
    if (!props.required) return true;
    return selectedDate.value !== null && selectedDate.value !== undefined && selectedDate.value !== '';
});

const currentPercentage = computed(() => {
    if (totalAmountNum.value === 0) return 0;

    if (paymentType.value === 'percentage') {
        const value = Number(paymentValue.value);
        return isNaN(value) ? 0 : value;
    }

    const value = Number(paymentValue.value);
    if (isNaN(value)) return 0;

    return (value / totalAmountNum.value) * 100;
});

const isSubmitDisabled = computed(() => {
    // Check date validity
    if (!isValidDate.value) return true;

    // Check payment validity if payment terms are shown
    if (props.showPaymentTerms) {
        if (!isValidPayment.value) return true;
        if (validationError.value) return true;
    }

    return false;
});

// Helper functions
function formatCurrency(amount) {
    const num = Number(amount);
    if (isNaN(num)) return '₹0.00';

    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 2
    }).format(num);
}

function formatCurrencyShort(amount) {
    const num = Number(amount);
    if (isNaN(num)) return '₹0';

    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(num);
}

function isPresetActive(presetValue) {
    if (paymentType.value !== 'percentage') {
        return false;
    }
    const currentValue = Number(paymentValue.value);
    if (isNaN(currentValue)) return false;

    return Math.abs(currentValue - presetValue) < 0.01;
}

// Initialize form with default/prop values
function initializeForm() {
    // Set date - prioritize modelValue, then defaultDate
    const initialDate = props.modelValue || props.defaultDate;
    selectedDate.value = initialDate;

    // Set payment values
    if (props.paymentTermsValue && typeof props.paymentTermsValue === 'object') {
        paymentType.value = props.paymentTermsValue.type || 'percentage';
        const initialValue = props.paymentTermsValue.value;
        paymentValue.value = initialValue !== undefined && initialValue !== null ? initialValue : 100;
    } else {
        paymentType.value = 'percentage';
        paymentValue.value = 100;
    }

    // Set selected preset if value matches
    const currentVal = Number(paymentValue.value);
    if (paymentType.value === 'percentage' && !isNaN(currentVal) && [20, 50, 100].includes(currentVal)) {
        selectedPreset.value = currentVal;
    } else {
        selectedPreset.value = null;
    }

    validationError.value = '';
    isInitialized.value = true;
}

// Reset form to default state
function resetForm() {
    selectedDate.value = null;
    paymentType.value = 'percentage';
    paymentValue.value = 100;
    selectedPreset.value = 100;
    validationError.value = '';
    isInitialized.value = false;

    if (validationTimeout.value) {
        clearTimeout(validationTimeout.value);
        validationTimeout.value = null;
    }
}

// Watch for modal open/close
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        nextTick(() => {
            initializeForm();
        });
    } else {
        isInitialized.value = false;
    }
});

// Watch for external date changes
watch(() => props.modelValue, (newVal) => {
    if (props.isOpen && isInitialized.value) {
        selectedDate.value = newVal;
    }
});

watch(() => props.defaultDate, (newVal) => {
    if (props.isOpen && isInitialized.value && !selectedDate.value) {
        selectedDate.value = newVal;
    }
});

// Validate payment value
function validatePaymentValue(value, type, showError = true) {
    if (!showError || !props.showPaymentTerms) {
        return true;
    }

    validationError.value = '';
    const numValue = Number(value);

    if (isNaN(numValue)) {
        validationError.value = 'Please enter a valid number';
        return false;
    }

    if (numValue <= 0) {
        validationError.value = 'Payment value must be greater than 0';
        return false;
    }

    if (type === 'percentage') {
        if (numValue < props.minPercentage) {
            validationError.value = `Percentage must be at least ${props.minPercentage}%`;
            return false;
        }
        if (numValue > props.maxPercentage) {
            validationError.value = `Percentage cannot exceed ${props.maxPercentage}%`;
            return false;
        }
    } else {
        if (numValue < minAmount.value) {
            validationError.value = `Amount must be at least ${formatCurrencyShort(minAmount.value)} (${props.minPercentage}% of total)`;
            return false;
        }
        if (numValue > maxAmount.value) {
            validationError.value = `Amount cannot exceed ${formatCurrencyShort(maxAmount.value)} (total amount)`;
            return false;
        }
    }

    return true;
}

function selectPreset(preset) {
    selectedPreset.value = preset.value;
    paymentType.value = 'percentage';
    paymentValue.value = preset.value;
    validationError.value = '';
}

function handlePaymentTypeChange(newType) {
    if (paymentType.value === newType) {
        return;
    }

    paymentType.value = newType;
    selectedPreset.value = null;

    const currentVal = Number(paymentValue.value);
    if (isNaN(currentVal)) {
        paymentValue.value = newType === 'percentage' ? 100 : totalAmountNum.value;
        return;
    }

    if (newType === 'percentage') {
        // Convert amount to percentage
        if (currentVal > 100) {
            const percentage = (currentVal / totalAmountNum.value) * 100;
            paymentValue.value = Math.round(Math.min(Math.max(percentage, props.minPercentage), props.maxPercentage) * 100) / 100;
        }
        // Check if new value matches a preset
        const newVal = Number(paymentValue.value);
        if (!isNaN(newVal) && [20, 50, 100].includes(newVal)) {
            selectedPreset.value = newVal;
        }
    } else {
        // Convert percentage to amount
        if (currentVal <= 100) {
            paymentValue.value = Math.round((totalAmountNum.value * currentVal) / 100 * 100) / 100;
        }
    }

    validationError.value = '';
}

function handleCustomInput(event) {
    const value = event.target.value;
    const numValue = Number(value);

    // Update selected preset status
    if (!isNaN(numValue) && paymentType.value === 'percentage' && [20, 50, 100].includes(numValue)) {
        selectedPreset.value = numValue;
    } else {
        selectedPreset.value = null;
    }

    // Apply real-time constraints
    if (!isNaN(numValue)) {
        if (paymentType.value === 'percentage') {
            paymentValue.value = Math.min(Math.max(numValue, 0), props.maxPercentage);
        } else {
            paymentValue.value = Math.min(Math.max(numValue, 0), maxAmount.value);
        }
    } else {
        paymentValue.value = value;
    }

    // Debounce validation
    if (validationTimeout.value) {
        clearTimeout(validationTimeout.value);
    }

    validationTimeout.value = setTimeout(() => {
        validatePaymentValue(paymentValue.value, paymentType.value, true);
    }, 800);
}

function handleInputBlur() {
    const numValue = Number(paymentValue.value);

    if (isNaN(numValue)) {
        paymentValue.value = paymentType.value === 'percentage' ? 100 : totalAmountNum.value;
        return;
    }

    // Auto-correct to minimum if below threshold
    if (paymentType.value === 'percentage') {
        if (numValue < props.minPercentage && numValue > 0) {
            paymentValue.value = props.minPercentage;
            selectedPreset.value = props.minPercentage === 20 ? 20 : null;
            validationError.value = `Auto-corrected to minimum ${props.minPercentage}%`;
            setTimeout(() => { validationError.value = ''; }, 3000);
        } else if (numValue === 0) {
            paymentValue.value = 100;
            selectedPreset.value = 100;
        }

        // Round to 2 decimal places
        paymentValue.value = Math.round(Number(paymentValue.value) * 100) / 100;

        // Check if matches preset after rounding
        const roundedVal = Number(paymentValue.value);
        if ([20, 50, 100].includes(roundedVal)) {
            selectedPreset.value = roundedVal;
        }
    } else {
        if (numValue < minAmount.value && numValue > 0) {
            paymentValue.value = Math.round(minAmount.value * 100) / 100;
            validationError.value = `Auto-corrected to minimum ${formatCurrencyShort(minAmount.value)}`;
            setTimeout(() => { validationError.value = ''; }, 3000);
        } else if (numValue === 0) {
            paymentValue.value = totalAmountNum.value;
        }

        // Round to 2 decimal places
        paymentValue.value = Math.round(Number(paymentValue.value) * 100) / 100;
    }

    validatePaymentValue(paymentValue.value, paymentType.value, true);
}

function closeModal() {
    resetForm();
    emit('close', true);
}

function handleSubmit() {
    // Validate date first
    if (props.required && !selectedDate.value) {
        validationError.value = 'Please select a date';
        return;
    }

    // Validate payment terms if required
    if (props.showPaymentTerms) {
        const numValue = Number(paymentValue.value);

        if (isNaN(numValue) || numValue <= 0) {
            validationError.value = 'Please enter a valid payment amount';
            return;
        }

        if (!validatePaymentValue(paymentValue.value, paymentType.value, true)) {
            return;
        }

        if (!isValidPayment.value) {
            validationError.value = 'Payment amount is outside the valid range';
            return;
        }
    }

    // Prepare payment data
    const paymentData = {
        type: paymentType.value,
        value: Number(paymentValue.value),
        calculatedAmount: calculatedAmount.value,
        percentage: currentPercentage.value
    };

    // Emit events
    emit('update:modelValue', selectedDate.value);

    if (props.showPaymentTerms) {
        emit('update:paymentTerms', paymentData);
        emit('submit', {
            date: selectedDate.value,
            paymentTerms: paymentData
        });
    } else {
        emit('submit', selectedDate.value);
    }

    // Close form after successful submit
    closeModal();
}

// Cleanup on unmount
onBeforeUnmount(() => {
    if (validationTimeout.value) {
        clearTimeout(validationTimeout.value);
    }
});
</script>

<template>
    <TransitionRoot appear :show="props.isOpen" as="template">
        <Dialog as="div" class="relative z-10">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95">
                        <DialogPanel
                            class="w-full max-w-md transform rounded-xl bg-white text-left align-middle shadow-xl transition-all">
                            <div v-if="props.showHeader" class="flex justify-between items-center bg-gray-50 px-4 py-3">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                    {{ props.label }}
                                </DialogTitle>

                                <button @click="closeModal" v-if="closeBtn" type="button"
                                    class="bg-slate-100 rounded-full p-1 hover:shadow hover:bg-slate-200 cursor-pointer transition-colors"
                                    aria-label="Close modal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path fill="none" stroke="black" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M17 7L7 17M7 7l10 10" />
                                    </svg>
                                </button>
                            </div>

                            <div class="px-4 py-4 space-y-4">
                                <!-- Date Input -->
                                <FormDateInput v-model="selectedDate" :defaultDate="props.defaultDate"
                                    name="modal_date_input" label="Select Date" :required="props.required"
                                    :helperText="props.helperText" :placeholder="props.placeholder" />

                                <!-- Payment Terms Section -->
                                <div v-if="props.showPaymentTerms" class="space-y-3">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Payment Terms
                                        <span v-if="props.paymentTermsRequired" class="text-red-500">*</span>
                                    </label>

                                    <!-- Preset Percentage Buttons -->
                                    <div class="grid grid-cols-3 gap-2">
                                        <button v-for="preset in presetPercentages" :key="preset.value" type="button"
                                            @click="selectPreset(preset)" :class="[
                                                'px-4 py-2.5 text-sm font-medium rounded-md border transition-all duration-200',
                                                isPresetActive(preset.value)
                                                    ? 'bg-primary text-white border-primary shadow-md'
                                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                                            ]">
                                            {{ preset.label }}
                                        </button>
                                    </div>

                                    <!-- Custom Input Section -->
                                    <div class="space-y-2">
                                        <p class="text-xs text-gray-500 font-medium">Or enter custom amount:</p>

                                        <!-- Type Selector -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="handlePaymentTypeChange('percentage')" :class="[
                                                'px-3 py-2 text-sm font-medium rounded-md border transition-all duration-200',
                                                paymentType === 'percentage'
                                                    ? 'bg-indigo-50 text-indigo-700 border-indigo-300 shadow-sm'
                                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                            ]">
                                                <span class="flex items-center justify-center gap-1">
                                                    <span>Percentage</span>
                                                    <span class="text-xs">(%)</span>
                                                </span>
                                            </button>
                                            <button type="button" @click="handlePaymentTypeChange('amount')" :class="[
                                                'px-3 py-2 text-sm font-medium rounded-md border transition-all duration-200',
                                                paymentType === 'amount'
                                                    ? 'bg-indigo-50 text-indigo-700 border-indigo-300 shadow-sm'
                                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                            ]">
                                                <span class="flex items-center justify-center gap-1">
                                                    <span>Amount</span>
                                                    <span class="text-xs">(₹)</span>
                                                </span>
                                            </button>
                                        </div>

                                        <!-- Input Field -->
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-sm font-medium">
                                                    {{ paymentType === 'percentage' ? '%' : '₹' }}
                                                </span>
                                            </div>
                                            <input :value="paymentValue" @input="handleCustomInput"
                                                @blur="handleInputBlur" type="number"
                                                :min="paymentType === 'percentage' ? minPercentage : minAmount"
                                                :max="paymentType === 'percentage' ? maxPercentage : maxAmount"
                                                step="0.01" :class="[
                                                    'w-full pl-8 pr-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 text-sm transition-colors',
                                                    validationError && !isValidPayment
                                                        ? 'border-red-300 focus:ring-red-500 focus:border-red-500 bg-red-50'
                                                        : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'
                                                ]" :placeholder="paymentType === 'percentage'
                                                    ? `${minPercentage} - ${maxPercentage}`
                                                    : `${formatCurrencyShort(minAmount)}`" />
                                        </div>

                                        <!-- Validation Error Message -->
                                        <transition enter-active-class="transition-opacity duration-200"
                                            leave-active-class="transition-opacity duration-200"
                                            enter-from-class="opacity-0" leave-to-class="opacity-0">
                                            <div v-if="validationError"
                                                class="flex items-start gap-1.5 text-xs text-red-600 bg-red-50 border border-red-200 rounded-md p-2">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4 flex-shrink-0 mt-0.5" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span>{{ validationError }}</span>
                                            </div>
                                        </transition>

                                        <!-- Helper Text -->
                                        <div class="text-xs text-gray-500 bg-gray-50 rounded-md p-2">
                                            <div class="flex items-center justify-between">
                                                <span>Min: {{ minPercentage }}% ({{ formatCurrencyShort(minAmount)
                                                }})</span>
                                                <span>Max: {{ maxPercentage }}% ({{ formatCurrencyShort(maxAmount)
                                                }})</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Calculated Amount Display -->
                                    <div :class="[
                                        'border rounded-lg p-3 transition-all duration-200',
                                        isValidPayment
                                            ? 'bg-gradient-to-br from-blue-50 to-indigo-50 border-blue-200'
                                            : 'bg-gradient-to-br from-red-50 to-pink-50 border-red-200'
                                    ]">
                                        <div class="flex justify-between items-center">
                                            <span :class="[
                                                'text-sm font-medium',
                                                isValidPayment ? 'text-gray-700' : 'text-red-700'
                                            ]">
                                                Payment Amount:
                                            </span>
                                            <span :class="[
                                                'text-lg font-bold',
                                                isValidPayment ? 'text-gray-900' : 'text-red-700'
                                            ]">
                                                {{ displayAmount }}
                                            </span>
                                        </div>
                                        <div v-if="totalAmount > 0"
                                            class="flex justify-between items-center text-xs mt-1.5 pt-1.5 border-t"
                                            :class="isValidPayment ? 'text-gray-600 border-blue-200' : 'text-red-600 border-red-200'">
                                            <span>Total Invoice:</span>
                                            <span class="font-semibold">{{ formatCurrency(totalAmountNum) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs mt-1"
                                            :class="isValidPayment ? 'text-indigo-600' : 'text-red-600'">
                                            <span>Percentage:</span>
                                            <span class="font-semibold">{{ currentPercentage.toFixed(2) }}%</span>
                                        </div>
                                        <div v-if="!isValidPayment && props.showPaymentTerms"
                                            class="flex items-center gap-1.5 mt-2 pt-2 text-xs text-red-700 border-t border-red-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span class="font-medium">Payment amount is outside the valid range</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 px-4 pb-4 pt-2 border-t border-gray-100">
                                <button type="button"
                                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 transition-colors"
                                    @click="closeModal">
                                    Cancel
                                </button>
                                <button type="button" :disabled="isSubmitDisabled" :class="[
                                    'inline-flex justify-center rounded-md border border-transparent px-5 py-2 text-sm font-medium text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 transition-all duration-200',
                                    isSubmitDisabled
                                        ? 'bg-gray-300 cursor-not-allowed opacity-60'
                                        : 'bg-primary hover:bg-primary/90 hover:shadow-md'
                                ]" @click="handleSubmit">
                                    Confirm
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>