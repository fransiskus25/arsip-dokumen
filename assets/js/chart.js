/**
 * Chart.js Initialization and Management
 * Untuk dashboard dan statistik sistem pengarsipan dokumen
 */

class ChartManager {
    constructor() {
        this.charts = new Map();
        this.defaultColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
            '#858796', '#f8f9fa', '#5a5c69', '#3a3b45', '#2e59d9',
            '#17a673', '#2c9faf', '#f6c23e', '#e74a3b', '#858796'
        ];
        
        this.init();
    }

    init() {
        console.log('Initializing ChartManager...');
        
        // Pastikan Chart.js sudah terload
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Loading now...');
            this.loadChartJS().then(() => {
                this.initializeCharts();
            }).catch(error => {
                console.error('Failed to load Chart.js:', error);
            });
        } else {
            this.initializeCharts();
        }
    }

    loadChartJS() {
        return new Promise((resolve, reject) => {
            if (typeof Chart !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    initializeCharts() {
        console.log('Initializing all charts...');
        
        this.initMonthlyChart();
        this.initCategoryChart();
        this.initAnnualTrendChart();
        
        console.log('All charts initialized:', this.charts.size);
    }

    // Monthly Document Statistics Chart
    initMonthlyChart() {
        const ctx = document.getElementById('monthlyChart');
        if (!ctx) {
            console.warn('Monthly chart canvas not found');
            return;
        }

        try {
            const monthlyData = this.getMonthlyData();
            console.log('Monthly data:', monthlyData);

            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Jumlah Dokumen',
                        data: monthlyData,
                        backgroundColor: 'rgba(78, 115, 223, 0.7)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: `Statistik Bulanan (${new Date().getFullYear()})`,
                            font: {
                                size: 16,
                                weight: '600'
                            },
                            padding: 20
                        },
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 14
                            },
                            padding: 12,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    return `Dokumen: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 12
                                }
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Dokumen',
                                font: {
                                    size: 14,
                                    weight: '600'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });

            this.charts.set('monthly', chart);
            console.log('Monthly chart initialized successfully');
        } catch (error) {
            console.error('Error initializing monthly chart:', error);
        }
    }

    // Document Category Distribution Chart
    initCategoryChart() {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) {
            console.warn('Category chart canvas not found');
            return;
        }

        try {
            const categoryData = this.getCategoryData();
            console.log('Category data:', categoryData);

            const chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.labels,
                    datasets: [{
                        data: categoryData.values,
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.9)',
                            'rgba(28, 200, 138, 0.9)',
                            'rgba(54, 185, 204, 0.9)',
                            'rgba(246, 194, 62, 0.9)',
                            'rgba(231, 74, 59, 0.9)',
                            'rgba(133, 135, 150, 0.9)'
                        ],
                        borderColor: [
                            'rgba(78, 115, 223, 1)',
                            'rgba(28, 200, 138, 1)',
                            'rgba(54, 185, 204, 1)',
                            'rgba(246, 194, 62, 1)',
                            'rgba(231, 74, 59, 1)',
                            'rgba(133, 135, 150, 1)'
                        ],
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribusi Kategori Dokumen',
                            font: {
                                size: 16,
                                weight: '600'
                            },
                            padding: 20
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((context.raw / total) * 100);
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            this.charts.set('category', chart);
            console.log('Category chart initialized successfully');
        } catch (error) {
            console.error('Error initializing category chart:', error);
        }
    }

    // Annual Trend Chart
    initAnnualTrendChart() {
        const ctx = document.getElementById('annualTrendChart');
        if (!ctx) {
            console.warn('Annual trend chart canvas not found');
            return;
        }

        try {
            const trendData = this.getAnnualTrendData();
            console.log('Annual trend data:', trendData);

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.years,
                    datasets: [{
                        label: 'Total Dokumen',
                        data: trendData.values,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Trend Tahunan Dokumen',
                            font: {
                                size: 16,
                                weight: '600'
                            },
                            padding: 20
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Dokumen',
                                font: {
                                    size: 14,
                                    weight: '600'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Tahun',
                                font: {
                                    size: 14,
                                    weight: '600'
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 2000
                    }
                }
            });

            this.charts.set('annualTrend', chart);
            console.log('Annual trend chart initialized successfully');
        } catch (error) {
            console.error('Error initializing annual trend chart:', error);
        }
    }

    // Data fetching methods
    getMonthlyData() {
        try {
            // Coba dapatkan data dari global variable
            if (typeof monthlyData !== 'undefined' && Array.isArray(monthlyData)) {
                return monthlyData;
            }
            
            // Fallback: generate random data untuk demo
            console.warn('Monthly data not found in global scope, using demo data');
            return Array.from({length: 12}, () => Math.floor(Math.random() * 50) + 10);
        } catch (error) {
            console.error('Error getting monthly data:', error);
            return [12, 19, 15, 17, 19, 23, 17, 15, 18, 16, 14, 11];
        }
    }

    getCategoryData() {
        try {
            // Coba dapatkan data dari global variable
            if (typeof categoryData !== 'undefined' && categoryData.labels && categoryData.values) {
                return categoryData;
            }
            
            // Fallback: data demo
            console.warn('Category data not found in global scope, using demo data');
            return {
                labels: ['Surat Masuk', 'Surat Keluar', 'Laporan', 'SK', 'Nota Dinas'],
                values: [45, 30, 25, 15, 10]
            };
        } catch (error) {
            console.error('Error getting category data:', error);
            return {
                labels: ['Surat Masuk', 'Surat Keluar', 'Laporan'],
                values: [40, 30, 20]
            };
        }
    }

    getAnnualTrendData() {
        try {
            // Coba dapatkan data dari global variable
            if (typeof annualTrendData !== 'undefined' && annualTrendData.years && annualTrendData.values) {
                return annualTrendData;
            }
            
            // Fallback: data demo
            console.warn('Annual trend data not found in global scope, using demo data');
            const currentYear = new Date().getFullYear();
            const years = Array.from({length: 5}, (_, i) => currentYear - 4 + i);
            const values = Array.from({length: 5}, (_, i) => Math.floor(Math.random() * 200) + 100);
            
            return { years, values };
        } catch (error) {
            console.error('Error getting annual trend data:', error);
            return {
                years: ['2020', '2021', '2022', '2023', '2024'],
                values: [100, 150, 200, 250, 300]
            };
        }
    }

    // Utility methods
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    updateChartData(chartId, newData) {
        const chart = this.charts.get(chartId);
        if (chart) {
            chart.data = newData;
            chart.update('none');
        }
    }

    refreshAllCharts() {
        this.charts.forEach(chart => {
            if (chart) chart.update();
        });
    }

    destroyAllCharts() {
        this.charts.forEach(chart => {
            if (chart) chart.destroy();
        });
        this.charts.clear();
    }

    // Export chart as image
    exportChartAsImage(chartId, fileName = 'chart') {
        const chart = this.charts.get(chartId);
        if (!chart) {
            alert('Chart tidak ditemukan!');
            return;
        }

        try {
            const link = document.createElement('a');
            link.href = chart.toBase64Image();
            link.download = `${fileName}-${new Date().toISOString().split('T')[0]}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (error) {
            console.error('Error exporting chart:', error);
            alert('Error saat mengexport chart: ' + error.message);
        }
    }

    // Print chart
    printChart(chartId) {
        const chart = this.charts.get(chartId);
        if (!chart) {
            alert('Chart tidak ditemukan!');
            return;
        }

        try {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                    <head>
                        <title>Print Chart</title>
                        <style>
                            body { 
                                text-align: center; 
                                margin: 40px; 
                                font-family: Arial, sans-serif;
                            }
                            img { 
                                max-width: 100%; 
                                height: auto;
                                border: 1px solid #ddd;
                                border-radius: 8px;
                                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                            }
                            .print-header {
                                margin-bottom: 20px;
                                color: #333;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="print-header">
                            <h1>Sistem Pengarsipan Dokumen</h1>
                            <p>Chart Report - ${new Date().toLocaleDateString('id-ID')}</p>
                        </div>
                        <img src="${chart.toBase64Image()}" />
                        <script>
                            window.onload = function() {
                                window.print();
                                setTimeout(function() {
                                    window.close();
                                }, 500);
                            }
                        </script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        } catch (error) {
            console.error('Error printing chart:', error);
            alert('Error saat mencetak chart: ' + error.message);
        }
    }
}

// Initialize ChartManager when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing charts...');
    
    // Tunggu sedikit untuk memastikan semua resource terload
    setTimeout(() => {
        window.chartManager = new ChartManager();
        
        // Tambahkan event listener untuk tombol export/print
        document.addEventListener('click', function(e) {
            if (e.target.closest('[data-chart-export]')) {
                const chartId = e.target.closest('[data-chart-export]').dataset.chartExport;
                const fileName = e.target.closest('[data-chart-export]').dataset.fileName || 'chart';
                window.chartManager.exportChartAsImage(chartId, fileName);
            }
            
            if (e.target.closest('[data-chart-print]')) {
                const chartId = e.target.closest('[data-chart-print]').dataset.chartPrint;
                window.chartManager.printChart(chartId);
            }
        });
    }, 100);
});

// Global functions untuk akses dari HTML
window.exportChart = function(chartId, fileName) {
    if (window.chartManager) {
        window.chartManager.exportChartAsImage(chartId, fileName);
    } else {
        alert('Chart manager belum siap. Silakan tunggu sebentar.');
    }
};

window.printChart = function(chartId) {
    if (window.chartManager) {
        window.chartManager.printChart(chartId);
    } else {
        alert('Chart manager belum siap. Silakan tunggu sebentar.');
    }
};

window.refreshCharts = function() {
    if (window.chartManager) {
        window.chartManager.refreshAllCharts();
        alert('Chart diperbarui!');
    } else {
        alert('Chart manager belum siap.');
    }
};

// Chart default configuration
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif";
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#6c757d';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    
    console.log('Chart.js defaults configured');
}