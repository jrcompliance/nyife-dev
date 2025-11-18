<!-- <template>
    <AppLayout>
        <div
            class="bg-white md:bg-inherit pt-10 px-4 md:pt-8 md:p-8 rounded-[5px] text-[#000] h-full md:overflow-y-auto">
            <div class="flex justify-between">
                <div>
                    <h2 class="text-xl mb-1">{{ $t('Team') }}</h2>
                    <p class="mb-6 flex items-center text-sm leading-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="M12 11v5m0 5a9 9 0 1 1 0-18a9 9 0 0 1 0 18Zm.05-13v.1h-.1V8h.1Z" />
                        </svg>
                        <span class="ml-1 mt-1">{{ $t('Add edit and delete accounts in your team') }}</span>
                    </p>
                </div>
                <div>
                    <button @click="openModal()"
                        class="rounded-md bg-primary px-3 py-2 text-sm text-white shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">{{
                            $t('Invite user') }}</button>
                </div>
            </div>
            <TeamTable :rows="props.rows" @edit="openModal" />
        </div>

        <Modal :label="label" :isOpen=isOpenFormModal>
            <div class="mt-5 grid grid-cols-1 gap-x-6 gap-y-4">
                <form @submit.prevent="submitForm()" class="grid gap-x-6 gap-y-4 sm:grid-cols-6">
                    <FormInput v-model="form.email" :error="form.errors.email" :name="$t('Email')" :type="'email'"
                        :class="'sm:col-span-6'" :disabled="formMethod === 'put' ? true : false" />
                    <FormSelect v-model="form.role" :error="form.errors.role" :options="roleOptions" :name="$t('Role')"
                        :class="'sm:col-span-6'" :placeholder="$t('Select Role')" />
                    <div class="mt-4 flex">
                        <button type="button" @click.self="isOpenFormModal = false"
                            class="inline-flex justify-center rounded-md border border-transparent bg-slate-50 px-4 py-2 text-sm text-slate-500 hover:bg-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 mr-4">{{
                                $t('Cancel') }}</button>
                        <button
                            :class="['inline-flex justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2', { 'opacity-50': form.processing }]"
                            :disabled="form.processing">
                            <svg v-if="form.processing" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M12 2A10 10 0 1 0 22 12A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8A8 8 0 0 1 12 20Z"
                                    opacity=".5" />
                                <path fill="currentColor" d="M20 12h2A10 10 0 0 0 12 2V4A8 8 0 0 1 20 12Z">
                                    <animateTransform attributeName="transform" dur="1s" from="0 12 12"
                                        repeatCount="indefinite" to="360 12 12" type="rotate" />
                                </path>
                            </svg>
                            <span v-else>{{ $t('Save') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>


<script setup>
import AppLayout from "./../Layout/App.vue";
import { ref } from 'vue';
import { useForm } from "@inertiajs/vue3";
import TeamTable from '@/Components/Tables/TeamTable.vue';
import Modal from '@/Components/Modal.vue';
import FormInput from '@/Components/FormInput.vue';
import FormSelect from '@/Components/FormSelect.vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({ rows: Object, filters: Object });
const isOpenFormModal = ref(false);
const formUrl = ref('/team/invite');
const formMethod = ref('post');
const label = ref('Invite user');

const form = useForm({
    email: null,
    role: null,
});

const roleOptions = [
    { value: 'manager', label: trans('Manager') },
    { value: 'agent', label: trans('Agent') },
]

const openModal = (key, formData = {}) => {
    label.value = trans('Invite user');
    formUrl.value = '/team/invite';
    formMethod.value = 'post';

    if (key) {
        label.value = trans('Update user');
        formUrl.value = '/team/' + key.id;
        formMethod.value = 'put';
        form.email = key.email;
        form.role = key.role
        isOpenFormModal.value = true;
    } else {
        form.email = null;
        form.role = null;
        isOpenFormModal.value = true;
    }
}

const submitForm = () => {
    if (formMethod.value === 'post') {
        form.post(formUrl.value, {
            onFinish: () => {
                isOpenFormModal.value = false;
            }
        });
    } else {
        form.put(formUrl.value, {
            onFinish: () => {
                isOpenFormModal.value = false;
            }
        });
    }
}
</script> -->



<!-- ========================================= NEW UI CODE ====================================== -->


<template>
    <AppLayout>
        <div class="min-h-screen bg-white pt-10 px-4 md:pt-8 md:p-8">
            <!-- Hero Header Section -->
            <div class="mb-8">
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <!-- Left Section -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#ff5100] to-[#ff7a3d] flex items-center justify-center shadow-lg shadow-[#ff5100]/25">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-white">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <h2
                                class="text-3xl font-bold bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 bg-clip-text text-transparent">
                                {{ $t('Team') }}
                            </h2>
                        </div>
                        <div class="flex items-start gap-2 text-gray-600">
                            <p class="text-sm md:text-base font-medium leading-relaxed">
                                {{ $t('Add edit and delete accounts in your team') }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Section - Action Button -->
                    <div>
                        <button @click="openModal()"
                            class="group relative inline-flex items-center gap-3 px-6 py-3.5 bg-gradient-to-r from-primary via-orange-600 to-red-600 text-white text-sm font-bold rounded-2xl shadow-lg transform transition-all duration-300 overflow-hidden">
                            <div
                                class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300">
                            </div>
                            <div class="relative w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <line x1="19" y1="8" x2="19" y2="14" />
                                    <line x1="22" y1="11" x2="16" y2="11" />
                                </svg>
                            </div>
                            <span class="relative">{{ $t('Invite user') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Team Table Component -->
            <TeamTable :rows="props.rows" @edit="openModal" />
        </div>

        <!-- Modal Component -->
        <Modal :label="label" :isOpen="isOpenFormModal">
            <div class="mt-5">
                <div class="space-y-5">
                    <!-- Email Input -->
                    <FormInput v-model="form.email" :error="form.errors.email" :name="$t('Email')" :type="'email'"
                        :class="'w-full'" :disabled="formMethod === 'put' ? true : false" />

                    <!-- Role Select -->
                    <FormSelect v-model="form.role" :error="form.errors.role" :options="roleOptions" :name="$t('Role')"
                        :class="'w-full'" :placeholder="$t('Select Role')" />

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 pt-4">
                        <button type="button" @click.self="isOpenFormModal = false"
                            class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-300 transform hover:scale-105">
                            {{ $t('Cancel') }}
                        </button>
                        <button type="button" @click="submitForm" :class="[
                            'flex-1 px-6 py-3 bg-primary text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2',
                            { 'opacity-50 cursor-not-allowed': form.processing }
                        ]" :disabled="form.processing">
                            <svg v-if="form.processing" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" class="animate-spin">
                                <path fill="currentColor"
                                    d="M12 2A10 10 0 1 0 22 12A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8A8 8 0 0 1 12 20Z"
                                    opacity=".5" />
                                <path fill="currentColor" d="M20 12h2A10 10 0 0 0 12 2V4A8 8 0 0 1 20 12Z">
                                    <animateTransform attributeName="transform" dur="1s" from="0 12 12"
                                        repeatCount="indefinite" to="360 12 12" type="rotate" />
                                </path>
                            </svg>
                            <span>{{ form.processing ? $t('Saving...') : $t('Save') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>


<script setup>
import AppLayout from "./../Layout/App.vue";
import { ref } from 'vue';
import { useForm } from "@inertiajs/vue3";
import TeamTable from '@/Components/Tables/TeamTable.vue';
import Modal from '@/Components/Modal.vue';
import FormInput from '@/Components/FormInput.vue';
import FormSelect from '@/Components/FormSelect.vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({ rows: Object, filters: Object });
const isOpenFormModal = ref(false);
const formUrl = ref('/team/invite');
const formMethod = ref('post');
const label = ref('Invite user');

const form = useForm({
    email: null,
    role: null,
});

const roleOptions = [
    { value: 'manager', label: trans('Manager') },
    { value: 'agent', label: trans('Agent') },
]

const openModal = (key, formData = {}) => {
    label.value = trans('Invite user');
    formUrl.value = '/team/invite';
    formMethod.value = 'post';

    if (key) {
        label.value = trans('Update user');
        formUrl.value = '/team/' + key.id;
        formMethod.value = 'put';
        form.email = key.email;
        form.role = key.role
        isOpenFormModal.value = true;
    } else {
        form.email = null;
        form.role = null;
        isOpenFormModal.value = true;
    }
}

const submitForm = () => {
    if (formMethod.value === 'post') {
        form.post(formUrl.value, {
            onFinish: () => {
                isOpenFormModal.value = false;
            }
        });
    } else {
        form.put(formUrl.value, {
            onFinish: () => {
                isOpenFormModal.value = false;
            }
        });
    }
}
</script>