<!-- InvoiceAnalyticsDashboard -->

<!-- Revenue Trend, Payment Status Distribution and Platform Charge Distribution -->
<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from 'vue';
import {
    DollarSign,
    FileText,
    CheckCircle,
    AlertCircle,
    CreditCard,
    RefreshCw,
    Loader2
} from 'lucide-vue-next';
import Chart from 'chart.js/auto';

const props = defineProps({
    apiBaseUrl: {
        type: String,
        default: import.meta.env.VITE_BACKEND_API_URL || ''
    },
    initialFilters: {
        type: Object,
        default: () => ({
            startDate: '',
            endDate: '',
            paymentStatus: '',
            platformChargeType: ''
        })
    },
    showFilters: {
        type: Boolean,
        default: true
    },
    showStats: {
        type: Boolean,
        default: true
    },
    showCharts: {
        type: Boolean,
        default: true
    },
    showTopCustomers: {
        type: Boolean,
        default: true
    },
    showDiscountAnalysis: {
        type: Boolean,
        default: true
    },
    topCustomersLimit: {
        type: Number,
        default: 10
    },
    autoFetch: {
        type: Boolean,
        default: true
    },
    enableExternalControl: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['dataLoaded', 'error', 'filterChange']);

// State
const loading = ref(false);
const filters = ref({ ...props.initialFilters });
const revenueTrendGroupBy = ref('day');

const dashboardStats = ref({
    totalRevenue: 0,
    platformRevenue: 0,
    walletRevenue: 0,
    totalGST: 0,
    totalInvoices: 0,
    avgInvoiceValue: 0,
    paidInvoices: { count: 0, amount: 0 },
    unpaidInvoices: { count: 0, amount: 0 },
    paymentStatusBreakdown: []
});

const revenueTrend = ref([]);
const paymentMethods = ref([]);
const platformCharges = ref([]);
const topCustomers = ref([]);
const discountAnalysis = ref({
    totalDiscountedInvoices: 0,
    totalDiscountGiven: 0,
    avgDiscountPercent: 0,
    discountRate: 0
});

const charts = ref({});

// Refs for chart canvases
const revenueTrendChart = ref(null);
const paymentStatusChart = ref(null);
const platformChargeChart = ref(null);

// Methods
const formatCurrency = (value) => {
    if (!value && value !== 0) return '0.00';
    return new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const buildQueryParams = (additionalParams = {}) => {
    return new URLSearchParams({ ...filters.value, ...additionalParams }).toString();
};

const fetchDashboardStats = async () => {
    try {
        const params = buildQueryParams();
        const response = await fetch(`${props.apiBaseUrl}/analytics/dashboard/stats?${params}`);
        const result = await response.json();
        if (result.success) {
            dashboardStats.value = result.data;
            await nextTick();
            renderPaymentStatusChart();
        }
    } catch (error) {
        emit('error', { type: 'stats', error });
        console.error('Error fetching dashboard stats:', error);
    }
};

const fetchRevenueTrend = async () => {
    try {
        const params = buildQueryParams({ groupBy: revenueTrendGroupBy.value });
        const response = await fetch(`${props.apiBaseUrl}/analytics/revenue/trend?${params}`);
        const result = await response.json();
        if (result.success) {
            revenueTrend.value = result.data;
            await nextTick();
            renderRevenueTrendChart();
        }
    } catch (error) {
        emit('error', { type: 'revenueTrend', error });
        console.error('Error fetching revenue trend:', error);
    }
};

const fetchPaymentMethods = async () => {
    try {
        const params = buildQueryParams();
        const response = await fetch(`${props.apiBaseUrl}/analytics/payment-methods?${params}`);
        const result = await response.json();
        if (result.success) {
            paymentMethods.value = result.data;
        }
    } catch (error) {
        emit('error', { type: 'paymentMethods', error });
        console.error('Error fetching payment methods:', error);
    }
};

const fetchPlatformCharges = async () => {
    try {
        const params = buildQueryParams();
        const response = await fetch(`${props.apiBaseUrl}/analytics/platform-charges?${params}`);
        const result = await response.json();
        if (result.success) {
            platformCharges.value = result.data;
            await nextTick();
            renderPlatformChargeChart();
        }
    } catch (error) {
        emit('error', { type: 'platformCharges', error });
        console.error('Error fetching platform charges:', error);
    }
};

const fetchTopCustomers = async () => {
    try {
        const params = buildQueryParams({ limit: props.topCustomersLimit });
        const response = await fetch(`${props.apiBaseUrl}/analytics/top-customers?${params}`);
        const result = await response.json();
        if (result.success) {
            topCustomers.value = result.data;
        }
    } catch (error) {
        emit('error', { type: 'topCustomers', error });
        console.error('Error fetching top customers:', error);
    }
};

const fetchDiscountAnalysis = async () => {
    try {
        const params = buildQueryParams();
        const response = await fetch(`${props.apiBaseUrl}/analytics/discounts?${params}`);
        const result = await response.json();
        if (result.success) {
            discountAnalysis.value = result.data;
        }
    } catch (error) {
        emit('error', { type: 'discountAnalysis', error });
        console.error('Error fetching discount analysis:', error);
    }
};

const fetchAllData = async () => {
    loading.value = true;
    try {
        const promises = [];

        if (props.showStats) promises.push(fetchDashboardStats());
        if (props.showCharts) {
            promises.push(fetchRevenueTrend());
            promises.push(fetchPaymentMethods());
            promises.push(fetchPlatformCharges());
        }
        if (props.showTopCustomers) promises.push(fetchTopCustomers());
        if (props.showDiscountAnalysis) promises.push(fetchDiscountAnalysis());

        await Promise.all(promises);

        emit('dataLoaded', {
            stats: dashboardStats.value,
            revenueTrend: revenueTrend.value,
            paymentMethods: paymentMethods.value,
            platformCharges: platformCharges.value,
            topCustomers: topCustomers.value,
            discountAnalysis: discountAnalysis.value
        });
    } catch (error) {
        emit('error', { type: 'fetchAll', error });
        console.error('Error fetching all data:', error);
    } finally {
        loading.value = false;
    }
};

const renderRevenueTrendChart = () => {
    if (charts.value.revenueTrend) {
        charts.value.revenueTrend.destroy();
    }

    if (!revenueTrendChart.value) return;

    const ctx = revenueTrendChart.value.getContext('2d');
    charts.value.revenueTrend = new Chart(ctx, {
        type: 'line',
        data: {
            labels: revenueTrend.value.map(item => item.period),
            datasets: [
                {
                    label: 'Total Revenue',
                    data: revenueTrend.value.map(item => item.revenue),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Platform Charge',
                    data: revenueTrend.value.map(item => item.platformCharge),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
};

const renderPaymentStatusChart = () => {
    if (charts.value.paymentStatus) {
        charts.value.paymentStatus.destroy();
    }

    if (!paymentStatusChart.value) return;

    const ctx = paymentStatusChart.value.getContext('2d');
    charts.value.paymentStatus = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dashboardStats.value.paymentStatusBreakdown.map(s => s.status),
            datasets: [{
                data: dashboardStats.value.paymentStatusBreakdown.map(s => s.amount),
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(156, 163, 175)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
};

const renderPlatformChargeChart = () => {
    if (charts.value.platformCharge) {
        charts.value.platformCharge.destroy();
    }

    if (!platformChargeChart.value) return;

    const ctx = platformChargeChart.value.getContext('2d');
    charts.value.platformCharge = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: platformCharges.value.map(item => item.type),
            datasets: [{
                label: 'Total Charge',
                data: platformCharges.value.map(item => item.totalCharge),
                backgroundColor: 'rgba(99, 102, 241, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
};

const onFilterChange = () => {
    emit('filterChange', filters.value);
    if (!props.enableExternalControl) {
        fetchAllData();
    }
};

const resetFilters = () => {
    filters.value = { ...props.initialFilters };
    onFilterChange();
};

const destroyCharts = () => {
    Object.values(charts.value).forEach(chart => {
        if (chart) chart.destroy();
    });
    charts.value = {};
};

// Expose methods for external control
defineExpose({
    fetchAllData,
    resetFilters,
    updateFilters: (newFilters) => {
        filters.value = { ...filters.value, ...newFilters };
        onFilterChange();
    },
    getStats: () => dashboardStats.value,
    getData: () => ({
        stats: dashboardStats.value,
        revenueTrend: revenueTrend.value,
        paymentMethods: paymentMethods.value,
        platformCharges: platformCharges.value,
        topCustomers: topCustomers.value,
        discountAnalysis: discountAnalysis.value
    })
});

// Lifecycle
onMounted(() => {
    if (props.autoFetch) {
        fetchAllData();
    }
});

onBeforeUnmount(() => {
    destroyCharts();
});

// Watch for external filter changes
watch(() => props.initialFilters, (newFilters) => {
    filters.value = { ...newFilters };
    if (props.autoFetch) {
        fetchAllData();
    }
}, { deep: true });
</script>

<template>
    <div class="min-h-screen">
        <!-- Filters -->
        <div v-if="showFilters" class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" v-model="filters.startDate" @change="onFilterChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" v-model="filters.endDate" @change="onFilterChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select v-model="filters.paymentStatus" @change="onFilterChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Charge Type</label>
                    <select v-model="filters.platformChargeType" @change="onFilterChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="Monthly">Monthly</option>
                        <option value="Yearly">Yearly</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex gap-4">
                <button @click="resetFilters"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                    <RefreshCw class="w-4 h-4 inline mr-2" />
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <Loader2 class="w-8 h-8 animate-spin text-blue-500" />
        </div>

        <!-- Dashboard Content -->
        <div v-else>
            <!-- Stats Cards -->
            <div v-if="showStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                ₹{{ formatCurrency(dashboardStats.totalRevenue) }}
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <DollarSign class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        GST: ₹{{ formatCurrency(dashboardStats.totalGST) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Invoices</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ dashboardStats.totalInvoices }}
                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <FileText class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Avg: ₹{{ formatCurrency(dashboardStats.avgInvoiceValue) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Paid Invoices</p>
                            <p class="text-2xl font-bold text-green-600 mt-2">
                                {{ dashboardStats.paidInvoices.count }}
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <CheckCircle class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        ₹{{ formatCurrency(dashboardStats.paidInvoices.amount) }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Unpaid Invoices</p>
                            <p class="text-2xl font-bold text-red-600 mt-2">
                                {{ dashboardStats.unpaidInvoices.count }}
                            </p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <AlertCircle class="w-6 h-6 text-red-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        ₹{{ formatCurrency(dashboardStats.unpaidInvoices.amount) }}
                    </p>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div v-if="showCharts" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Revenue Trend Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Revenue Trend</h2>
                        <select v-model="revenueTrendGroupBy" @change="fetchRevenueTrend"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                            <option value="day">Daily</option>
                            <option value="week">Weekly</option>
                            <option value="month">Monthly</option>
                        </select>
                    </div>
                    <div class="h-64">
                        <canvas ref="revenueTrendChart"></canvas>
                    </div>
                </div>

                <!-- Payment Status Pie Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Status Distribution</h2>
                    <div class="h-64 flex items-center justify-center">
                        <canvas ref="paymentStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div v-if="showCharts" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Payment Methods -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h2>
                    <div class="space-y-3">
                        <div v-for="method in paymentMethods" :key="method.method"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <CreditCard class="w-5 h-5 text-gray-600 mr-3" />
                                <span class="font-medium text-gray-900">{{ method.method }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">
                                    ₹{{ formatCurrency(method.totalAmount) }}
                                </p>
                                <p class="text-xs text-gray-500">{{ method.count }} transactions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Charge Types -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Platform Charge Distribution</h2>
                    <div class="h-64">
                        <canvas ref="platformChargeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Customers -->
            <div v-if="showTopCustomers" class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Customers by Revenue</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contact Person
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Invoices
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Revenue
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Avg Invoice
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(customer, index) in topCustomers" :key="index">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ customer.companyName }}</div>
                                    <div class="text-sm text-gray-500">{{ customer.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ customer.contactPerson }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ customer.invoiceCount }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    ₹{{ formatCurrency(customer.totalRevenue) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹{{ formatCurrency(customer.avgInvoiceValue) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Discount Analysis -->
            <div v-if="showDiscountAnalysis" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Discount Analysis</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm text-purple-600 font-medium">Discounted Invoices</p>
                        <p class="text-2xl font-bold text-purple-900 mt-1">
                            {{ discountAnalysis.totalDiscountedInvoices }}
                        </p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm text-purple-600 font-medium">Total Discount Given</p>
                        <p class="text-2xl font-bold text-purple-900 mt-1">
                            ₹{{ formatCurrency(discountAnalysis.totalDiscountGiven) }}
                        </p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm text-purple-600 font-medium">Avg Discount %</p>
                        <p class="text-2xl font-bold text-purple-900 mt-1">
                            {{ discountAnalysis.avgDiscountPercent.toFixed(2) }}%
                        </p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm text-purple-600 font-medium">Discount Rate</p>
                        <p class="text-2xl font-bold text-purple-900 mt-1">
                            {{ discountAnalysis.discountRate }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>