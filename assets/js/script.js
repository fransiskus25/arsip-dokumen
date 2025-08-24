/**
 * Sistem Pengarsipan Dokumen - Main JavaScript
 * Handles layout, interactions, and utilities
 */

class LayoutManager {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.backdrop = document.getElementById('sidebarBackdrop');
        this.navbarToggler = document.querySelector('.navbar-toggler');
        this.darkModeToggle = document.getElementById('dark-mode-toggle');
        this.darkModeStyle = document.getElementById('dark-mode-style');
        
        this.init();
    }

    init() {
        this.initSidebar();
        this.initDarkMode();
        this.initResponsiveBehavior();
        this.initTooltips();
        this.initFormValidation();
        this.initSmoothScrolling();
        this.initAutoHideAlerts();
        
        console.log('LayoutManager initialized');
    }

    initSidebar() {
        if (this.navbarToggler && this.sidebar) {
            this.navbarToggler.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleSidebar();
            });
        }

        if (this.backdrop) {
            this.backdrop.addEventListener('click', () => this.hideSidebar());
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && 
                this.sidebar.classList.contains('show') &&
                !this.sidebar.contains(e.target) &&
                !this.navbarToggler.contains(e.target)) {
                this.hideSidebar();
            }
        });
    }

    toggleSidebar() {
        this.sidebar.classList.toggle('show');
        this.backdrop.classList.toggle('show');
        
        if (this.sidebar.classList.contains('show')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    hideSidebar() {
        this.sidebar.classList.remove('show');
        this.backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    initDarkMode() {
        if (this.darkModeToggle && this.darkModeStyle) {
            this.darkModeToggle.addEventListener('click', () => {
                const isDarkMode = this.darkModeStyle.disabled;
                this.darkModeStyle.disabled = !isDarkMode;
                
                document.documentElement.setAttribute('data-bs-theme', !isDarkMode ? 'dark' : 'light');
                localStorage.setItem('darkMode', !isDarkMode);
                
                this.darkModeToggle.innerHTML = `<i class="fas ${!isDarkMode ? 'fa-sun' : 'fa-moon'}"></i>`;
            });
        }
    }

    initResponsiveBehavior() {
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                this.hideSidebar();
            }
        });

        // Close sidebar when menu items are clicked (mobile)
        const menuItems = document.querySelectorAll('.sidebar .nav-link');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    this.hideSidebar();
                }
            });
        });
    }

    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    initSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    initAutoHideAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    }
}

// Utility Functions
class AppUtils {
    static previewPDF(url) {
        const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
        const iframe = document.getElementById('pdfPreviewIframe');
        if (iframe && modal) {
            iframe.src = url;
            modal.show();
        }
    }

    static exportReport(format) {
        const url = new URL(window.location.href);
        url.searchParams.set('export', format);
        window.location.href = url.toString();
    }

    static confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    static showLoading() {
        const loader = document.createElement('div');
        loader.className = 'loading-overlay';
        loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(loader);
    }

    static hideLoading() {
        const loader = document.querySelector('.loading-overlay');
        if (loader) loader.remove();
    }

    static formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    static debounce(func, wait) {
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
}

// Chart Initialization
class ChartManager {
    static initCharts() {
        // Monthly chart
        const monthlyCtx = document.getElementById('monthlyChart');
        if (monthlyCtx) {
            const monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Jumlah Dokumen',
                        data: [],
                        backgroundColor: 'rgba(78, 115, 223, 0.5)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // Category chart
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)',
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize layout manager
    window.layoutManager = new LayoutManager();
    
    // Initialize charts if they exist
    if (typeof Chart !== 'undefined') {
        ChartManager.initCharts();
    }
    
    // Add loading state to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            AppUtils.showLoading();
        });
    });
    
    console.log('Application initialized successfully');
});

// Global functions for HTML onclick attributes
window.previewPDF = AppUtils.previewPDF;
window.exportReport = AppUtils.exportReport;
window.confirmAction = AppUtils.confirmAction;