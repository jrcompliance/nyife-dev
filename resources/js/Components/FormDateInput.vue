<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: [Date, String, null],
    defaultDate: { type: [Date, String], default: null },
    name: String,
    className: String,
    label: String,
    placeholder: { type: String, default: 'Select date' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    helperText: String,
    errorMessage: String
});

const emit = defineEmits(['update:modelValue']);

const internalDate = ref(null);
const currentMonth = ref(new Date());
const isOpen = ref(false);
const dropdownRef = ref(null);

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const quickOptions = [
    { label: '7 Days', days: 7 },
    { label: '1 Month', months: 1 },
    { label: '2 Months', months: 2 },
    { label: '3 Months', months: 3 }
];

const model = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const displayValue = computed(() => {
    const date = model.value || internalDate.value;
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const calendarDays = computed(() => {
    const daysInMonth = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() + 1,
        0
    ).getDate();

    const firstDay = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth(),
        1
    ).getDay();

    const days = [];
    for (let i = 0; i < firstDay; i++) {
        days.push(null);
    }
    for (let day = 1; day <= daysInMonth; day++) {
        days.push(day);
    }
    return days;
});

const initializeDate = () => {
    if (model.value) {
        internalDate.value = new Date(model.value);
    } else if (props.defaultDate) {
        internalDate.value = new Date(props.defaultDate);
        model.value = internalDate.value;
    }

    if (internalDate.value) {
        currentMonth.value = new Date(internalDate.value);
    }
};

const toggleDropdown = () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
    }
};

const closeDropdown = () => {
    isOpen.value = false;
};

const handleQuickSelect = (option) => {
    const newDate = new Date();
    if (option.days) {
        newDate.setDate(newDate.getDate() + option.days);
    } else if (option.months) {
        newDate.setMonth(newDate.getMonth() + option.months);
    }
    selectDate(newDate);
};

const selectDate = (date) => {
    internalDate.value = date;
    model.value = date;
    closeDropdown();
};

const handleDateClick = (day) => {
    if (day) {
        const newDate = new Date(
            currentMonth.value.getFullYear(),
            currentMonth.value.getMonth(),
            day
        );
        selectDate(newDate);
    }
};

const prevMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() - 1
    );
};

const nextMonth = () => {
    currentMonth.value = new Date(
        currentMonth.value.getFullYear(),
        currentMonth.value.getMonth() + 1
    );
};

const isSelectedDate = (day) => {
    if (!day) return false;
    const date = model.value || internalDate.value;
    if (!date) return false;
    const d = new Date(date);
    return (
        day === d.getDate() &&
        currentMonth.value.getMonth() === d.getMonth() &&
        currentMonth.value.getFullYear() === d.getFullYear()
    );
};

const isToday = (day) => {
    if (!day) return false;
    const today = new Date();
    return (
        day === today.getDate() &&
        currentMonth.value.getMonth() === today.getMonth() &&
        currentMonth.value.getFullYear() === today.getFullYear()
    );
};

const handleClickOutside = (event) => {
    if (isOpen.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

watch(() => props.defaultDate, () => {
    if (!model.value && props.defaultDate) {
        initializeDate();
    }
}, { immediate: true });

onMounted(() => {
    initializeDate();
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div :class="className">
        <div class="mb-2">
            <label v-if="label" :for="name" class="block text-sm font-medium text-gray-900 mb-2"
                :class="{ 'after:content-[\'*\'] after:ml-0.5 after:text-red-500': required }">
                {{ label }}
            </label>

            <div class="relative" ref="dropdownRef">
                <div class="relative">
                    <input :id="name" :name="name" type="text"
                        class="block w-full rounded-md border-0 p-2 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 cursor-pointer"
                        :class="{
                            'ring-red-500 focus:ring-red-600': errorMessage,
                            'bg-gray-50 cursor-not-allowed text-gray-500': disabled
                        }" :value="displayValue" :placeholder="required ? `${placeholder} *` : placeholder"
                        :disabled="disabled" @click="toggleDropdown" readonly />
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Dropdown -->
                <div v-if="isOpen"
                    class="absolute z-50 mt-2 w-full max-w-sm bg-white rounded-lg shadow-lg border border-gray-200 p-4">
                    <!-- Quick Options -->
                    <div class="grid grid-cols-2 gap-2 mb-4 pb-4 border-b border-gray-200">
                        <button v-for="option in quickOptions" :key="option.label" type="button"
                            class="px-3 py-2 text-sm font-medium text-primary bg-white border-2 border-primary/50 rounded-md hover:bg-primary hover:text-white transition-colors"
                            @click.stop="handleQuickSelect(option)">
                            {{ option.label }}
                        </button>
                    </div>

                    <!-- Calendar Header -->
                    <div class="flex items-center justify-between mb-4">
                        <button type="button" class="p-1.5 rounded-md hover:bg-gray-100 transition-colors"
                            @click.stop="prevMonth">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ monthNames[currentMonth.getMonth()] }} {{ currentMonth.getFullYear() }}
                        </span>
                        <button type="button" class="p-1.5 rounded-md hover:bg-gray-100 transition-colors"
                            @click.stop="nextMonth">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                    </div>

                    <!-- Weekdays -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div v-for="day in ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']" :key="day"
                            class="text-center text-xs font-semibold text-gray-500 py-2">
                            {{ day }}
                        </div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-1">
                        <button v-for="(day, index) in calendarDays" :key="index" type="button"
                            class="aspect-square flex items-center justify-center rounded-md text-sm font-medium transition-colors"
                            :class="{
                                'invisible': !day,
                                'bg-primary text-white': isSelectedDate(day),
                                'bg-indigo-100 text-primary': isToday(day) && !isSelectedDate(day),
                                'hover:bg-gray-100 text-gray-700': day && !isSelectedDate(day) && !isToday(day)
                            }" @click.stop="handleDateClick(day)" :disabled="!day">
                            {{ day || '' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Error or Helper Text -->
            <p v-if="errorMessage" class="mt-2 text-sm text-red-600">
                {{ errorMessage }}
            </p>
            <p v-else-if="helperText" class="mt-2 text-sm text-gray-500">
                {{ helperText }}
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Add any component-specific styles here */
</style>