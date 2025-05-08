<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <!-- Quick Stats Section! -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-medium text-gray-900">Quick Stats</h3>
                            <div class="mt-4 grid grid-cols-3 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <p class="text-sm text-gray-500">Total Clients</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ \App\Models\Client::count() }}</p>
                                </div>
                                <div class="bg-green-500 p-4 rounded-lg border border-gray-200">
                                    <p class="text-sm text-white">Active Policies</p>
                                    <p class="text-2xl font-semibold text-white">{{ \App\Models\Client::where('status', 'Active')->count() }}</p>
                                </div>
                                <div class="bg-red-500 p-4 rounded-lg border border-gray-200">
                                    <p class="text-sm text-white">Total Expiring Policies</p>
                                    <p class="text-2xl font-semibold text-white">{{ \App\Models\Client::where('status', 'Expiring')->count() }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats Section -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            
                            <div class="mt-4 grid grid-cols-1 gap-4">
                                <!-- Forecast Renewal Chart -->
                                <div class="bg-white p-6 rounded-lg shadow">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-semibold">Forecast Renewal</h3>
                                        <form id="insuranceFilterForm" class="flex items-center space-x-2">
                                            <label for="insurance_company" class="text-sm text-gray-600">Filter by Insurance Company:</label>
                                            <select name="insurance_company" 
                                                    id="insurance_company" 
                                                    onchange="this.form.submit()"
                                                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <option value="all" {{ request('insurance_company', 'all') == 'all' ? 'selected' : '' }}>All Companies</option>
                                                @foreach($insuranceCompanies as $company)
                                                    <option value="{{ $company }}" {{ request('insurance_company') == $company ? 'selected' : '' }}>
                                                        {{ $company }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                    <canvas id="forecastRenewalChart"></canvas>
                                </div>

                                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                                <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const forecastCtx = document.getElementById('forecastRenewalChart').getContext('2d');
                                        
                                        // Get data from the controller
                                        const forecastData = @json($forecastData);
                                        console.log('Forecast Data in JS:', forecastData);
                                        
                                        // Format number with thousand separator
                                        const formatNumber = (num) => {
                                            return 'RM ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        };

                                        // Calculate max value and round up to nearest 1000
                                        const maxPremium = Math.ceil(Math.max(...forecastData.map(item => item.total_premium)) / 1000) * 1000;
                                        
                                        new Chart(forecastCtx, {
                                            type: 'bar',
                                            data: {
                                                labels: forecastData.map(item => item.month),
                                                datasets: [{
                                                    label: 'Net Premium (RM)',
                                                    data: forecastData.map(item => item.total_premium),
                                                    backgroundColor: 'rgba(59, 130, 246, 0.8)', // Blue color
                                                    borderColor: 'rgba(59, 130, 246, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        max: maxPremium * 1.2,
                                                        ticks: {
                                                            stepSize: Math.ceil(maxPremium / 5),
                                                            callback: function(value) {
                                                                return formatNumber(value);
                                                            }
                                                        }
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            label: function(context) {
                                                                return formatNumber(context.raw);
                                                            }
                                                        }
                                                    },
                                                    datalabels: {
                                                        anchor: 'end',
                                                        align: 'top',
                                                        formatter: function(value) {
                                                            return formatNumber(value);
                                                        },
                                                        font: {
                                                            weight: 'bold'
                                                        },
                                                        color: '#1f2937'
                                                    }
                                                }
                                            },
                                            plugins: [ChartDataLabels]
                                        });
                                    });
                                </script>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <!-- Clients by Insurance Company Chart -->
                                <div class="bg-white p-6 rounded-lg shadow">
                                    <h3 class="text-lg font-semibold mb-4">Clients by Insurance Company</h3>
                                    <canvas id="insuranceChart"></canvas>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const ctx = document.getElementById('insuranceChart').getContext('2d');
                                        
                                        // Get data from the controller
                                        const data = @json($insuranceCompanyData);
                                        
                                        // Format number with thousand separator
                                        const formatNumber = (num) => {
                                            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        };

                                        // Calculate max value and round up to nearest 10
                                        const maxValue = Math.ceil(Math.max(...data.map(item => item.count)) / 10) * 10;
                                        
                                        new Chart(ctx, {
                                            type: 'bar',
                                            data: {
                                                labels: data.map(item => item.company),
                                                datasets: [{
                                                    label: 'Number of Clients',
                                                    data: data.map(item => item.count),
                                                    backgroundColor: 'rgba(249, 115, 22, 0.8)', // Orange color
                                                    borderColor: 'rgba(249, 115, 22, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        max: maxValue+50,
                                                        ticks: {
                                                            stepSize: Math.ceil(maxValue / 5),
                                                            callback: function(value) {
                                                                return formatNumber(value);
                                                            }
                                                        }
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            label: function(context) {
                                                                return formatNumber(context.raw);
                                                            }
                                                        }
                                                    },
                                                    datalabels: {
                                                        anchor: 'end',
                                                        align: 'top',
                                                        formatter: function(value) {
                                                            return formatNumber(value);
                                                        },
                                                        font: {
                                                            weight: 'bold'
                                                        },
                                                        color: '#1f2937'
                                                    }
                                                }
                                            },
                                            plugins: [ChartDataLabels]
                                        });
                                    });
                                </script>
                                
                                <!-- Category Chart -->
                                <div class="bg-white p-6 rounded-lg shadow">
                                    <h3 class="text-lg font-semibold mb-4">Clients by Category</h3>
                                    <canvas id="categoryChart"></canvas>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
                                        
                                        // Get data from the controller
                                        const categoryData = @json($categoryData);
                                        
                                        // Format number with thousand separator
                                        const formatNumber = (num) => {
                                            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        };

                                        // Calculate max value and round up to nearest 100 plus 50
                                        const maxCategoryValue = Math.ceil(Math.max(...categoryData.map(item => item.count)) / 100) * 100 + 50;
                                        
                                        new Chart(categoryCtx, {
                                            type: 'bar',
                                            data: {
                                                labels: categoryData.map(item => item.category),
                                                datasets: [{
                                                    label: 'Number of Clients',
                                                    data: categoryData.map(item => item.count),
                                                    backgroundColor: 'rgba(16, 185, 129, 0.8)', // Green color
                                                    borderColor: 'rgba(16, 185, 129, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        max: maxCategoryValue,
                                                        ticks: {
                                                            stepSize: Math.ceil(maxCategoryValue / 5),
                                                            callback: function(value) {
                                                                return formatNumber(value);
                                                            }
                                                        }
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            label: function(context) {
                                                                return formatNumber(context.raw);
                                                            }
                                                        }
                                                    },
                                                    datalabels: {
                                                        anchor: 'end',
                                                        align: 'top',
                                                        formatter: function(value) {
                                                            return formatNumber(value);
                                                        },
                                                        font: {
                                                            weight: 'bold'
                                                        },
                                                        color: '#1f2937'
                                                    }
                                                }
                                            },
                                            plugins: [ChartDataLabels]
                                        });
                                    });
                                </script>

                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4">
                                <!-- Total Premium vs Inception Date Monthly Chart -->
                                <div class="bg-white p-6 rounded-lg shadow">
                                    <h3 class="text-lg font-semibold mb-4">Total Premium vs Inception Date Monthly</h3>
                                    <canvas id="totalPremiumInceptionDateChart"></canvas>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const inceptionCtx = document.getElementById('totalPremiumInceptionDateChart').getContext('2d');
                                        
                                        // Get data from the controller
                                        const inceptionData = @json($inceptionPremiumData);
                                        console.log('Inception Data in JS:', inceptionData);
                                        
                                        // Format number with thousand separator
                                        const formatNumber = (num) => {
                                            return '' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        };

                                        // Calculate max value and round up to nearest 1000 plus 50
                                        const maxInceptionValue = Math.ceil(Math.max(...inceptionData.map(item => item.total_premium)) / 1000) * 1000 + 50;
                                        
                                        new Chart(inceptionCtx, {
                                            type: 'bar',
                                            data: {
                                                labels: inceptionData.map(item => {
                                                    const date = new Date(item.month + '-01');
                                                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                                                }),
                                                datasets: [{
                                                    label: 'Net Premium',
                                                    data: inceptionData.map(item => item.total_premium),
                                                    backgroundColor: 'rgba(139, 92, 246, 0.8)', // Purple color
                                                    borderColor: 'rgba(139, 92, 246, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        max: maxInceptionValue * 1.2,
                                                        ticks: {
                                                            stepSize: Math.ceil(maxInceptionValue / 5),
                                                            callback: function(value) {
                                                                return formatNumber(value);
                                                            }
                                                        }
                                                    },
                                                    x: {
                                                        ticks: {
                                                            maxRotation: 45,
                                                            minRotation: 45
                                                        }
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            label: function(context) {
                                                                return formatNumber(context.raw);
                                                            }
                                                        }
                                                    },
                                                    datalabels: {
                                                        tickSize: {
                                                            maxRotation: 45,
                                                            minRotation: 45
                                                        },
                                                        anchor: 'end',
                                                        align: 'top',
                                                        formatter: function(value) {
                                                            return formatNumber(value);
                                                        },
                                                        font: {
                                                            weight: 'bold'
                                                        },
                                                        color: '#1f2937'
                                                    }
                                                }
                                            },
                                            plugins: [ChartDataLabels]
                                        });
                                    });
                                </script>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4">
                                <!-- Total Premium vs Inception Date Yearly Chart -->
                                <div class="bg-white p-6 rounded-lg shadow">
                                    <h3 class="text-lg font-semibold mb-4">Total Premium vs Inception Date Yearly</h3>
                                    <canvas id="totalPremiumInceptionDateYearlyChart"></canvas>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const yearlyCtx = document.getElementById('totalPremiumInceptionDateYearlyChart').getContext('2d');
                                        
                                        // Get data from the controller
                                        const yearlyData = @json($inceptionPremiumYearlyData);
                                        console.log('Yearly Data in JS:', yearlyData);
                                        
                                        // Format number with thousand separator
                                        const formatNumber = (num) => {
                                            return 'RM ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        };

                                        // Calculate max value and round up to nearest 1000 plus 50
                                        const maxYearlyValue = Math.ceil(Math.max(...yearlyData.map(item => item.total_premium)) / 1000) * 1000 + 50;
                                        
                                        new Chart(yearlyCtx, {
                                            type: 'bar',
                                            data: {
                                                labels: yearlyData.map(item => item.year),
                                                datasets: [{
                                                    label: 'Net Premium',
                                                    data: yearlyData.map(item => item.total_premium),
                                                    backgroundColor: 'rgba(236, 72, 153, 0.8)', // Pink color
                                                    borderColor: 'rgba(236, 72, 153, 1)',
                                                    borderWidth: 1
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                scales: {
                                                    y: {
                                                        beginAtZero: true,
                                                        max: maxYearlyValue * 1.2,
                                                        ticks: {
                                                            stepSize: Math.ceil(maxYearlyValue / 5),
                                                            callback: function(value) {
                                                                return formatNumber(value);
                                                            }
                                                        }
                                                    },
                                                    x: {
                                                        ticks: {
                                                            maxRotation: 45,
                                                            minRotation: 45
                                                        }
                                                    }
                                                },
                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            label: function(context) {
                                                                return formatNumber(context.raw);
                                                            }
                                                        }
                                                    },
                                                    datalabels: {
                                                        anchor: 'end',
                                                        align: 'top',
                                                        formatter: function(value) {
                                                            return formatNumber(value);
                                                        },
                                                        font: {
                                                            weight: 'bold'
                                                        },
                                                        color: '#1f2937'
                                                    }
                                                }
                                            },
                                            plugins: [ChartDataLabels]
                                        });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
