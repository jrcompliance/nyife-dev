<template>
    <div class="">
        <!-- Trigger Button -->
        <div class="flex justify-center">
            <button @click="openModal"
                class="bg-primary text-white font-semibold px-4 md:px-6 py-2.5 rounded-xl transition-all duration-300 hover:scale-[1.02] shadow-lg flex items-center gap-2">
                <CirclePlus />
                Create Quotation Invoice
            </button>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
            <div class="modal-container">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h1 class="modal-title">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" class="text-primary"
                            stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        Create & Share Quotation Invoice
                    </h1>
                    <button @click="closeModal" class="close-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <p class="subtitle">Fill in the details below to generate your quotation</p>

                    <!-- Client Information -->
                    <div class="section-title">Client Information</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Company Name <span class="required">*</span></label>
                            <input type="text" v-model="formData.companyName" placeholder="Enter company name"
                                :class="{ 'error-input': errors.companyName }">
                            <span v-if="errors.companyName" class="error-text">{{ errors.companyName }}</span>
                        </div>
                        <div class="form-group">
                            <label>Contact Person <span class="required">*</span></label>
                            <input type="text" v-model="formData.contactPerson" placeholder="Enter contact person name"
                                :class="{ 'error-input': errors.contactPerson }">
                            <span v-if="errors.contactPerson" class="error-text">{{ errors.contactPerson }}</span>
                        </div>
                    </div>

                    <div class="form-grid">

                        <div class="form-group">
                            <FormInput v-model="formData.email" :name="$t('Email')" :label="$t('Email')"
                                :error="errors.email" type="email" />
                        </div>

                        <div class="form-group py-2">
                            <FormPhoneInput v-model="formData.phone" :name="$t('Phone')" :label="$t('Phone')"
                                :error="errors.phone" />
                        </div>

                        <!-- a vuejs date picker with a default date which was set from props it should have quick date option such as 7 days, 1 month, 2 months and 3 months -->
                        <div class="form-group py-2">
                            <FormDateInput v-model="formData.quotation_valid_until_date"
                                :defaultDate="formData.quotation_valid_until_date" name="quotation_valid_until_date"
                                label="Quotation Vaildity Date" :required="true" helperText="Select Validity Date"
                                className="mb-4" />
                        </div>

                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea v-model="formData.address" placeholder="Enter complete address"
                            :class="{ 'error-input': errors.address }"></textarea>
                        <span v-if="errors.address" class="error-text">{{ errors.address }}</span>
                    </div>


                    <!-- Service Charges -->
                    <div class="section-title">Service Charges</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Platform Charge Type</label>
                            <select v-model="formData.selectedPlanId" @change="updatePlatformCharge">
                                <option value="">Select Plan</option>
                                <option v-for="plan in activePlans" :key="plan.id" :value="plan.id">
                                    {{ plan.name }} - ₹{{ formatCurrency(plan.price) }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Platform Charge Amount (₹)</label>
                            <input type="number" v-model="formData.platformCharge" placeholder="0" min="0" step="0.01"
                                readonly style="background-color: #f9fafb; cursor: not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Discount (%)</label>
                            <input type="number" v-model="formData.discount" placeholder="0" min="0" max="100"
                                step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Wallet Recharge (₹)</label>
                            <input type="number" v-model="formData.walletRecharge" placeholder="0" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Setup Fee (₹)</label>
                            <input type="number" v-model="formData.setupFee" placeholder="0" min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Customization Fee (₹)</label>
                            <input type="number" v-model="formData.customizationFee" placeholder="0" min="0"
                                step="0.01">
                        </div>
                    </div>

                    <!-- Additional Items -->
                    <div class="additional-items">
                        <div class="additional-header">
                            <div class="section-title no-border">Additional Items (Optional)</div>
                            <button class="btn btn-secondary" @click="addAdditionalItem">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <div v-for="(item, index) in additionalItems" :key="index" class="item-row">
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" v-model="item.description" placeholder="Item description">
                            </div>
                            <div class="form-group">
                                <label>Amount (₹)</label>
                                <input type="number" v-model="item.amount" placeholder="0" min="0" step="0.01">
                            </div>
                            <button class="btn btn-danger" @click="removeAdditionalItem(index)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path
                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="summary-box">
                        <h3 class="summary-title">Quotation Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>₹{{ formatCurrency(calculateSubtotal()) }}</span>
                        </div>
                        <div class="summary-row" v-if="formData.discount > 0">
                            <span>Discount ({{ formData.discount }}%):</span>
                            <span class="text-red-600">-₹{{ formatCurrency(calculateDiscount()) }}</span>
                        </div>
                        <div class="summary-row" v-if="formData.discount > 0">
                            <span>Amount After Discount:</span>
                            <span>₹{{ formatCurrency(calculateAmountAfterDiscount()) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>GST (18%):</span>
                            <span>₹{{ formatCurrency(calculateGST()) }}</span>
                        </div>
                        <div class="summary-row total">
                            <span>TOTAL:</span>
                            <span>₹{{ formatCurrency(calculateTotal()) }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-group">
                        <button class="btn btn-outline" @click="closeModal">
                            Cancel
                        </button>
                        <button class="btn btn-primary" @click="generatePDF" :disabled="isGenerating">
                            <FileText v-if="!isGenerating" />
                            <span v-if="isGenerating">Generating...</span>
                            <span v-else>Generate PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Options Modal -->
        <div v-if="showShareOptions" class="modal-overlay" @click.self="closeShareModal">
            <div class="share-modal">
                <div class="share-header">
                    <h2 class="share-title">Share Quotation</h2>
                    <button @click="closeShareModal" class="close-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <div class="share-body">
                    <p class="share-subtitle">Choose how you want to share this quotation</p>

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
                                <p>{{ isSharing.whatsapp ? 'Sharing...' : 'Send quotation via WhatsApp' }}</p>
                            </div>
                        </button>

                        <!-- Email Share -->
                        <button @click="shareViaEmail" :disabled="isSharing.email || !currentPdfData.email"
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
                                <p v-if="!currentPdfData.email" class="text-red-500">Email not provided</p>
                                <p v-else-if="isSharing.email">Sending...</p>
                                <p v-else>Send to {{ currentPdfData.email }}</p>
                            </div>
                        </button>

                        <!-- Download Again -->
                        <button @click="downloadPDF" class="share-option download">
                            <div class="share-icon-wrapper download-bg">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                            </div>
                            <div class="share-content">
                                <h3>Download</h3>
                                <p>Save copy of the PDF</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDF Template (Hidden) -->
        <div v-if="currentPdfData" ref="pdfTemplate" class="pdf-template">
            <div class="pdf-content relative pb-8">
                <div class="pdf-header">
                    <div class="company-info">
                        <img src="../../images/nyifeBrand.svg" alt="nyife-logo" width='181' height="60"></img>
                        <h2>Complia Services Ltd</h2>
                        <p>nyife.chat | info@nyife.chat | +91 11 430 22 315 | GST No: 07AALCC1963C1ZT</p>
                        <p>Plot no.9, Third Floor, Paschim Vihar Extn.</p>
                        <p>Delhi-110063, India</p>
                    </div>
                    <div class="quotation-info">
                        <h3>QUOTATION #{{ currentPdfData?.quotation_number }}</h3>
                        <p><strong>Date:</strong> {{ currentPdfData?.quotation_date }}</p>
                        <p><strong>Valid Until:</strong> {{ currentPdfData?.quotation_valid_until_date }}</p>
                    </div>
                </div>

                <div class="client-info">
                    <h4>Kind Attention: {{ currentPdfData?.contact_person || 'N/A' }}</h4>
                    <p><strong>Company:</strong> {{ currentPdfData?.company_name || 'N/A' }}</p>
                    <p><strong>Phone Number:</strong> {{ currentPdfData?.phone || 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ currentPdfData?.email || 'N/A' }}</p>
                    <p><strong>Address:</strong> {{ currentPdfData?.address || 'N/A' }}</p>
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
                        <tr v-for="(item, index) in getVisibleItems(currentPdfData, currentPdfData?.additional_fee)"
                            :key="index">
                            <td>{{ index + 1 }}</td>
                            <td>{{ item?.description }}</td>
                            <td style="text-align: right;">{{ formatCurrency(item?.amount) }}</td>
                        </tr>

                        <div v-if="((currentPdfData?.discount > 0 ? 2 : 4) - (getVisibleItems(currentPdfData, currentPdfData?.additional_fee)?.length || 0)) > 0"
                            v-for="index in (currentPdfData?.discount > 0 ? 2 : 4) - (getVisibleItems(currentPdfData, currentPdfData?.additional_fee)?.length || 0)"
                            :key="index" class="h-7 w-full">
                        </div>

                    </tbody>
                </table>

                <table class="summary-table">
                    <thead>
                        <tr>
                            <td>SUBTOTAL:</td>
                            <td>₹{{ currentPdfData?.sub_total }}</td>
                        </tr>
                        <tr v-if="currentPdfData?.discount > 0">
                            <td>DISCOUNT ({{ currentPdfData?.discount }}%):</td>
                            <td>-₹{{ currentPdfData?.discount_amount }}</td>
                        </tr>
                        <tr v-if="currentPdfData?.discount > 0">
                            <td>AMOUNT AFTER DISCOUNT:</td>
                            <td>₹{{ currentPdfData?.amount_after_discount }}</td>
                        </tr>
                        <tr>
                            <td>GST 18%:</td>
                            <td>₹{{ currentPdfData?.GST_amount }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>TOTAL:</td>
                            <td>₹{{ currentPdfData?.total }}</td>
                        </tr>
                    </thead>
                </table>

                <div class="thank-you-note">
                    <p>Thank you for considering nyife.chat for your communication needs. We appreciate the
                        opportunity to share our quotation and hope you find our platform innovative, reliable,
                        and competitively priced. We look forward to building a long-term partnership and
                        contributing to your business growth.</p>
                </div>

                <div class="terms">
                    <h4>Terms & Conditions</h4>
                    <div class="terms-grid">
                        <div class="term-section">
                            <h5>Prices & Taxes</h5>
                            <p>All prices mentioned in this quotation are exclusive of GST. GST @18% will be
                                applicable over and above the quoted prices.</p>
                        </div>

                        <div class="term-section">
                            <h5>Quotation Validity</h5>
                            <p>This quotation validity is mentioned in the top of the quotation. Prices and terms are
                                subject to review after the validity period.</p>
                        </div>

                        <div class="term-section">
                            <h5>Payment Terms</h5>
                            <p>All payments must be made in favour of "Complia Services Ltd." Payment shall be
                                made as per the agreed schedule. Services will commence upon receipt of payment.
                            </p>
                        </div>

                        <div class="term-section">
                            <h5>Scope of Support</h5>
                            <p>Basic setup and standard support are included. Any customization or additional
                                technical support beyond standard scope will be charged separately.</p>
                        </div>

                        <div class="term-section">
                            <h5>Price Revision Clause</h5>
                            <p>In case of any price changes by third-party platforms (Meta, Google, telecom
                                operators), such changes will be passed on to the customer at actual rates.</p>
                        </div>

                        <div class="term-section">
                            <h5>Service Activation & Delivery</h5>
                            <p>Service activation timelines may vary depending on platform approvals. We will make
                                best efforts to ensure timely activation and delivery of services.</p>
                        </div>
                    </div>
                </div>

                <div class="signature">
                    <p>If you have any questions concerning this quotation, please contact us at info@nyife.chat
                    </p>
                    <p class="signature-line">____________________</p>
                    <p class="name">Abhishek Anand</p>
                    <p class="title">Business Manager</p>
                </div>

                <!-- This is auto generated -->
                <p class="absolute text-nowrap bottom-4 left-[50%] -translate-x-[50%] text-xs text-black/50">
                    This is an auto-generated quotation invoice and does not require a signature.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { toast } from 'vue3-toastify';
import FormPhoneInput from './FormPhoneInput.vue';
import FormInput from './FormInput.vue';
import { CirclePlus, FileText } from 'lucide-vue-next';
import axios from 'axios';
import FormDateInput from './FormDateInput.vue';

const base_url = import.meta.env.VITE_BACKEND_API_URL;
const whatsapp_token = import.meta.env.VITE_WA_TOKEN;

const props = defineProps(['refresh']);
const emit = defineEmits(['update:refresh']);


const activePlans = ref([]);

const fetchSubscriptionPlans = async () => {
    try {
        const res = await axios.get(`${base_url}/subscription-plans`);
        if (!res?.data?.success) {
            throw new Error(res?.data?.message || "Failed to fetch subscription plans");
        }
        activePlans.value = res.data.data;
    } catch (error) {
        toast.error(error.message || 'Error fetching subscription plans. Please try again.');
    }
};

onMounted(() => {
    fetchSubscriptionPlans();
});

const isModalOpen = ref(false);
const isGenerating = ref(false);
const showShareOptions = ref(false);
const pdfTemplate = ref(null);
const errors = ref({});
const isSharing = ref({
    whatsapp: false,
    email: false
});

const formData = ref({
    companyName: '',
    contactPerson: '',
    phone: '',
    email: '',
    address: '',
    selectedPlanId: null,
    platformChargeType: '',
    platformCharge: 0,
    walletRecharge: 0,
    setupFee: 0,
    customizationFee: 0,
    discount: 0,
    quotation_valid_until_date: new Date(new Date().setMonth(new Date().getMonth() + 1))

});

const additionalItems = ref([]);
const currentPdfData = ref(null);

const openModal = () => {
    isModalOpen.value = true;
    errors.value = {};
};

const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};

const closeShareModal = () => {
    showShareOptions.value = false;
    resetForm();
};

const resetForm = () => {
    formData.value = {
        companyName: '',
        contactPerson: '',
        phone: '',
        email: '',
        address: '',
        selectedPlanId: null,
        platformChargeType: '',
        platformCharge: 0,
        walletRecharge: 0,
        setupFee: 0,
        customizationFee: 0,
        discount: 0,
        quotation_valid_until_date: new Date(new Date().setMonth(new Date().getMonth() + 1))
    };
    additionalItems.value = [];
    errors.value = {};
};

const updatePlatformCharge = () => {
    const selectedPlan = activePlans.value.find(plan => plan.id === formData.value.selectedPlanId);
    if (selectedPlan) {
        formData.value.platformCharge = parseFloat(selectedPlan.price);
        formData.value.platformChargeType = selectedPlan.name;
    } else {
        formData.value.platformCharge = '';
        formData.value.platformChargeType = '';
    }
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    if (!formData.value.companyName.trim()) {
        errors.value.companyName = 'Company name is required';
        isValid = false;
    }

    if (!formData.value.contactPerson.trim()) {
        errors.value.contactPerson = 'Contact person name is required';
        isValid = false;
    }

    if (!formData.value.phone.trim()) {
        errors.value.phone = 'Phone number is required';
        isValid = false;
    }

    if (calculateSubtotal() === 0) {
        toast.error('Please add at least one item with an amount greater than 0');
        isValid = false;
    }

    return isValid;
};

const addAdditionalItem = () => {
    additionalItems.value.push({ description: '', amount: 0 });
};

const removeAdditionalItem = (index) => {
    additionalItems.value.splice(index, 1);
};

const formatCurrency = (value) => {
    const num = parseFloat(value) || 0;
    return num.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const calculateSubtotal = () => {
    let total = 0;
    if (formData.value.platformCharge) total += parseFloat(formData.value.platformCharge) || 0;
    if (formData.value.walletRecharge) total += parseFloat(formData.value.walletRecharge) || 0;
    if (formData.value.setupFee) total += parseFloat(formData.value.setupFee) || 0;
    if (formData.value.customizationFee) total += parseFloat(formData.value.customizationFee) || 0;

    additionalItems.value.forEach(item => {
        if (item.amount) total += parseFloat(item.amount) || 0;
    });

    return total;
};

const calculateDiscount = () => {
    const discount = parseFloat(formData.value.discount) || 0;
    return calculateSubtotal() * (discount / 100);
};

const calculateAmountAfterDiscount = () => {
    return calculateSubtotal() - calculateDiscount();
};

const calculateGST = () => {
    return calculateAmountAfterDiscount() * 0.18;
};

const calculateTotal = () => {
    return calculateAmountAfterDiscount() + calculateGST();
};

const getVisibleItems = (Data, additionalData) => {
    const items = [];

    if (parseFloat(Data?.platform_charge) > 0) {
        items.push({
            description: `Platform Charge (${Data?.platform_charge_type})`,
            amount: parseFloat(Data?.platform_charge)
        });
    }

    if (parseFloat(Data?.wallet_recharge) > 0) {
        items.push({
            description: 'Wallet Recharge',
            amount: parseFloat(Data?.wallet_recharge)
        });
    }

    if (parseFloat(Data?.setup_fee) > 0) {
        items.push({
            description: 'Setup Fee',
            amount: parseFloat(Data?.setup_fee)
        });
    }

    if (parseFloat(Data?.customization_fee) > 0) {
        items.push({
            description: 'Customization Fee',
            amount: parseFloat(Data?.customization_fee)
        });
    }

    additionalData?.forEach(item => {
        if (item?.description && parseFloat(item?.amount) > 0) {
            items.push({
                description: item?.description,
                amount: parseFloat(item?.amount)
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
    const fileName = `Quotation_${currentPdfData.value.quotation_number.replace('/', '_')}_${currentPdfData.value.company_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;

    // Convert PDF to Blob
    const pdfBlob = pdf.output('blob');
    return { blob: pdfBlob, pdf, fileName };
};

const generatePDF = async () => {
    if (!validateForm()) {
        return;
    }

    isGenerating.value = true;

    try {

        const payload = {
            company_name: formData.value.companyName,
            contact_person: formData.value.contactPerson,
            phone: formData.value.phone,
            email: formData.value.email,
            address: formData.value.address,
            selected_plan_id: formData.value.selectedPlanId,
            platform_charge_type: formData.value.platformChargeType,
            platform_charge: formData.value.platformCharge,
            wallet_recharge: formData.value.walletRecharge,
            setup_fee: formData.value.setupFee,
            customization_fee: formData.value.customizationFee,
            additional_fee: additionalItems.value,
            discount: formData.value.discount,
            GST: 18,
            quotation_valid_until_date: formData.value.quotation_valid_until_date
        }

        const res = await axios.post(`${base_url}/invoices`, payload);


        if (!res?.data?.success) {
            throw new Error(res?.data?.message || "Failed to create invoice");
        }

        currentPdfData.value = res?.data?.data;

        try {
            const { blob, fileName } = await generatePDFBlob();

            const formData = new FormData();
            formData.append("pdf_data", blob, fileName);
            formData.append("pdf_type", "quotation");
            formData.append("id", res?.data?.data.id);

            const uploadRes = await axios.post(`${base_url}/uploads`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            });

            if (!uploadRes?.data?.success) {
                throw new Error(uploadRes?.data?.message || "Failed to upload quotation PDF");
            }

            toast.success('Quotation invoice generated successfully!');
            currentPdfData.value.quotation_invoice_pdf_url = uploadRes.data.data.file.url;
            currentPdfData.value.quotation_invoice_pdf_download_url = uploadRes.data.data.file.downloadUrl;
            currentPdfData.value.quotation_invoice_pdf_filename = uploadRes.data.data.file.filename;

            // Close the form modal
            isModalOpen.value = false;

            // Show share options modal after a short delay
            setTimeout(() => {
                showShareOptions.value = true;
            }, 300);

        } catch (error) {
            if (currentPdfData.value.id) {
                await axios.delete(`${base_url}/invoices/${currentPdfData.value.id}`);
            }
            toast.error(error.message || 'Error generating PDF. Please try again.');
        }


    } catch (error) {
        toast.error(error.message || 'Error generating quotation. Please try again.');
    } finally {
        isGenerating.value = false;
        emit('update:refresh', !props.refresh);
    }
};


const downloadPDF = async () => {
    if (!currentPdfData.value.quotation_invoice_pdf_download_url) {
        toast.error('No PDF available to download');
        return;
    }

    try {
        // Fetch the PDF as a blob
        const response = await fetch(currentPdfData.value.quotation_invoice_pdf_download_url);
        const blob = await response.blob();

        // Create a blob URL
        const blobUrl = window.URL.createObjectURL(blob);

        // Create and trigger download
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = currentPdfData.value.quotation_invoice_pdf_filename || "invoice.pdf";
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
    if (!currentPdfData.value.quotation_invoice_pdf_url) {
        toast.error('No PDF available to share');
        return;
    }

    if (!currentPdfData.value.phone) {
        toast.error('Phone number is required');
        return;
    }

    isSharing.value.whatsapp = true;


    try {
        const url = currentPdfData.value.quotation_invoice_pdf_url;
        const fileName = currentPdfData.value.quotation_invoice_pdf_filename;
        const response = await fetch("https://wa.nyife.chat/api/send/template", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${whatsapp_token}`,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                phone: currentPdfData.value.phone,
                template: {
                    name: "quotation_invoice",
                    language: { code: "en" },
                    components: [
                        {
                            type: "header",
                            parameters: [
                                {
                                    type: "document",
                                    document: {
                                        link: url,
                                        filename: fileName || "invoice.pdf"
                                    }
                                }
                            ]
                        }
                    ]
                }
            })
        });

        const data = await response.json().catch(() => null);

        if (data.data.success === false) {
            throw new Error(data.data.message || "Failed to share on WhatsApp");
        }

        toast.success("Quotation shared on WhatsApp!");

    } catch (error) {
        toast.error(error.message || 'Error sharing on WhatsApp. Please try again.');
    } finally {
        isSharing.value.whatsapp = false;
    }
};

const shareViaEmail = async () => {
    if (!currentPdfData.value.quotation_invoice_pdf_url) {
        toast.error('No PDF available to share');
        return;
    }

    if (!formData.value.email) {
        toast.error('Email address is required');
        return;
    }

    isSharing.value.email = true;

    try {

        const payload = {
            customer_name: currentPdfData.value.contact_person,
            invoice_type: 'Quotation',
            invoice_number: currentPdfData.value.quotation_number,
            invoice_url: currentPdfData.value.quotation_invoice_pdf_download_url,
            email: currentPdfData.value.email
        }

        const response = await axios.post(`${base_url}/email/share-invoice`, payload);

        if (!response?.data?.success) {
            throw new Error('Failed to send email');
        }

        toast.success(`Quotation sent to ${currentPdfData.value.email} successfully!`);

    } catch (error) {
        toast.error('Error sending email. Please try again.');
    } finally {
        isSharing.value.email = false;
    }
};

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

/* Modal Container */
.modal-container {
    background: white;
    border-radius: 1rem;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Modal Header */
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.close-btn {
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: background-color 0.2s;
    border: none;
    background: transparent;
    cursor: pointer;
}

.close-btn:hover {
    background-color: #f3f4f6;
}

/* Modal Body */
.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}

.subtitle {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

/* Section Title */
.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 1.5rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.section-title.no-border {
    border-bottom: none;
    margin-bottom: 0.5rem;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

/* Form Group */
.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.required {
    color: #ef4444;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: inherit;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #ff5100;
    box-shadow: 0 0 0 3px rgba(255, 81, 0, 0.1);
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
    border-color: #cbd5e0;
}

.error-input {
    border-color: #e53e3e !important;
}

.error-text {
    color: #e53e3e;
    font-size: 12px;
    margin-top: 4px;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin: 30px 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #ff5100;
}

.section-title.no-border {
    margin: 0;
    border: none;
    padding: 0;
}

.additional-items {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    margin-top: 20px;
}

.additional-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.item-row {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
    gap: 15px;
    margin-bottom: 15px;
    align-items: center;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-primary {
    background: #ff5100;
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #48bb78;
    color: white;
}

.btn-secondary:hover {
    background: #38a169;
}

.btn-danger {
    background: #f56565;
    color: white;
    padding: 8px 12px;
    margin-top: 1.5rem;
}

.btn-danger:hover {
    background: #e53e3e;
}

.btn-group {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    justify-content: center;
}

.summary-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}

.summary-title {
    margin-bottom: 15px;
    color: #333;
    font-size: 18px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 15px;
}

.summary-row.total {
    font-size: 20px;
    font-weight: 700;
    color: #ff5100;
    border-top: 2px solid #ddd;
    padding-top: 15px;
    margin-top: 10px;
}

/* PDF Template Styles */
.pdf-template {
    position: absolute;
    left: -9999px;
    display: none;
    width: 793px;
    background-image: url("../../images/nyife-bg.png");
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

.quotation-info {
    text-align: right;
}

.quotation-info h3 {
    font-size: 18px;
    color: #333;
    margin-bottom: 5px;
}

.quotation-info p {
    font-size: 11px;
    color: #666;
    margin: 3px 0;
}

.client-info {
    background: #f8f9fa;
    padding: 16px;
    margin-bottom: 25px;
    border-radius: 5px;
}

.client-info h4 {
    font-size: 13px;
    color: #333;
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

.summary-table {
    margin-left: auto;
    width: 300px;
    margin-bottom: 30px;
}

.summary-table tr {
    border-bottom: 1px solid #e0e0e0;
}

.summary-table td {
    padding: 10px;
    font-size: 13px;
}

.summary-table td:last-child {
    text-align: right;
    font-weight: 600;
}

.summary-table .total-row {
    background: #ff5100;
    color: white;
    font-size: 16px;
    font-weight: 700;
}

.thank-you-note {
    padding: 15px;
    background: #ff51002d;
    border-left: 4px solid #ff5100;
}

.thank-you-note p {
    margin: 0;
    color: #333;
    font-size: 11px;
    line-height: 1.6;
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
    margin: 40px 0;
    text-align: right;
}

.signature p {
    font-size: 12px;
    color: #333;
    margin: 5px 0;
}

.signature .signature-line {
    margin-top: 20px;
    margin-bottom: 5px;
}

.signature .name {
    font-weight: 700;
    font-size: 14px;
}

.signature .title {
    font-size: 11px;
    color: #666;
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