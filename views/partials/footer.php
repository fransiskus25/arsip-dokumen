            </div><!-- End content-area -->
        </main><!-- End main-content -->
    </div><!-- End main-wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
    
    <script>
    // Enhanced Layout Management
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const navbarToggler = document.querySelector('.navbar-toggler');
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const darkModeStyle = document.getElementById('dark-mode-style');
        
        // Mobile sidebar toggle
        if (navbarToggler && sidebar) {
            navbarToggler.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
                
                // Prevent body scroll when sidebar is open
                if (sidebar.classList.contains('show')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        }
        
        // Close sidebar when clicking backdrop
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992 && 
                sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                !navbarToggler.contains(e.target)) {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
        
        // Dark mode toggle
        if (darkModeToggle && darkModeStyle) {
            darkModeToggle.addEventListener('click', function() {
                const isDarkMode = darkModeStyle.disabled;
                darkModeStyle.disabled = !isDarkMode;
                
                // Update theme attribute
                document.documentElement.setAttribute('data-bs-theme', !isDarkMode ? 'dark' : 'light');
                
                // Save preference
                localStorage.setItem('darkMode', !isDarkMode);
                document.cookie = `dark_mode=${!isDarkMode}; path=/; max-age=31536000`;
                
                // Update icon
                this.innerHTML = `<i class="fas ${!isDarkMode ? 'fa-sun' : 'fa-moon'}"></i>`;
            });
        }
        
        // Auto-hide alerts
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade');
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 300);
            }, 5000);
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        console.log('Layout initialized successfully');
    });

    // Utility functions
    function previewPDF(url) {
        const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
        const iframe = document.getElementById('pdfPreviewIframe');
        if (iframe && modal) {
            iframe.src = url;
            modal.show();
        }
    }

    function exportReport(format) {
        const url = new URL(window.location.href);
        url.searchParams.set('export', format);
        window.location.href = url.toString();
    }

    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    function showLoading() {
        const loader = document.createElement('div');
        loader.className = 'loading-overlay';
        loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(loader);
    }

    function hideLoading() {
        const loader = document.querySelector('.loading-overlay');
        if (loader) {
            loader.remove();
        }
    }
    </script>
</body>
</html>