<template>
    <div class="">
        <div class="space-y-6 mb-12">
            <div class="relative flex justify-between gap-4">
                <!-- Search Bar -->
                <div class="relative group w-full max-w-screen-sm">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-[#ff5100] to-[#ff7733] rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-300">
                    </div>
                    <div
                        class="relative bg-white flex items-center shadow-lg h-14 rounded-2xl transition-all duration-300 border-2 border-orange-100">
                        <span class="pl-5 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                class="text-[#ff5100]">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m15 15l6 6m-11-4a7 7 0 1 1 0-14a7 7 0 0 1 0 14Z" />
                            </svg>
                        </span>
                        <input @input="search" v-model="params.search" type="text"
                            class="outline-none px-4 w-full bg-transparent text-gray-700 placeholder-gray-400 font-medium"
                            :placeholder="$t('Search quotations, proformas, and payment receipts')">
                        <button v-if="isSearching === false && params.search" @click="clearSearch" type="button"
                            class="pr-4 text-gray-400 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10s10-4.5 10-10S17.5 2 12 2zm3.7 12.3c.4.4.4 1 0 1.4c-.4.4-1 .4-1.4 0L12 13.4l-2.3 2.3c-.4.4-1 .4-1.4 0c-.4-.4-.4-1 0-1.4l2.3-2.3l-2.3-2.3c-.4-.4-.4-1 0-1.4c.4-.4 1-.4 1.4 0l2.3 2.3l2.3-2.3c.4-.4 1-.4 1.4 0c.4.4.4 1 0 1.4L13.4 12l2.3 2.3z" />
                            </svg>
                        </button>
                        <span v-if="isSearching" class="pr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                class="text-[#ff5100]">
                                <circle cx="12" cy="3.5" r="1.5" fill="currentColor" opacity="0">
                                    <animateTransform attributeName="transform" calcMode="discrete" dur="2.4s"
                                        repeatCount="indefinite" type="rotate"
                                        values="0 12 12;90 12 12;180 12 12;270 12 12" />
                                    <animate attributeName="opacity" dur="0.6s" keyTimes="0;0.5;1"
                                        repeatCount="indefinite" values="1;1;0" />
                                </circle>
                                <circle cx="12" cy="3.5" r="1.5" fill="currentColor" opacity="0">
                                    <animateTransform attributeName="transform" begin="0.2s" calcMode="discrete"
                                        dur="2.4s" repeatCount="indefinite" type="rotate"
                                        values="30 12 12;120 12 12;210 12 12;300 12 12" />
                                    <animate attributeName="opacity" begin="0.2s" dur="0.6s" keyTimes="0;0.5;1"
                                        repeatCount="indefinite" values="1;1;0" />
                                </circle>
                                <circle cx="12" cy="3.5" r="1.5" fill="currentColor" opacity="0">
                                    <animateTransform attributeName="transform" begin="0.4s" calcMode="discrete"
                                        dur="2.4s" repeatCount="indefinite" type="rotate"
                                        values="60 12 12;150 12 12;240 12 12;330 12 12" />
                                    <animate attributeName="opacity" begin="0.4s" dur="0.6s" keyTimes="0;0.5;1"
                                        repeatCount="indefinite" values="1;1;0" />
                                </circle>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="ml-auto flex items-center gap-4 flex-wrap">
                    <button @click="fetchInvoices"
                        class="bg-primary text-white font-semibold px-4 md:px-6 py-2.5 rounded-xl transition-all duration-300 hover:scale-[1.02] shadow-lg flex items-center gap-2">
                        <RefreshCcw :class="loading ? 'animate-spin' : ''" />
                    </button>
                    <QuotationInvoiceGenerator :refresh="refresh" @update:refresh="refresh = $event" />
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-3xl shadow-md border-2 border-primary/10 p-4">
                <!-- Table -->
                <div class="">
                    <table class="w-full">
                        <!-- Table Header -->
                        <thead class="border-b border-primary/10">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <span>{{ $t('Q/PI No.') }}</span>
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <span>{{ $t('Company') }}</span>
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <span>{{ $t('Contact Person') }}</span>
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <span>{{ $t('Date') }}</span>
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <span>{{ $t('Amount') }}</span>
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <span>{{ $t('Status') }}</span>
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    <span>{{ $t('Actions') }}</span>
                                </th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="divide-y divide-primary/10">
                            <tr v-if="loading === false || invoiceData?.invoices?.length > 0"
                                v-for="(item) in invoiceData?.invoices" :key="item.id"
                                class="hover:bg-orange-50/30 transition-all duration-200 group">

                                <!-- Quotation Number -->
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ item.quotation_number }}
                                    </div>
                                    <div v-if="item.payment_id" class="text-xs text-gray-500 mt-1">
                                        {{ item.payment_id }}
                                    </div>
                                    <div v-else="item.proforma_number" class="text-xs text-gray-500 mt-1">
                                        {{ item.proforma_number }}
                                    </div>
                                </td>

                                <!-- Company -->
                                <td class="px-6 py-5">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ item.company_name }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ item.email }}
                                    </div>
                                </td>

                                <!-- Contact Person -->
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ item.contact_person }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ item.phone }}
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ item.proforma_date || item.quotation_date }}
                                    </div>
                                    <div v-if="item.paid_at" class="text-xs text-gray-500 mt-1">
                                        Paid at: {{ item.paid_at.split('T')[0] }}
                                    </div>
                                    <div v-else-if="item.proforma_valid_until_date" class="text-xs text-gray-500 mt-1">
                                        Valid: {{ item.proforma_valid_until_date }}
                                    </div>
                                    <div v-else class="text-xs text-gray-500 mt-1">
                                        Valid: {{ item.quotation_valid_until_date }}
                                    </div>
                                </td>

                                <!-- Amount -->
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-base font-bold text-gray-900">
                                        ₹{{ formatAmount(item.total) }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ item.platform_charge_type }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span :class="getStatusBadgeClass(item)"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium w-fit">
                                            {{ getStatusText(item) }}
                                        </span>
                                        <div v-if="item.payment_status === 'expired'"
                                            class="text-xs text-gray-500 mt-1 ml-1">
                                            Payment link expired
                                        </div>
                                        <div v-if="item.payment_status === 'paid'"
                                            class="text-xs text-gray-500 mt-1 ml-1">
                                            via {{ item.payment_method }}
                                        </div>

                                        <div v-else class="text-xs text-gray-500 mt-1 ml-1">
                                            {{ item.payment_status }}
                                        </div>

                                    </div>
                                </td>

                                <!-- Actions - Custom Dropdown -->
                                <td class="px-6 py-5 whitespace-nowrap text-right">
                                    <div class="relative inline-block">
                                        <button @click="toggleDropdown(item.id)"
                                            class="inline-flex justify-center rounded-md text-sm font-medium text-black hover:bg-[#F6F7F9] hover:rounded-full p-2 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M12 16a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2m0-6a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2m0-6a2 2 0 0 1 2 2a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2Z" />
                                            </svg>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <Transition enter-active-class="transition ease-out duration-100"
                                            enter-from-class="transform opacity-0 scale-95"
                                            enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75"
                                            leave-from-class="transform opacity-100 scale-100"
                                            leave-to-class="transform opacity-0 scale-95">
                                            <div v-if="openDropdownId === item.id"
                                                class="absolute right-0 mt-2 w-64 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden">
                                                <div class="py-1">
                                                    <button @click="openInvoiceModal(item); closeDropdown()"
                                                        class="text-teal-600 w-full text-left px-4 py-3 text-sm transition-colors flex items-center gap-3 hover:bg-gray-50">
                                                        <Eye size="18" class="flex-shrink-0" />
                                                        <span class="font-medium truncate">{{ $t("View details")
                                                        }}</span>
                                                    </button>

                                                    <button v-for="action in getAvailableActions(item)"
                                                        :key="action.key"
                                                        @click="handleAction(action.key, item); closeDropdown()"
                                                        class="w-full text-left px-4 py-3 text-sm transition-colors flex items-center gap-3 hover:bg-gray-50"
                                                        :class="action.colorClass">
                                                        <component :is="action.icon" :size="18" class="flex-shrink-0" />
                                                        <span class="font-medium truncate">{{ $t(action.label) }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </Transition>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="invoiceData?.invoices?.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div
                                            class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                                viewBox="0 0 24 24" class="text-gray-400">
                                                <path fill="currentColor"
                                                    d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z" />
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                {{ $t('No records found') }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                {{ $t('Your invoices and receipts will appear here') }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="loading === true">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div
                                            class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                                viewBox="0 0 24 24" class="text-gray-400">
                                                <path fill="currentColor"
                                                    d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z" />
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                {{ $t('Loading...') }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                {{ $t('Please wait while we fetch your invoices and receipts') }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="invoiceData?.invoices?.length > 10" class="flex items-center justify-start gap-2">
                    <p>{{ invoiceData?.totalInvoices }} {{ " " }}Items</p>

                    <div class="flex items-center gap-3">
                        <!-- First Page Button -->
                        <button @click="goToFirstPage" :disabled="invoiceData.currentPage === 1"
                            class="flex items-center justify-center w-12 h-12 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            aria-label="First page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-600">
                                <polyline points="11 17 6 12 11 7"></polyline>
                                <polyline points="18 17 13 12 18 7"></polyline>
                            </svg>
                        </button>

                        <!-- Previous Page Button -->
                        <button @click="goToPreviousPage" :disabled="invoiceData.currentPage === 1"
                            class="flex items-center justify-center w-12 h-12 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            aria-label="Previous page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-600">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>

                        <!-- Page Indicator -->
                        <div
                            class="flex items-center justify-center min-w-[120px] h-12 px-6 bg-white border border-gray-200 rounded-lg">
                            <span class="text-gray-700 font-medium text-base">
                                {{ invoiceData.currentPage }} of {{ invoiceData.totalPages }}
                            </span>
                        </div>

                        <!-- Next Page Button -->
                        <button @click="goToNextPage" :disabled="invoiceData.currentPage === invoiceData.totalPages"
                            class="flex items-center justify-center w-12 h-12 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            aria-label="Next page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-600">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>

                        <!-- Last Page Button -->
                        <button @click="goToLastPage" :disabled="invoiceData.currentPage === invoiceData.totalPages"
                            class="flex items-center justify-center w-12 h-12 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            aria-label="Last page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-gray-600">
                                <polyline points="13 17 18 12 13 7"></polyline>
                                <polyline points="6 17 11 12 6 7"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Date Picker Modal -->
        <DatePickerModal :isOpen="isDateModalOpen" @close="closeDateModal" @submit="handleDateSubmit"
            v-model="selectedDate" label="Select Proforma Validity" :defaultDate="selectedDate" :required="true"
            helperText="Pick a date from the calendar" placeholder="Select date" :closeBtn="true" :showHeader="true" />
        <!-- Share Options Modal -->
        <div v-if="showShareOptions" class="modal-overlay" @click.self="closeShareModal">
            <div class="share-modal">
                <div class="share-header">
                    <h2 class="share-title">Share PDF</h2>
                    <button @click="closeShareModal" class="close-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <div class="share-body">
                    <p class="share-subtitle">Choose how you want to share this pdf</p>

                    <div class="share-options">
                        <!-- WhatsApp Share -->
                        <button @click="shareOnWhatsApp" :disabled="isSharing.whatsapp" class="share-option whatsapp">
                            <div class="share-icon-wrapper whatsapp-bg">
                                <svg v-if="!isSharing.whatsapp" width="28" height="28" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                                    </path>
                                </svg>
                                <svg v-else class="animate-spin" width="28" height="28" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="2" x2="12" y2="6"></line>
                                    <line x1="12" y1="18" x2="12" y2="22"></line>
                                    <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                    <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                    <line x1="2" y1="12" x2="6" y2="12"></line>
                                    <line x1="18" y1="12" x2="22" y2="12"></line>
                                    <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                    <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                </svg>
                            </div>
                            <div class="share-content">
                                <h3>Share on WhatsApp</h3>
                                <p>{{ isSharing.whatsapp ? 'Sharing...' : 'Send pdf via WhatsApp' }}</p>
                            </div>
                        </button>

                        <!-- Email Share -->
                        <button @click="shareViaEmail" :disabled="isSharing.email || !currentPDF.email"
                            class="share-option email">
                            <div class="share-icon-wrapper email-bg">
                                <svg v-if="!isSharing.email" width="28" height="28" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                    </path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <svg v-else class="animate-spin" width="28" height="28" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="2" x2="12" y2="6"></line>
                                    <line x1="12" y1="18" x2="12" y2="22"></line>
                                    <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                    <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                    <line x1="2" y1="12" x2="6" y2="12"></line>
                                    <line x1="18" y1="12" x2="22" y2="12"></line>
                                    <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                    <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                </svg>
                            </div>
                            <div class="share-content">
                                <h3>Share via Email</h3>
                                <p v-if="!currentPDF.email" class="text-red-500">Email not provided</p>
                                <p v-else-if="isSharing.email">Sending...</p>
                                <p v-else>Send to {{ currentPDF.email }}</p>
                            </div>
                        </button>

                        <!-- Download Again -->
                        <button @click="downloadPDF" :disabled="isSharing.downloading" class="share-option download">
                            <div class="share-icon-wrapper download-bg">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                            </div>
                            <div class="share-content">
                                <h3 v-if="isSharing.downloading">Downloading...</h3>
                                <h3 v-else>Download</h3>
                                <p v-if="isSharing.downloading">Please wait...</p>
                                <p v-else>Save copy of the PDF</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- PDF Proforma Invoice Template (Hidden) -->
        <div v-if="generateCurrentProformaPDF" ref="pdfTemplate" class="pdf-template">
            <div class="pdf-content relative pb-8">
                <div class="pdf-header">
                    <div class="company-info">
                        <img src="../../../images/nyifeBrand.svg" alt="nyife-logo" class="h-16 aspect-auto"></img>
                        <h2>Complia Services Ltd</h2>
                        <p>nyife.chat | info@nyife.chat | +91 11 430 22 315 | GST No: 07AALCC1963C1ZT</p>
                        <p>Plot no.9, Third Floor, Paschim Vihar Extn.</p>
                        <p>Delhi-110063, India</p>
                    </div>
                    <div class="invoice-info">
                        <h3>PROFORMA INVOICE</h3>
                        <p><strong>Invoice #:</strong> {{ generateCurrentProformaPDF.proforma_number }}</p>
                        <p><strong>Date:</strong> {{ generateCurrentProformaPDF.proforma_date }}</p>
                        <p><strong>Due Date:</strong> {{ generateCurrentProformaPDF.proforma_valid_until_date }}</p>
                    </div>
                </div>

                <div class="client-info">
                    <h4>Bill To:</h4>
                    <p><strong>{{ generateCurrentProformaPDF.contact_person || 'N/A' }}</strong></p>
                    <p><strong>Company:</strong> {{ generateCurrentProformaPDF.company_name || 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ generateCurrentProformaPDF.phone || 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ generateCurrentProformaPDF.email || 'N/A' }}</p>
                    <p><strong>Address:</strong> {{ generateCurrentProformaPDF.address || 'N/A' }}</p>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Description</th>
                            <th style="width: 150px; text-align: right;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in getVisibleItems(generateCurrentProformaPDF, generateCurrentProformaPDF.additional_fee)"
                            :key="index">
                            <td>{{ index + 1 }}</td>
                            <td>{{ item.description }}</td>
                            <td style="text-align: right;">{{ formatCurrency(item.amount) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary-section">
                    <div class="payment-qr">
                        <div class="qr-container">
                            <div class="qr-header">
                                <h4>Scan to Pay</h4>
                                <p>Quick & Secure Payment</p>
                            </div>
                            <div class="qr-code-wrapper">
                                <img :src="generateCurrentProformaPDF.qrCode" alt="Payment QR Code" class="qr-code">
                            </div>
                        </div>
                    </div>

                    <div class="summary-table-wrapper">
                        <table class="summary-table">
                            <tr>
                                <td>SUBTOTAL:</td>
                                <td>₹{{ generateCurrentProformaPDF.sub_total }}</td>
                            </tr>
                            <tr v-if="generateCurrentProformaPDF.discount > 0">
                                <td>DISCOUNT ({{ generateCurrentProformaPDF.discount }}%):</td>
                                <td class="discount-amount">-₹{{ generateCurrentProformaPDF.discount_amount }}</td>
                            </tr>
                            <tr v-if="generateCurrentProformaPDF.discount > 0">
                                <td>AMOUNT AFTER DISCOUNT:</td>
                                <td>₹{{ generateCurrentProformaPDF.amount_after_discount }}</td>
                            </tr>
                            <tr>
                                <td>GST 18%:</td>
                                <td>₹{{ generateCurrentProformaPDF.GST_amount }}</td>
                            </tr>
                            <tr class="total-row">
                                <td><strong>TOTAL AMOUNT DUE:</strong></td>
                                <td><strong>₹{{ generateCurrentProformaPDF.total }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="terms">
                    <h4>Terms & Conditions</h4>
                    <div class="terms-grid">
                        <div class="term-section">
                            <h5>Payment Terms</h5>
                            <p>Full payment is due within the specified due date. Late payments may incur additional
                                charges.</p>
                        </div>
                        <div class="term-section">
                            <h5>Invoice Validity</h5>
                            <p>This proforma validity is mentioned in the top of the proforma. Services will commence
                                upon payment confirmation.</p>
                        </div>
                        <div class="term-section">
                            <h5>Tax Compliance</h5>
                            <p>All prices include applicable GST @18%. Tax invoice will be issued upon payment receipt.
                            </p>
                        </div>
                        <div class="term-section">
                            <h5>Cancellation Policy</h5>
                            <p>Cancellations must be notified 48 hours in advance. Refunds subject to our refund policy
                                terms.</p>
                        </div>
                    </div>
                </div>

                <div class="signature">
                    <p>For any queries regarding this invoice, please contact us at info@nyife.chat</p>
                    <p class="signature-line">____________________</p>
                    <p class="name">Abhishek Anand</p>
                    <p class="title">Business Manager</p>
                    <p class="company-stamp">Complia Services Ltd</p>
                </div>

                <p class="absolute text-nowrap bottom-4 left-[50%] -translate-x-[50%] text-xs text-black/50">
                    This is an auto-generated proforma invoice. Please make payment to receive official tax invoice.
                </p>
            </div>
        </div>
        <!-- PDF Payment Receipt Template (Hidden) -->
        <div v-if="generateCurrentPaymentReceiptPDF" ref="pdfTemplatePaymentReceipt"
            class="pdf-template-payment-receipt">
            <div class="receipt-container">
                <div class="receipt-header">
                    <div class="success-icon">
                        <Check />
                    </div>
                    <h1 class="receipt-title">Payment Receipt</h1>
                    <p class="receipt-subtitle">Your payment has been received successfully</p>
                </div>

                <div class="receipt-body">
                    <div class="company-info pb-4">
                        <img src="../../../images/nyifeBrand.svg" alt="nyife-logo" class="h-16 aspect-auto"></img>
                        <h2>Complia Services Ltd</h2>
                        <p>nyife.chat | info@nyife.chat | +91 11 430 22 315 | GST No: 07AALCC1963C1ZT</p>
                        <p>Plot no.9, Third Floor, Paschim Vihar Extn.</p>
                        <p>Delhi-110063, India</p>
                    </div>
                    <div class="proforma-reference">
                        <p><strong>Payment Receipt Against Proforma Invoice:</strong>
                            {{ generateCurrentPaymentReceiptPDF?.proforma_number }}</p>
                    </div>

                    <div class="section">
                        <h2 class="section-title">Payment Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Payment ID</span>
                                <span class="info-value">{{ generateCurrentPaymentReceiptPDF?.payment_id }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Date</span>
                                <span class="info-value">{{ formatDateTimeIST(generateCurrentPaymentReceiptPDF?.paid_at)
                                    }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Method</span>
                                <span class="info-value">{{
                                    generateCurrentPaymentReceiptPDF?.payment_method?.toUpperCase() }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Status</span>
                                <span class="info-value">Paid</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Amount Paid</span>
                                <span class="info-value">₹{{
                                    generateCurrentPaymentReceiptPDF?.payment_metadata?.amount_paid
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h2 class="section-title">Customer Information</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Company Name</span>
                                <span class="info-value">{{ generateCurrentPaymentReceiptPDF?.company_name || `Not
                                    provided` }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Contact Person</span>
                                <span class="info-value">{{ generateCurrentPaymentReceiptPDF?.contact_person || `Not
                                    provided` }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email Address</span>
                                <span class="info-value">{{ generateCurrentPaymentReceiptPDF?.email || `Not provided`
                                }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone Number</span>
                                <span class="info-value">{{ generateCurrentPaymentReceiptPDF?.phone || `Not provided`
                                }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Address</span>
                                <span class="info-value">{{ generateCurrentPaymentReceiptPDF?.address || `Not provided`
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <div class="footer-note">
                        This is a computer-generated receipt and does not require a physical signature.<br>
                        For any queries, please contact us at info@nyife.chat
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Details Modal -->
        <InvoiceDetailsModal :isOpen="isInvoiceModalOpen" @close="closeInvoiceModal" :invoiceData="invoiceModalData"
            :closeBtn="true" :showHeader="true" />
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import debounce from 'lodash/debounce';
import { FileText, FileCheck, Receipt, Plus, RefreshCcw, Check, Eye } from 'lucide-vue-next';
import QuotationInvoiceGenerator from '../QuotationInvoiceGenerator.vue';
import { toast } from 'vue3-toastify';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import axios from 'axios';
import QRCode from "qrcode";
import DatePickerModal from '../DatePickerModal.vue';
import InvoiceDetailsModal from '../InvoiceDetailsModal.vue';

const base_url = import.meta.env.VITE_BACKEND_API_URL;
const whatsapp_token = import.meta.env.VITE_WA_TOKEN;


const invoiceData = ref([]);
const loading = ref(false);

const params = ref({
    search: '',
    page: 1,
    limit: 10,
});

const isSharing = ref({
    whatsapp: false,
    email: false,
    downloading: false
});

const refresh = ref(false);

const pdfTemplate = ref(null);
const pdfTemplatePaymentReceipt = ref(null);
const isSearching = ref(false);
const showShareOptions = ref(false);
const openDropdownId = ref(null);
const currentPDF = ref(null);
const generateCurrentProformaPDF = ref(null);
const generateCurrentPaymentReceiptPDF = ref(null);

const isDateModalOpen = ref(false);
const selectedDate = ref(
    new Date(new Date().setDate(new Date().getDate() + 7))
);

const tempProformaData = ref(null);


const isInvoiceModalOpen = ref(false);

const invoiceModalData = ref({});

function openInvoiceModal(item) {
    isInvoiceModalOpen.value = true;
    invoiceModalData.value = item;
}

function closeInvoiceModal() {
    isInvoiceModalOpen.value = false;
    setTimeout(() => {
        invoiceModalData.value = {}
    }, 1000)
}

function openDateModal(item) {
    isDateModalOpen.value = true;
    tempProformaData.value = item;
}

function closeDateModal() {
    isDateModalOpen.value = false;
    tempProformaData.value = null;
}

function handleDateSubmit(date) {
    selectedDate.value = date;
    generateProforma(tempProformaData.value);
}

// Action configuration - Config-driven approach
const actionConfig = {
    shareQuotation: {
        key: 'shareQuotation',
        label: 'Share Quotation',
        icon: FileText,
        colorClass: 'text-blue-600',
        condition: () => true,
    },
    generateProforma: {
        key: 'generateProforma',
        label: 'Generate & Share Proforma',
        icon: Plus,
        colorClass: 'text-green-600',
        condition: (item) => !item.proforma_invoice,
    },
    shareProforma: {
        key: 'shareProforma',
        label: 'Share Proforma',
        icon: FileCheck,
        colorClass: 'text-purple-600',
        condition: (item) => item.proforma_invoice,
    },
    generateReceipt: {
        key: 'generateReceipt',
        label: 'Generate & Share Payment Receipt',
        icon: Plus,
        colorClass: 'text-orange-600',
        condition: (item) => item.payment_receipt && !item.payment_invoice_pdf_url,
    },
    shareReceipt: {
        key: 'shareReceipt',
        label: 'Share Payment Receipt',
        icon: Receipt,
        colorClass: 'text-orange-600',
        condition: (item) => item.payment_receipt && item.payment_invoice_pdf_url,
    }
};

const openShareModal = () => {
    showShareOptions.value = true;
};

const closeShareModal = () => {
    showShareOptions.value = false;
    currentPDF.value = null;
    generateCurrentProformaPDF.value = null;
    generateCurrentPaymentReceiptPDF.value = null;
};

// Dropdown methods
const toggleDropdown = (id) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};

const closeDropdown = () => {
    openDropdownId.value = null;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    const dropdowns = document.querySelectorAll('.relative.inline-block');
    let clickedInside = false;

    dropdowns.forEach(dropdown => {
        if (dropdown.contains(event.target)) {
            clickedInside = true;
        }
    });

    if (!clickedInside) {
        closeDropdown();
    }
};

async function fetchInvoices() {
    loading.value = true;
    try {
        const res = await axios.get(`${base_url}/invoices`, {
            params: params.value,
        });

        invoiceData.value = res.data?.data || [];
    } catch (err) {
        toast.error(err.message || 'Error fetching invoices');
    } finally {
        loading.value = false;
        isSearching.value = false;
    }
}

// initial load
onMounted(() => {
    fetchInvoices();
});

watch(refresh, () => {
    fetchInvoices();
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Get available actions based on item state
const getAvailableActions = (item) => {
    return Object.values(actionConfig).filter(action => action.condition(item));
};

// Handle action clicks
const handleAction = (actionKey, item) => {
    switch (actionKey) {
        case 'shareQuotation':
            shareQuotation(item);
            break;
        case 'generateProforma':
            openDateModal(item);
            // generateProforma(item);
            break;
        case 'shareProforma':
            shareProforma(item);
            break;
        case 'generateReceipt':
            generateReceipt(item);
            break;
        case 'shareReceipt':
            shareReceipt(item);
            break;
    }
};

const formatCurrency = (value) => {
    const num = parseFloat(value) || 0;
    return num.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getVisibleItems = (Data, additionalData) => {
    const items = [];

    if (parseFloat(Data.platform_charge) > 0) {
        items.push({
            description: `Platform Charge (${Data.platform_charge_type})`,
            amount: parseFloat(Data.platform_charge)
        });
    }

    if (parseFloat(Data.wallet_recharge) > 0) {
        items.push({
            description: 'Wallet Recharge',
            amount: parseFloat(Data.wallet_recharge)
        });
    }

    if (parseFloat(Data.setup_fee) > 0) {
        items.push({
            description: 'Setup Fee',
            amount: parseFloat(Data.setup_fee)
        });
    }

    if (parseFloat(Data.customization_fee) > 0) {
        items.push({
            description: 'Customization Fee',
            amount: parseFloat(Data.customization_fee)
        });
    }

    additionalData.forEach(item => {
        if (item.description && parseFloat(item.amount) > 0) {
            items.push({
                description: item.description,
                amount: parseFloat(item.amount)
            });
        }
    });

    return items;
};

const generatePDFBlob = async () => {


    await new Promise(resolve => setTimeout(resolve, 200));

    const element = pdfTemplate.value;

    // Show element temporarily for rendering
    element.style.position = 'fixed';
    element.style.left = '0';
    element.style.top = '0';
    element.style.zIndex = '-1';
    element.style.display = 'block';
    element.style.width = '210mm';
    element.style.minHeight = '297mm';

    // High-quality canvas rendering
    const canvas = await html2canvas(element, {
        scale: 4,
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff',
        width: 793.7,
        windowWidth: 793.7,
        onclone: (clonedDoc) => {
            const clonedElement = clonedDoc.querySelector('.pdf-template');
            if (clonedElement) {
                clonedElement.style.display = 'block';
                clonedElement.style.width = '210mm';
            }
        }
    });

    // Hide element after rendering
    element.style.display = 'none';
    element.style.position = 'absolute';
    element.style.left = '-9999px';
    element.style.zIndex = '';
    element.style.width = '';
    element.style.minHeight = '';

    // Create PDF with high quality
    const imgData = canvas.toDataURL('image/png', 1.0);

    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4',
        compress: true
    });

    const pdfWidth = 210;
    const pdfHeight = 297;
    const imgWidth = pdfWidth;
    const imgHeight = (canvas.height * pdfWidth) / canvas.width;

    let heightLeft = imgHeight;
    let position = 0;

    // Add first page
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
    heightLeft -= pdfHeight;

    // Add additional pages if content overflows
    while (heightLeft > 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
        heightLeft -= pdfHeight;
    }

    // Generate filename
    const fileName = `Proforma_${generateCurrentProformaPDF.value.proforma_number.replace('/', '_')}_${generateCurrentProformaPDF.value.company_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;

    // Convert PDF to Blob
    const pdfBlob = pdf.output('blob');
    return { blob: pdfBlob, pdf, fileName };
};

const generatePaymentReceiptPDFBlob = async () => {


    await new Promise(resolve => setTimeout(resolve, 200));

    const element = pdfTemplatePaymentReceipt.value;

    // Show element temporarily for rendering
    element.style.position = 'fixed';
    element.style.left = '0';
    element.style.top = '0';
    element.style.zIndex = '-1';
    element.style.display = 'block';
    element.style.width = '210mm';
    element.style.minHeight = '297mm';

    // High-quality canvas rendering
    const canvas = await html2canvas(element, {
        scale: 4,
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff',
        width: 793.7,
        windowWidth: 793.7,
        onclone: (clonedDoc) => {
            const clonedElement = clonedDoc.querySelector('.pdf-template-payment-receipt');
            if (clonedElement) {
                clonedElement.style.display = 'block';
                clonedElement.style.width = '210mm';
            }
        }
    });

    // Hide element after rendering
    element.style.display = 'none';
    element.style.position = 'absolute';
    element.style.left = '-9999px';
    element.style.zIndex = '';
    element.style.width = '';
    element.style.minHeight = '';

    // Create PDF with high quality
    const imgData = canvas.toDataURL('image/png', 1.0);

    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4',
        compress: true
    });

    const pdfWidth = 210;
    const pdfHeight = 297;
    const imgWidth = pdfWidth;
    const imgHeight = (canvas.height * pdfWidth) / canvas.width;

    let heightLeft = imgHeight;
    let position = 0;

    // Add first page
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
    heightLeft -= pdfHeight;

    // Add additional pages if content overflows
    while (heightLeft > 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
        heightLeft -= pdfHeight;
    }

    // Generate filename
    const fileName = `PaymentReceipt_${generateCurrentPaymentReceiptPDF.value.payment_id.replace('/', '_')}_${generateCurrentPaymentReceiptPDF.value.company_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;

    // Convert PDF to Blob
    const pdfBlob = pdf.output('blob');
    return { blob: pdfBlob, pdf, fileName };
};

const downloadPDF = async () => {
    if (!currentPDF.value?.pdfDownloadUrl) {
        toast.error('No PDF available to download');
        return;
    }

    try {
        // Fetch the PDF as a blob
        const response = await fetch(currentPDF.value.pdfDownloadUrl);
        const blob = await response.blob();

        // Create a blob URL
        const blobUrl = window.URL.createObjectURL(blob);

        // Create and trigger download
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = currentPDF.value.pdfName || "invoice.pdf";
        document.body.appendChild(link);
        link.click();

        // Cleanup
        document.body.removeChild(link);
        window.URL.revokeObjectURL(blobUrl);

        toast.success('PDF downloaded successfully!');

    } catch (error) {
        console.error('Download error:', error);
        toast.error('Error downloading PDF. Please try again.');
    }
};

const shareOnWhatsApp = async () => {
    if (!currentPDF.value.pdf) {
        toast.error('No PDF available to share');
        return;
    }

    if (!currentPDF.value.phone) {
        toast.error('Phone number is required');
        return;
    }

    isSharing.value.whatsapp = true;

    try {
        const response = await fetch("https://wa.nyife.chat/api/send/template", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${whatsapp_token}`,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                phone: currentPDF.value.phone,
                template: {
                    name: currentPDF.value.templateName,
                    language: { code: "en" },
                    components: [
                        {
                            type: "header",
                            parameters: [
                                {
                                    type: "document",
                                    document: {
                                        link: currentPDF.value.pdf,
                                        filename: currentPDF.value.pdfName || "invoice.pdf"
                                    }
                                }
                            ]
                        }
                    ]
                }
            })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            console.error("WhatsApp API Error:", errorData);
            throw new Error("Failed to share on WhatsApp");
        }

        toast.success("Invoice shared on WhatsApp!");



    } catch (error) {
        console.error("Error sharing on WhatsApp:", error);
        toast.error("Error sharing on WhatsApp. Please try again.");
    } finally {
        isSharing.value.whatsapp = false;
    }
};


const shareViaEmail = async () => {
    if (!currentPDF.value.pdf) {
        toast.error('No PDF available to share');
        return;
    }

    if (!currentPDF.value.email) {
        toast.error('Email address is required');
        return;
    }

    isSharing.value.email = true;

    try {

        const payload = {
            customer_name: currentPDF.value.contactPerson,
            invoice_type: currentPDF.value.templateType,
            invoice_number: currentPDF.value.invoiceNumber,
            invoice_url: currentPDF.value.pdfDownloadUrl,
            email: currentPDF.value.email
        }

        const response = await axios.post(`${base_url}/email/share-invoice`, payload);

        if (!response?.data?.success) {
            throw new Error('Failed to send email');
        }

        toast.success(`Quotation sent to ${currentPDF.value.email} successfully!`);

    } catch (error) {
        toast.error('Error sending email. Please try again.');
    } finally {
        isSharing.value.email = false;
    }
};

// Action handlers
const shareQuotation = (item) => {
    currentPDF.value = {
        contactPerson: item.contact_person,
        companyName: item.company_name,
        phone: item.phone,
        email: item.email,
        pdf: item.quotation_invoice_pdf_url,
        invoiceNumber: item.quotation_number,
        pdfDownloadUrl: `${item.quotation_invoice_pdf_url}/download`,
        templateName: "quotation_invoice",
        templateType: "Quotation",
        pdfName: `Quotation_${item.quotation_number.replace('/', '_')}_${item.company_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`
    };
    openShareModal();
};

const generateProforma = async (item) => {
    const tId = toast.loading('Generating proforma invoice...');
    try {

        const payload = {
            proforma_valid_until_date: selectedDate.value.toISOString()
        }

        const res = await axios.put(`${base_url}/invoices/generate-proforma/${item.id}`, payload);

        if (!res?.data?.success) {
            throw new Error(res?.data?.message || 'Failed to generate proforma invoice');
        }

        const qrCode = ref('');

        const paymentUrl = res?.data?.data.payment_url?.payment_link_short_url;

        qrCode.value = await QRCode.toDataURL(paymentUrl, { width: 180 });

        generateCurrentProformaPDF.value = {
            ...res?.data?.data,
            qrCode: qrCode.value
        };


        const { blob, fileName } = await generatePDFBlob();

        const formData = new FormData();
        formData.append("pdf_data", blob, fileName);
        formData.append("pdf_type", "proforma");
        formData.append("id", res?.data?.data.id);

        const uploadRes = await axios.post(`${base_url}/uploads`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });

        if (!uploadRes?.data?.success) {
            throw new Error(uploadRes?.data?.message || "Failed to upload proforma PDF");
        }

        closeDateModal();
        toast.success('Proforma invoice generated successfully!');

        currentPDF.value = {
            contactPerson: generateCurrentProformaPDF.value.contact_person,
            companyName: generateCurrentProformaPDF.value.company_name,
            phone: generateCurrentProformaPDF.value.phone,
            email: generateCurrentProformaPDF.value.email,
            invoiceNumber: generateCurrentProformaPDF.value.proforma_number,
            templateType: "Proforma",
            templateName: "proforma_invoice",
            pdf: uploadRes.data.data.file.url,
            pdfDownloadUrl: uploadRes.data.data.file.downloadUrl,
            pdfName: uploadRes.data.data.file.filename
        };


        // Show share options modal after a short delay
        setTimeout(() => {
            openShareModal();
        }, 300);

    } catch (error) {
        toast.error(error.message || 'Error generating proforma invoice. Please try again.');

    } finally {
        refresh.value = !refresh.value;
        toast.remove(tId);
    }
};

const shareProforma = (item) => {
    currentPDF.value = {
        contactPerson: item.contact_person,
        companyName: item.company_name,
        phone: item.phone,
        email: item.email,
        templateName: "proforma_invoice",
        templateType: "Proforma",
        invoiceNumber: item.proforma_number,
        pdf: item.proforma_invoice_pdf_url,
        pdfDownloadUrl: `${item.proforma_invoice_pdf_url}/download`,
        pdfName: `Proforma_${item.proforma_number.replace('/', '_')}_${item.company_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`
    };
    openShareModal();
};

const generateReceipt = async (item) => {
    const tId = toast.loading('Generating  payment receipt...');
    try {

        generateCurrentPaymentReceiptPDF.value = item;

        const { blob, fileName } = await generatePaymentReceiptPDFBlob();

        const formData = new FormData();
        formData.append("pdf_data", blob, fileName);
        formData.append("pdf_type", "payment");
        formData.append("id", item.id);

        const uploadRes = await axios.post(`${base_url}/uploads`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });

        if (!uploadRes?.data?.success) {
            throw new Error(uploadRes?.data?.message || "Failed to upload payment receipt PDF");
        }

        toast.success('Payment receipt generated successfully!');

        currentPDF.value = {
            contactPerson: generateCurrentPaymentReceiptPDF.value.contact_person,
            companyName: generateCurrentPaymentReceiptPDF.value.company_name,
            phone: generateCurrentPaymentReceiptPDF.value.phone,
            email: generateCurrentPaymentReceiptPDF.value.email,
            invoiceNumber: generateCurrentPaymentReceiptPDF.value.payment_id,
            templateType: "Payment Receipt",
            templateName: "payment_receipt",
            pdf: uploadRes.data.data.file.url,
            pdfDownloadUrl: uploadRes.data.data.file.downloadUrl,
            pdfName: uploadRes.data.data.file.filename
        };



        // Show share options modal after a short delay
        setTimeout(() => {
            openShareModal();
        }, 300);

    } catch (error) {
        toast.error(error.message || 'Error generating payment receipt. Please try again.');

    } finally {
        refresh.value = !refresh.value;
        toast.remove(tId);
    }
};

const shareReceipt = (item) => {
    currentPDF.value = {
        contactPerson: item.contact_person,
        companyName: item.company_name,
        phone: item.phone,
        email: item.email,
        invoiceNumber: item.payment_id,
        templateType: "Payment Receipt",
        templateName: "payment_receipt",
        pdf: item.payment_invoice_pdf_url,
        pdfDownloadUrl: `${item.payment_invoice_pdf_url}/download`,
        pdfName: `PaymentReceipt_${item.payment_id.replace('/', '_')}_${item.company_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`
    };
    openShareModal();
};

// Status helpers
const getStatusText = (item) => {
    if (item.payment_receipt) return 'Paid';
    if (item.proforma_invoice) return 'Proforma Issued';
    return 'Quotation Issued';
};

const getStatusBadgeClass = (item) => {
    if (item.payment_receipt) {
        return 'bg-green-100 text-green-800 border border-green-200';
    }
    if (item.proforma_invoice) {
        return 'bg-purple-100 text-purple-800 border border-purple-200';
    }
    return 'bg-blue-100 text-blue-800 border border-blue-200';
};

// Utility functions
const clearSearch = () => {
    params.value.search = '';
    fetchInvoices();
};

const search = debounce(() => {
    isSearching.value = true;
    fetchInvoices();
}, 1000);


const goToFirstPage = () => {
    params.value.page = 1;
    fetchInvoices();
}

const goToPreviousPage = () => {
    if (invoiceData.value.currentPage > 1) {
        params.value.page--;
        fetchInvoices();
    }
}

const goToNextPage = () => {
    if (invoiceData.value.currentPage < invoiceData.value.totalPages) {
        params.value.page++;
        fetchInvoices();
    }
}

const goToLastPage = () => {
    params.value.page = invoiceData.value.totalPages;
    fetchInvoices();
}

const formatAmount = (amount) => {
    return new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

function formatDateTimeIST(isoString) {
    if (!isoString) return '';

    return new Intl.DateTimeFormat('en-IN', {
        timeZone: 'Asia/Kolkata',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }).format(new Date(isoString));
}


const emit = defineEmits(['update:modelValue', 'callback']);
</script>

<style scoped>
/* Modal Overlay */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

/* Proforma Invoice Generator Styles */
.pdf-template {
    position: absolute;
    left: -9999px;
    display: none;
    width: 793px;
    background-image: url("../../../images/nyife-bg.png");
    background-size: 70%;
    background-position: center;
    background-repeat: no-repeat;
    padding: 20px;
    font-family: Arial, sans-serif;
}

.pdf-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #333;
}

.company-info h2 {
    color: #ff5100;
    font-size: 24px;
    margin-bottom: 5px;
}

.company-info p {
    font-size: 11px;
    color: #666;
    margin: 2px 0;
}

.invoice-info {
    text-align: right;
    background: linear-gradient(135deg, #ff5100 0%, #ff7d47 100%);
    padding: 16px 20px;
    border-radius: 8px;
    color: white;
}

.invoice-info h3 {
    font-size: 20px;
    color: white;
    margin-bottom: 8px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.invoice-info p {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.95);
    margin: 4px 0;
}

.client-info {
    background: #f8f9fa;
    padding: 16px;
    margin-bottom: 25px;
    border-radius: 5px;
    border-left: 4px solid #ff5100;
}

.client-info h4 {
    font-size: 13px;
    color: #ff5100;
    margin-bottom: 8px;
    font-weight: 700;
}

.client-info p {
    font-size: 12px;
    color: #555;
    line-height: 1.6;
    margin: 2px 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

.items-table th {
    background: #ff5100;
    color: white;
    padding: 12px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
}

.items-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 12px;
    color: #333;
}

.items-table tr:last-child td {
    border-bottom: none;
}

.summary-section {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    align-items: flex-start;
}

.payment-qr {
    flex: 0 0 240px;
}

.qr-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #ff5100cb;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(255, 81, 0, 0.15);
}

.qr-header h4 {
    font-size: 14px;
    color: #ff5100;
    margin-bottom: 4px;
    font-weight: 700;
}

.qr-header p {
    font-size: 10px;
    color: #666;
    margin-bottom: 12px;
}

.qr-code-wrapper {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.qr-code {
    width: 180px;
    height: 180px;
    display: block;
    margin: 0 auto;
}

.summary-table-wrapper {
    flex: 1;
}

.summary-table {
    width: 100%;
    border-collapse: collapse;
}

.summary-table tr {
    border-bottom: 1px solid #e0e0e0;
}

.summary-table td {
    padding: 10px 12px;
    font-size: 13px;
}

.summary-table td:first-child {
    color: #666;
}

.summary-table td:last-child {
    text-align: right;
    font-weight: 600;
    color: #333;
}

.summary-table .discount-amount {
    color: #28a745;
}

.summary-table .total-row {
    background: linear-gradient(135deg, #ff5100 0%, #ff7d47 100%);
}

.summary-table .total-row td {
    padding: 14px 12px;
    font-size: 16px;
    border: none;
    color: white;
}

.terms {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #333;
}

.terms h4 {
    font-size: 14px;
    color: #333;
    margin-bottom: 12px;
}

.terms-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.term-section h5 {
    font-size: 11px;
    color: #ff5100;
    margin-bottom: 5px;
    font-weight: 700;
}

.term-section p {
    font-size: 10px;
    color: #555;
    line-height: 1.5;
    margin-bottom: 8px;
}

.signature {
    margin: 100px 0 60px 0;
    text-align: right;
}

.signature p {
    font-size: 12px;
    color: #333;
    margin: 5px 0;
}

.signature .signature-line {
    margin-top: 30px;
    margin-bottom: 5px;
}

.signature .name {
    font-weight: 700;
    font-size: 14px;
    color: #ff5100;
}

.signature .title {
    font-size: 11px;
    color: #666;
}

.signature .company-stamp {
    font-size: 10px;
    color: #999;
    font-style: italic;
    margin-top: 2px;
}

/* Payment Receipt Generator Styles */

.pdf-template-payment-receipt {
    position: absolute;
    left: -9999px;
    display: none;
    width: 793px;
    padding: 20px;
    font-family: Arial, sans-serif;
}

.receipt-container {
    margin: 0 auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.receipt-header {
    background: linear-gradient(135deg, #ff5100 0%, #ff7d47 100%);
    padding: 20px;
    text-align: center;
    color: white;
}

.success-icon {
    width: 52px;
    height: 52px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 24px;
    color: #ff5100;
}

.receipt-title {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
}

.receipt-subtitle {
    font-size: 15px;
    opacity: 0.95;
}

.receipt-body {
    padding-top: 16px;
    padding-bottom: 0;
    background-image: url("../../../images/nyife-bg.png");
    background-size: 70%;
    background-position: center;
    background-repeat: no-repeat;
}

.section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding-bottom: 8px;
    border-bottom: 2px solid #ff5100;
}

.info-grid {
    display: grid;
    gap: 16px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 4px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
}

.info-value {
    font-size: 14px;
    color: #212529;
    font-weight: 600;
    text-align: right;
    max-width: 60%;
}

.proforma-reference {
    background: #fff3e0;
    border-left: 4px solid #ff9800;
    padding: 16px;
    border-radius: 4px;
    margin-bottom: 32px;
}

.proforma-reference p {
    font-size: 13px;
    color: #5d4037;
    line-height: 1.6;
    margin: 0;
}

.proforma-reference strong {
    color: #ff5100;
}

.footer {
    background: #f8f9fa;
    padding: 16px 40px;
    text-align: center;
    border-top: 1px solid #e9ecef;
}

.company-logo {
    font-size: 22px;
    font-weight: 700;
    color: #ff5100;
    margin-bottom: 16px;
}

.company-details {
    font-size: 12px;
    color: #6c757d;
    line-height: 1.8;
    margin-bottom: 20px;
}

.footer-note {
    font-size: 11px;
    color: #737980;
}

@media print {
    body {
        background: white;
        padding: 0;
    }

    .receipt-container {
        box-shadow: none;
    }
}

@media (max-width: 768px) {
    .receipt-body {
        padding: 24px;
    }

    .amount-value {
        font-size: 36px;
    }

    .info-value {
        max-width: 55%;
    }
}

/* Share Modal Styles */
.share-modal {
    background: white;
    border-radius: 1rem;
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.share-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.share-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.share-body {
    padding: 1.5rem;
}

.share-subtitle {
    color: #6b7280;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.share-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.share-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: left;
}

.share-option:hover:not(:disabled) {
    border-color: #ff5100;
    background: #fff5f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 81, 0, 0.1);
}

.share-option:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.share-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.whatsapp-bg {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
}

.email-bg {
    background: linear-gradient(135deg, #EA4335 0%, #C5221F 100%);
    color: white;
}

.download-bg {
    background: linear-gradient(135deg, #4285F4 0%, #1967D2 100%);
    color: white;
}

.share-content {
    flex: 1;
}

.share-content h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.share-content p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.btn-outline {
    background: white;
    color: #374151;
    border: 2px solid #e5e7eb;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.w-full {
    width: 100%;
}

.mt-6 {
    margin-top: 1.5rem;
}
</style>