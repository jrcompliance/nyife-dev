<template>
    <div class="">
        <!-- Trigger Button -->
        <div class="flex justify-center">
            <button @click="openModal"
                class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold px-6 py-3 rounded-xl transition-all duration-300 hover:scale-105 shadow-lg flex items-center gap-2">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                Create Proforma Invoice
            </button>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
            <div class="modal-container">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h1 class="modal-title">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" class="text-blue-600">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        Create Proforma Invoice
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
                    <p class="subtitle">Fill in the details below to generate your proforma invoice</p>

                    <!-- Invoice Details -->
                    <div class="section-title">Invoice Details</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Invoice Number</label>
                            <input type="text" v-model="formData.invoiceNumber" readonly
                                style="background-color: #f9fafb; cursor: not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Payment Terms</label>
                            <select v-model="formData.paymentTerms" @change="updateDueDate">
                                <option v-for="term in paymentTermsOptions" :key="term" :value="term">{{ term }}
                                </option>
                            </select>
                        </div>
                    </div>

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
                            <label>Kind Attention To <span class="required">*</span></label>
                            <input type="text" v-model="formData.attentionTo" placeholder="Enter contact person name"
                                :class="{ 'error-input': errors.attentionTo }">
                            <span v-if="errors.attentionTo" class="error-text">{{ errors.attentionTo }}</span>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" v-model="formData.email" placeholder="Enter email address"
                                :class="{ 'error-input': errors.email }">
                            <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                        </div>
                        <div class="form-group">
                            <label>Phone <span class="required">*</span></label>
                            <input type="tel" v-model="formData.phone" placeholder="Enter phone number"
                                :class="{ 'error-input': errors.phone }">
                            <span v-if="errors.phone" class="error-text">{{ errors.phone }}</span>
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
                        <h3 class="summary-title">Invoice Summary</h3>
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
                            <svg v-if="!isGenerating" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
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
                    <h2 class="share-title">Share Proforma Invoice</h2>
                    <button @click="closeShareModal" class="close-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <div class="share-body">
                    <p class="share-subtitle">Choose how you want to share this proforma invoice</p>

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
                                <p>{{ isSharing.whatsapp ? 'Sharing...' : 'Send invoice via WhatsApp' }}</p>
                            </div>
                        </button>

                        <!-- Email Share -->
                        <button @click="shareViaEmail" :disabled="isSharing.email || !formData.email"
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
                                <p v-if="!formData.email" class="text-red-500">Email not provided</p>
                                <p v-else-if="isSharing.email">Sending...</p>
                                <p v-else>Send to {{ formData.email }}</p>
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
        <div ref="pdfTemplate" class="pdf-template">
            <div class="pdf-content">
                <!-- Header with Blue Accent -->
                <div class="pdf-header-proforma">
                    <div class="header-left">
                        <div class="logo-section">
                            <img src="../../images/nyifeBrand.svg" alt="nyife-logo" class="company-logo">
                        </div>
                        <div class="company-details">
                            <h2 class="company-name-proforma">Complia Services Ltd</h2>
                            <div class="contact-grid">
                                <p>📧 info@nyife.chat</p>
                                <p>🌐 nyife.chat</p>
                                <p>📞 +91 11 430 22 315</p>
                                <p>🏢 GST: 07AALCC1963C1ZT</p>
                            </div>
                            <p class="address-line">Plot no.9, Third Floor, Paschim Vihar Extn., Delhi-110063, India</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="invoice-badge">
                            <h1>PROFORMA INVOICE</h1>
                        </div>
                        <div class="invoice-meta">
                            <div class="meta-row">
                                <span class="meta-label">Invoice #:</span>
                                <span class="meta-value">{{ formData.invoiceNumber }}</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Date:</span>
                                <span class="meta-value">{{ currentDate }}</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Due Date:</span>
                                <span class="meta-value">{{ dueDate }}</span>
                            </div>
                            <div class="meta-row">
                                <span class="meta-label">Terms:</span>
                                <span class="meta-value">{{ formData.paymentTerms }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill To Section -->
                <div class="bill-to-section">
                    <div class="bill-to-label">BILL TO</div>
                    <div class="bill-to-content">
                        <h3>{{ formData.attentionTo || 'N/A' }}</h3>
                        <p class="company-name-bill">{{ formData.companyName || 'N/A' }}</p>
                        <p>{{ formData.address || 'N/A' }}</p>
                        <div class="contact-info">
                            <span>📧 {{ formData.email || 'N/A' }}</span>
                            <span>📞 {{ formData.phone || 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <table class="items-table-proforma">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Description</th>
                            <th style="width: 80px; text-align: center;">Qty</th>
                            <th style="width: 140px; text-align: right;">Unit Price (₹)</th>
                            <th style="width: 140px; text-align: right;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in getVisibleItems()" :key="index">
                            <td>{{ index + 1 }}</td>
                            <td>{{ item.description }}</td>
                            <td style="text-align: center;">1</td>
                            <td style="text-align: right;">{{ formatCurrency(item.amount) }}</td>
                            <td style="text-align: right;">{{ formatCurrency(item.amount) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Summary Section -->
                <div class="summary-section-proforma">
                    <div class="summary-content">
                        <div class="summary-row-pdf">
                            <span>Subtotal:</span>
                            <span>₹{{ formatCurrency(calculateSubtotal()) }}</span>
                        </div>
                        <div class="summary-row-pdf" v-if="formData.discount > 0">
                            <span>Discount ({{ formData.discount }}%):</span>
                            <span class="text-red">-₹{{ formatCurrency(calculateDiscount()) }}</span>
                        </div>
                        <div class="summary-row-pdf" v-if="formData.discount > 0">
                            <span>Amount After Discount:</span>
                            <span>₹{{ formatCurrency(calculateAmountAfterDiscount()) }}</span>
                        </div>
                        <div class="summary-row-pdf">
                            <span>GST (18%):</span>
                            <span>₹{{ formatCurrency(calculateGST()) }}</span>
                        </div>
                        <div class="summary-row-pdf total-row-pdf">
                            <span>TOTAL AMOUNT DUE:</span>
                            <span>₹{{ formatCurrency(calculateTotal()) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <div class="payment-instructions">
                    <h4>Payment Instructions</h4>
                    <div class="payment-grid">
                        <div>
                            <p class="payment-label">Bank Name:</p>
                            <p class="payment-value">HDFC Bank</p>
                        </div>
                        <div>
                            <p class="payment-label">Account Name:</p>
                            <p class="payment-value">Complia Services Ltd</p>
                        </div>
                        <div>
                            <p class="payment-label">Account Number:</p>
                            <p class="payment-value">XXXXXXXXXXXX1234</p>
                        </div>
                        <div>
                            <p class="payment-label">IFSC Code:</p>
                            <p class="payment-value">HDFC0001234</p>
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="terms-proforma">
                    <h4>Terms & Conditions</h4>
                    <ol class="terms-list">
                        <li>This is a proforma invoice and not a tax invoice. A tax invoice will be issued upon payment
                            confirmation.</li>
                        <li>All prices are exclusive of GST. GST @18% is applicable over and above quoted prices.</li>
                        <li>Payment must be made within the specified payment terms from the invoice date.</li>
                        <li>Prices are subject to change without notice after the validity period.</li>
                        <li>Services will commence upon receipt of payment and completion of necessary formalities.</li>
                        <li>Any customization beyond standard scope will be charged separately as agreed.</li>
                    </ol>
                </div>

                <!-- Footer -->
                <div class="footer-proforma">
                    <div class="footer-note">
                        <p>Thank you for your business! We appreciate your trust in nyife.chat.</p>
                        <p>For any queries, please contact us at info@nyife.chat or call +91 11 430 22 315</p>
                    </div>
                    <div class="signature-section">
                        <div class="signature-line"></div>
                        <p class="signature-name">Abhishek Anand</p>
                        <p class="signature-title">Business Manager</p>
                    </div>
                    <div class="auto-generated">
                        This is an auto-generated proforma invoice and does not require a signature.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { toast } from 'vue3-toastify';

const activePlans = [
    { id: 2, name: "Yearly", price: "11000.00", period: "yearly", status: "active" },
    { id: 1, name: "Monthly", price: "1000.00", period: "monthly", status: "active" }
].filter(plan => plan.status === 'active');

const paymentTermsOptions = ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Due on Receipt'];

const isModalOpen = ref(false);
const isGenerating = ref(false);
const showShareOptions = ref(false);
const generatedPdfBlob = ref(null);
const generatedFileName = ref('');
const pdfTemplate = ref(null);
const errors = ref({});
const isSharing = ref({ whatsapp: false, email: false });

const formData = ref({
    invoiceNumber: '',
    companyName: '',
    attentionTo: '',
    phone: '',
    email: '',
    address: '',
    selectedPlanId: '',
    platformCharge: '',
    walletRecharge: '',
    setupFee: '',
    customizationFee: '',
    discount: 0,
    paymentTerms: 'Net 30'
});

const additionalItems = ref([]);
const currentDate = ref('');
const dueDate = ref('');

const generateInvoiceNumber = () => {
    const prefix = 'PI';
    const timestamp = Date.now().toString().slice(-8);
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    formData.value.invoiceNumber = `${prefix}-${timestamp}-${random}`;
};

const getCurrentDate = () => {
    const today = new Date();
    return today.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getDueDate = (days) => {
    const today = new Date();
    today.setDate(today.getDate() + days);
    return today.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const updateDueDate = () => {
    const daysMap = {
        'Net 15': 15,
        'Net 30': 30,
        'Net 45': 45,
        'Net 60': 60,
        'Due on Receipt': 0
    };
    dueDate.value = getDueDate(daysMap[formData.value.paymentTerms] || 30);
};

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
        invoiceNumber: '',
        companyName: '',
        attentionTo: '',
        phone: '',
        email: '',
        address: '',
        selectedPlanId: '',
        platformCharge: '',
        walletRecharge: '',
        setupFee: '',
        customizationFee: '',
        discount: 0,
        paymentTerms: 'Net 30'
    };
    additionalItems.value = [];
    errors.value = {};
    generatedPdfBlob.value = null;
    generatedFileName.value = '';
    generateInvoiceNumber();
};

const updatePlatformCharge = () => {
    const selectedPlan = activePlans.find(plan => plan.id === formData.value.selectedPlanId);
    if (selectedPlan) {
        formData.value.platformCharge = parseFloat(selectedPlan.price);
    } else {
        formData.value.platformCharge = '';
    }
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    if (!formData.value.companyName.trim()) {
        errors.value.companyName = 'Company name is required';
        isValid = false;
    }

    if (!formData.value.attentionTo.trim()) {
        errors.value.attentionTo = 'Contact person name is required';
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
    additionalItems.value.push({ description: '', amount: '' });
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

const getVisibleItems = () => {
    const items = [];

    if (parseFloat(formData.value.platformCharge) > 0) {
        const plan = activePlans.find(p => p.id === formData.value.selectedPlanId);
        items.push({
            description: `Platform Charge (${plan?.name || ''})`,
            amount: parseFloat(formData.value.platformCharge)
        });
    }

    if (parseFloat(formData.value.walletRecharge) > 0) {
        items.push({
            description: 'Wallet Recharge',
            amount: parseFloat(formData.value.walletRecharge)
        });
    }

    if (parseFloat(formData.value.setupFee) > 0) {
        items.push({
            description: 'Setup Fee',
            amount: parseFloat(formData.value.setupFee)
        });
    }

    if (parseFloat(formData.value.customizationFee) > 0) {
        items.push({
            description: 'Customization Fee',
            amount: parseFloat(formData.value.customizationFee)
        });
    }

    additionalItems.value.forEach(item => {
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
    currentDate.value = getCurrentDate();
    updateDueDate();

    await new Promise(resolve => setTimeout(resolve, 200));

    const element = pdfTemplate.value;

    element.style.position = 'fixed';
    element.style.left = '0';
    element.style.top = '0';
    element.style.zIndex = '-1';
    element.style.display = 'block';
    element.style.width = '210mm';
    element.style.minHeight = '297mm';

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

    element.style.display = 'none';
    element.style.position = 'absolute';
    element.style.left = '-9999px';
    element.style.zIndex = '';
    element.style.width = '';
    element.style.minHeight = '';

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

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
    heightLeft -= pdfHeight;

    while (heightLeft > 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight, undefined, 'FAST');
        heightLeft -= pdfHeight;
    }

    const fileName = `Proforma_Invoice_${formData.value.invoiceNumber}.pdf`;
    generatedFileName.value = fileName;

    const pdfBlob = pdf.output('blob');
    return { blob: pdfBlob, pdf, fileName };
};

const generatePDF = async () => {
    if (!validateForm()) {
        return;
    }

    isGenerating.value = true;

    try {
        const { blob } = await generatePDFBlob();
        generatedPdfBlob.value = blob;
        toast.success('Proforma Invoice generated successfully!');
        isModalOpen.value = false;
        setTimeout(() => {
            showShareOptions.value = true;
        }, 300);
    } catch (error) {
        console.error('Error generating PDF:', error);
        toast.error('Error generating invoice. Please try again.');
    } finally {
        isGenerating.value = false;
    }
};

const downloadPDF = () => {
    if (!generatedPdfBlob.value) {
        toast.error('No PDF available to download');
        return;
    }

    try {
        const url = URL.createObjectURL(generatedPdfBlob.value);
        const link = document.createElement('a');
        link.href = url;
        link.download = generatedFileName.value || 'Proforma_Invoice.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        toast.success('PDF downloaded successfully!');
    } catch (error) {
        console.error('Error downloading PDF:', error);
        toast.error('Error downloading PDF. Please try again.');
    }
};

const shareOnWhatsApp = async () => {
    if (!generatedPdfBlob.value) {
        toast.error('No PDF available to share');
        return;
    }

    isSharing.value.whatsapp = true;

    try {
        const formDataToSend = new FormData();
        formDataToSend.append('pdf', generatedPdfBlob.value, generatedFileName.value);
        formDataToSend.append('companyName', formData.value.companyName);
        formDataToSend.append('attentionTo', formData.value.attentionTo);
        formDataToSend.append('phone', formData.value.phone);
        formDataToSend.append('email', formData.value.email || '');
        formDataToSend.append('totalAmount', calculateTotal().toString());
        formDataToSend.append('invoiceNumber', formData.value.invoiceNumber);

        const response = await fetch('/api/share/whatsapp', {
            method: 'POST',
            body: formDataToSend,
        });

        if (!response.ok) {
            throw new Error('Failed to share on WhatsApp');
        }

        toast.success('Invoice shared on WhatsApp successfully!');
    } catch (error) {
        console.error('Error sharing on WhatsApp:', error);
        toast.error('Error sharing on WhatsApp. Please try again.');
    } finally {
        isSharing.value.whatsapp = false;
    }
};

const shareViaEmail = async () => {
    if (!generatedPdfBlob.value) {
        toast.error('No PDF available to share');
        return;
    }

    if (!formData.value.email) {
        toast.error('Email address is required');
        return;
    }

    isSharing.value.email = true;

    try {
        const formDataToSend = new FormData();
        formDataToSend.append('pdf', generatedPdfBlob.value, generatedFileName.value);
        formDataToSend.append('companyName', formData.value.companyName);
        formDataToSend.append('attentionTo', formData.value.attentionTo);
        formDataToSend.append('phone', formData.value.phone);
        formDataToSend.append('email', formData.value.email);
        formDataToSend.append('totalAmount', calculateTotal().toString());
        formDataToSend.append('invoiceNumber', formData.value.invoiceNumber);
        formDataToSend.append('dueDate', dueDate.value);
        formDataToSend.append('date', currentDate.value);

        const response = await fetch('/api/share/email', {
            method: 'POST',
            body: formDataToSend,
        });

        if (!response.ok) {
            throw new Error('Failed to send email');
        }

        toast.success(`Invoice sent to ${formData.value.email} successfully!`);
    } catch (error) {
        console.error('Error sending email:', error);
        toast.error('Error sending email. Please try again.');
    } finally {
        isSharing.value.email = false;
    }
};

onMounted(() => {
    currentDate.value = getCurrentDate();
    dueDate.value = getDueDate(30);
    generateInvoiceNumber();
});
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
    border-bottom: 2px solid #2563eb;
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
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
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

.btn-outline {
    background: white;
    color: #374151;
    border: 2px solid #e5e7eb;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.btn-group {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    justify-content: center;
}

.summary-box {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    border: 2px solid #2563eb;
}

.summary-title {
    margin-bottom: 15px;
    color: #1e40af;
    font-size: 18px;
    font-weight: 700;
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
    color: #1e40af;
    border-top: 2px solid #2563eb;
    padding-top: 15px;
    margin-top: 10px;
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
    border-color: #2563eb;
    background: #eff6ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
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

/* PDF Template Styles */
.pdf-template {
    position: absolute;
    left: -9999px;
    display: none;
    width: 793px;
    background: white;
    padding: 0;
    font-family: Arial, sans-serif;
}

.pdf-content {
    padding: 30px 40px;
    position: relative;
}

.pdf-header-proforma {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    margin-bottom: 35px;
    padding-bottom: 25px;
    border-bottom: 3px solid #2563eb;
}

.header-left {
    flex: 1;
}

.logo-section {
    margin-bottom: 15px;
}

.company-logo {
    height: 50px;
    width: auto;
}

.company-name-proforma {
    font-size: 22px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 10px;
}

.company-details {
    font-size: 11px;
    color: #4b5563;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
    margin: 8px 0;
}

.contact-grid p {
    margin: 0;
    line-height: 1.6;
}

.address-line {
    margin-top: 8px;
    line-height: 1.5;
}

.header-right {
    text-align: right;
}

.invoice-badge {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.invoice-badge h1 {
    font-size: 20px;
    margin: 0;
    font-weight: 700;
    letter-spacing: 1px;
}

.invoice-meta {
    background: #eff6ff;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #bfdbfe;
}

.meta-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 11px;
}

.meta-row:last-child {
    margin-bottom: 0;
}

.meta-label {
    font-weight: 600;
    color: #1e40af;
}

.meta-value {
    color: #374151;
}

.bill-to-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border-left: 4px solid #2563eb;
}

.bill-to-label {
    font-size: 10px;
    font-weight: 700;
    color: #2563eb;
    letter-spacing: 1.5px;
    margin-bottom: 10px;
}

.bill-to-content h3 {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 5px;
}

.company-name-bill {
    font-size: 13px;
    font-weight: 600;
    color: #4b5563;
    margin: 3px 0;
}

.bill-to-content p {
    font-size: 11px;
    color: #6b7280;
    margin: 3px 0;
    line-height: 1.5;
}

.contact-info {
    margin-top: 8px;
    display: flex;
    gap: 15px;
}

.contact-info span {
    font-size: 11px;
    color: #4b5563;
}

.items-table-proforma {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
    border: 1px solid #e5e7eb;
}

.items-table-proforma thead {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
}

.items-table-proforma th {
    padding: 14px 12px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.items-table-proforma td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 11px;
    color: #374151;
}

.items-table-proforma tbody tr:hover {
    background: #f9fafb;
}

.items-table-proforma tbody tr:last-child td {
    border-bottom: none;
}

.summary-section-proforma {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 30px;
}

.summary-content {
    width: 350px;
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.summary-row-pdf {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 12px;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}

.summary-row-pdf:last-child {
    border-bottom: none;
}

.total-row-pdf {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    font-size: 16px;
    font-weight: 700;
    padding: 15px;
    margin: 10px -20px -20px -20px;
    border-radius: 0 0 8px 8px;
}

.text-red {
    color: #dc2626;
}

.payment-instructions {
    background: #fffbeb;
    border: 1px solid #fbbf24;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.payment-instructions h4 {
    font-size: 13px;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 12px;
}

.payment-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.payment-label {
    font-size: 10px;
    color: #78350f;
    font-weight: 600;
    margin-bottom: 3px;
}

.payment-value {
    font-size: 11px;
    color: #1c1917;
    font-weight: 600;
}

.terms-proforma {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border: 1px solid #e5e7eb;
}

.terms-proforma h4 {
    font-size: 13px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 12px;
}

.terms-list {
    margin: 0;
    padding-left: 20px;
    font-size: 10px;
    color: #4b5563;
    line-height: 1.7;
}

.terms-list li {
    margin-bottom: 6px;
}

.footer-proforma {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e5e7eb;
}

.footer-note {
    text-align: center;
    margin-bottom: 30px;
}

.footer-note p {
    font-size: 11px;
    color: #6b7280;
    margin: 5px 0;
    line-height: 1.6;
}

.signature-section {
    text-align: right;
    margin-bottom: 20px;
}

.signature-line {
    width: 200px;
    margin-left: auto;
    border-bottom: 2px solid #374151;
    margin-bottom: 8px;
    padding-top: 40px;
}

.signature-name {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    margin: 5px 0;
}

.signature-title {
    font-size: 11px;
    color: #6b7280;
    margin: 0;
}

.auto-generated {
    text-align: center;
    font-size: 10px;
    color: #9ca3af;
    font-style: italic;
}
</style>