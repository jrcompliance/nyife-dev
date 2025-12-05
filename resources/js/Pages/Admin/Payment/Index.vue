<template>
    <AppLayout>
        <div
            class="bg-white md:bg-inherit pt-10 px-4 md:pt-8 md:p-8 rounded-[5px] text-[#000] h-full md:overflow-y-auto">
            <div class="flex-1 mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="w-10 h-10 text-white rounded-xl bg-gradient-to-br from-[#ff5100] to-[#ff7a3d] flex items-center justify-center shadow-lg shadow-[#ff5100]/25">
                        <CreditCard />
                    </div>
                    <h2
                        class="text-3xl font-bold bg-gradient-to-r from-gray-900 via-gray-800 to-gray-700 bg-clip-text text-transparent">
                        {{ $t('Invoice & Payment receipts') }}
                    </h2>
                </div>
                <div class="flex items-start gap-2 text-gray-600">
                    <p class="text-sm md:text-base font-medium leading-relaxed">
                        {{ $t('Quotations, proformas, and payment receipts.') }}
                    </p>
                </div>
            </div>

            <!-- Pass formatted data to table component -->
            <InvoiceAndPaymentTable :rows="seedData" :filters="props.filters" />
        </div>
    </AppLayout>
</template>

<script setup>
import { CreditCard } from "lucide-vue-next";
import AppLayout from "./../Layout/App.vue";
import InvoiceAndPaymentTable from '@/Components/Tables/InvoiceAndPaymentTable.vue';
import { ref } from "vue";

const props = defineProps({
    title: String,
    rows: Object,
    filters: Object
});

const seedData = ref({
    data: [
        // ----------------------- 1 -----------------------
        {
            companyName: "BrightVision Pvt Ltd",
            contactPerson: "Karan Singh",
            phone: "+917827742908",
            email: "karan.singh@brightvision.com",
            address: "Lane 9, MG Road\nMumbai, Maharashtra",
            selectedPlanId: 5,
            platformChargeType: "Monthly",
            platformCharge: 1000, // FIXED
            walletRecharge: 0,
            setupFee: 0,
            customizationFee: 0,
            additionalFee: [],

            // CALCULATED
            sub_total: 1000, // 1000
            discount: 100,
            amount_after_discount: 900,
            GST: 162,
            total: 1062,

            quotationNumber: "QT-2025-00055",
            quotationDate: "2025-12-12",
            quotationValidUntilDate: "2025-12-22",
            quotationInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",

            proformaInvoice: false,
            proformaNumber: "",
            proformaDate: "",
            proformaValidUntilDate: "",
            paymentUrl: "",
            proformaInvoicePdfUrl: "",

            paymentReceipt: false,
            paymentReceiptNumber: "",
            paymentReferenceNumber: "",
            paymentReceiptDate: "",
            paymentMethod: "",
            paymentAmount: 0,
            paymentInvoicePdfUrl: "",
        }
        ,
        // ----------------------- 2 -----------------------
        {
            companyName: "NextGen Technologies",
            contactPerson: "Amit Verma",
            phone: "+918800281734",
            email: "amit.verma@nextgen.com",
            address: "Plot 44, Sohna Road\nGurgaon, Haryana",
            selectedPlanId: 2,
            platformChargeType: "Yearly",
            platformCharge: 11000, // FIXED
            walletRecharge: 5000,
            setupFee: 600,
            customizationFee: 450,
            additionalFee: [
                { description: "API Access Fee", amount: 1500 }
            ],

            // CALCULATED
            sub_total: 11000 + 5000 + 600 + 450 + 1500, // 18550
            discount: 1855,
            amount_after_discount: 16695,
            GST: 3005.1,
            total: 19700.1,

            quotationNumber: "QT-2025-00022",
            quotationDate: "2025-12-05",
            quotationValidUntilDate: "2025-12-20",
            quotationInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",

            proformaInvoice: true,
            proformaNumber: "PF-2025-00222",
            proformaDate: "2025-12-06",
            proformaValidUntilDate: "2025-12-21",
            paymentUrl: "https://rzp.io/l/invPF222",
            proformaInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",

            paymentReceipt: true,
            paymentReceiptNumber: "RCPT-2025-50222",
            paymentReferenceNumber: "PAYN-REF-222",
            paymentReceiptDate: "2025-12-07",
            paymentMethod: "Credit Card",
            paymentAmount: 19700.1,
            paymentInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",
        },

        // ----------------------- 3 -----------------------
        {
            companyName: "Vertex Infocom",
            contactPerson: "Suresh Mehta",
            phone: "+918800281734",
            email: "suresh.mehta@vertex.in",
            address: "Sector 21\nNoida, UP - 201301",
            selectedPlanId: 4,
            platformChargeType: "Yearly",
            platformCharge: 11000, // FIXED
            walletRecharge: 2500,
            setupFee: 900,
            customizationFee: 650,
            additionalFee: [
                { description: "Custom Module Fee", amount: 2000 }
            ],

            // CALCULATED
            sub_total: 11000 + 2500 + 900 + 650 + 2000, // 18050
            discount: 1805,
            amount_after_discount: 16245,
            GST: 2924.1,
            total: 19169.1,

            quotationNumber: "QT-2025-00044",
            quotationDate: "2025-12-10",
            quotationValidUntilDate: "2025-12-25",
            quotationInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",

            proformaInvoice: true,
            proformaNumber: "PF-2025-00444",
            proformaDate: "2025-12-11",
            proformaValidUntilDate: "2025-12-26",
            paymentUrl: "https://rzp.io/l/invPF444",
            proformaInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",

            paymentReceipt: false,
            paymentReceiptNumber: "",
            paymentReferenceNumber: "",
            paymentReceiptDate: "",
            paymentMethod: "",
            paymentAmount: 0,
            paymentInvoicePdfUrl: "",
        },

        // ----------------------- 4 -----------------------
        {
            companyName: "Abc Company Pvt Ltd",
            contactPerson: "Rohit Sharma",
            phone: "+918800281734",
            email: "rohit.sharma@example.com",
            address: "D-221 Industrial Area\nNew Delhi - 110020",
            selectedPlanId: 1,
            platformChargeType: "Monthly",
            platformCharge: 1000,
            walletRecharge: 2000,
            setupFee: 300,
            customizationFee: 250,
            additionalFee: [
                { description: "Support Fee", amount: 500 }
            ],

            // CALCULATED
            sub_total: 4050,
            discount: 405,
            amount_after_discount: 3645,
            GST: 656.1, // 18%
            total: 4301.1,

            quotationNumber: "QT-2025-00011",
            quotationDate: "2025-12-01",
            quotationValidUntilDate: "2025-12-10",
            quotationInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",

            proformaInvoice: true,
            proformaNumber: "PF-2025-00111",
            proformaDate: "2025-12-02",
            proformaValidUntilDate: "2025-12-12",
            paymentUrl: "https://rzp.io/l/invPF111",
            proformaInvoicePdfUrl: "https://www.incometax.gov.in/iec/foportal/sites/default/files/2021-11/ERI%20API%20Specification_v1.1.pdf",

            paymentReceipt: false,
            paymentReceiptNumber: "",
            paymentReferenceNumber: "",
            paymentReceiptDate: "",
            paymentMethod: "",
            paymentAmount: 0,
            paymentInvoicePdfUrl: "",
        },
    ]
});

</script>