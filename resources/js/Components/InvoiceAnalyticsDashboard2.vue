<!-- InvoiceAnalyticsDashboard -->
<template>
    <div class="min-h-screen">

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" v-model="filters.startDate" @change="fetchAllData"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" v-model="filters.endDate" @change="fetchAllData"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                    <select v-model="filters.paymentStatus" @change="fetchAllData"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Charge Type</label>
                    <select v-model="filters.platformChargeType" @change="fetchAllData"
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
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
            <div class="bg-white rounded-lg shadow p-6 mb-6">
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
            <div class="bg-white rounded-lg shadow p-6">
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

<script>
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

const base_url = import.meta.env.VITE_BACKEND_API_URL;

export default {
    name: 'InvoiceAnalyticsDashboard',
    components: {
        DollarSign,
        FileText,
        CheckCircle,
        AlertCircle,
        CreditCard,
        RefreshCw,
        Loader2
    },
    data() {
        return {
            loading: false,
            filters: {
                startDate: '',
                endDate: '',
                paymentStatus: '',
                platformChargeType: ''
            },
            revenueTrendGroupBy: 'day',
            dashboardStats: {
                totalRevenue: 0,
                platformRevenue: 0,
                walletRevenue: 0,
                totalGST: 0,
                totalInvoices: 0,
                avgInvoiceValue: 0,
                paidInvoices: { count: 0, amount: 0 },
                unpaidInvoices: { count: 0, amount: 0 },
                paymentStatusBreakdown: []
            },
            revenueTrend: [],
            paymentMethods: [],
            platformCharges: [],
            topCustomers: [],
            discountAnalysis: {
                totalDiscountedInvoices: 0,
                totalDiscountGiven: 0,
                avgDiscountPercent: 0,
                discountRate: 0
            },
            charts: {}
        };
    },
    mounted() {
        this.fetchAllData();
    },
    beforeUnmount() {
        // Destroy all charts
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
    },
    methods: {
        async fetchAllData() {
            this.loading = true;
            try {
                await Promise.all([
                    this.fetchDashboardStats(),
                    this.fetchRevenueTrend(),
                    this.fetchPaymentMethods(),
                    this.fetchPlatformCharges(),
                    this.fetchTopCustomers(),
                    this.fetchDiscountAnalysis()
                ]);
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                this.loading = false;
            }
        },

        async fetchDashboardStats() {
            const params = new URLSearchParams(this.filters).toString();
            const response = await fetch(`${base_url}/analytics/dashboard/stats?${params}`);
            const result = await response.json();
            if (result.success) {
                this.dashboardStats = result.data;
                this.$nextTick(() => this.renderPaymentStatusChart());
            }
        },

        async fetchRevenueTrend() {
            const params = new URLSearchParams({
                ...this.filters,
                groupBy: this.revenueTrendGroupBy
            }).toString();
            const response = await fetch(`${base_url}/analytics/revenue/trend?${params}`);
            const result = await response.json();
            if (result.success) {
                console.log("result.data : ", result.data);
                this.revenueTrend = result.data;
                this.$nextTick(() => this.renderRevenueTrendChart());
            }
        },

        async fetchPaymentMethods() {
            const params = new URLSearchParams(this.filters).toString();
            const response = await fetch(`${base_url}/analytics/payment-methods?${params}`);
            const result = await response.json();
            if (result.success) {
                this.paymentMethods = result.data;
            }
        },

        async fetchPlatformCharges() {
            const params = new URLSearchParams(this.filters).toString();
            const response = await fetch(`${base_url}/analytics/platform-charges?${params}`);
            const result = await response.json();
            if (result.success) {
                this.platformCharges = result.data;
                this.$nextTick(() => this.renderPlatformChargeChart());
            }
        },

        async fetchTopCustomers() {
            const params = new URLSearchParams({ ...this.filters, limit: 10 }).toString();
            const response = await fetch(`${base_url}/analytics/top-customers?${params}`);
            const result = await response.json();
            if (result.success) {
                this.topCustomers = result.data;
            }
        },

        async fetchDiscountAnalysis() {
            const params = new URLSearchParams(this.filters).toString();
            const response = await fetch(`${base_url}/analytics/discounts?${params}`);
            const result = await response.json();
            if (result.success) {
                this.discountAnalysis = result.data;
            }
        },

        renderRevenueTrendChart() {
            if (this.charts.revenueTrend) {
                this.charts.revenueTrend.destroy();
            }

            const ctx = this.$refs.revenueTrendChart.getContext('2d');
            this.charts.revenueTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.revenueTrend.map(item => item.period),
                    datasets: [
                        {
                            label: 'Total Revenue',
                            data: this.revenueTrend.map(item => item.revenue),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Platform Charge',
                            data: this.revenueTrend.map(item => item.platformCharge),
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
        },

        renderPaymentStatusChart() {
            if (this.charts.paymentStatus) {
                this.charts.paymentStatus.destroy();
            }

            const ctx = this.$refs.paymentStatusChart.getContext('2d');
            this.charts.paymentStatus = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.dashboardStats.paymentStatusBreakdown.map(s => s.status),
                    datasets: [{
                        data: this.dashboardStats.paymentStatusBreakdown.map(s => s.amount),
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
        },

        renderPlatformChargeChart() {
            if (this.charts.platformCharge) {
                this.charts.platformCharge.destroy();
            }

            const ctx = this.$refs.platformChargeChart.getContext('2d');
            this.charts.platformCharge = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.platformCharges.map(item => item.type),
                    datasets: [{
                        label: 'Total Charge',
                        data: this.platformCharges.map(item => item.totalCharge),
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
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },

        resetFilters() {
            this.filters = {
                startDate: '',
                endDate: '',
                paymentStatus: '',
                platformChargeType: ''
            };
            this.fetchAllData();
        }
    }
};
</script>