// TOUT LE JS EST DÉSACTIVÉ POUR TESTER LA SOUMISSION CLASSIQUE DU FORMULAIRE
// document.addEventListener('DOMContentLoaded', function() {
//     // Initialize tooltips
//     var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
//     var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
//         return new bootstrap.Tooltip(tooltipTriggerEl);
//     });

//     // Initialize popovers
//     var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
//     var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
//         return new bootstrap.Popover(popoverTriggerEl);
//     });

//     // Auto-hide alerts after 5 seconds
//     setTimeout(function() {
//         var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
//         alerts.forEach(function(alert) {
//             var bsAlert = new bootstrap.Alert(alert);
//             bsAlert.close();
//         });
//     }, 5000);

//     // Smooth scrolling for anchor links
//     document.querySelectorAll('a[href^="#"]').forEach(anchor => {
//         anchor.addEventListener('click', function (e) {
//             e.preventDefault();
//             const target = document.querySelector(this.getAttribute('href'));
//             if (target) {
//                 target.scrollIntoView({
//                     behavior: 'smooth',
//                     block: 'start'
//                 });
//             }
//         });
//     });

//     // Form validation enhancement
//     var forms = document.querySelectorAll('.needs-validation');
//     Array.prototype.slice.call(forms).forEach(function(form) {
//         form.addEventListener('submit', function(event) {
//             if (!form.checkValidity()) {
//                 event.preventDefault();
//                 event.stopPropagation();
                
//                 // Focus on first invalid field
//                 var firstInvalid = form.querySelector(':invalid');
//                 if (firstInvalid) {
//                     firstInvalid.focus();
//                 }
//             }
//             form.classList.add('was-validated');
//         }, false);
//     });

//     // Loading states for buttons
//     document.querySelectorAll('button[type="submit"]').forEach(button => {
//         button.addEventListener('click', function() {
//             const form = this.closest('form');
//             if (form && form.checkValidity()) {
//                 this.disabled = true;
//                 const originalText = this.innerHTML;
//                 this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...';
                
//                 // Re-enable after 10 seconds as fallback
//                 setTimeout(() => {
//                     this.disabled = false;
//                     this.innerHTML = originalText;
//                 }, 10000);
//             }
//         });
//     });

//     // Table search functionality
//     const searchInputs = document.querySelectorAll('.table-search');
//     searchInputs.forEach(input => {
//         input.addEventListener('keyup', function() {
//             const searchTerm = this.value.toLowerCase();
//             const tableId = this.getAttribute('data-table');
//             const table = document.getElementById(tableId);
            
//             if (table) {
//                 const rows = table.querySelectorAll('tbody tr');
//                 rows.forEach(row => {
//                     const text = row.textContent.toLowerCase();
//                     row.style.display = text.includes(searchTerm) ? '' : 'none';
//                 });
//             }
//         });
//     });

//     // Confirmation dialogs
//     document.querySelectorAll('[data-confirm]').forEach(element => {
//         element.addEventListener('click', function(e) {
//             const message = this.getAttribute('data-confirm');
//             if (!confirm(message)) {
//                 e.preventDefault();
//                 return false;
//             }
//         });
//     });

//     // Auto-refresh for real-time data
//     if (document.querySelector('[data-auto-refresh]')) {
//         setInterval(function() {
//             // Only refresh if page is visible
//             if (!document.hidden) {
//                 location.reload();
//             }
//         }, 30000); // Refresh every 30 seconds
//     }

//     // Copy to clipboard functionality
//     document.querySelectorAll('.copy-to-clipboard').forEach(button => {
//         button.addEventListener('click', function() {
//             const text = this.getAttribute('data-clipboard-text');
//             navigator.clipboard.writeText(text).then(() => {
//                 // Show success feedback
//                 const originalText = this.innerHTML;
//                 this.innerHTML = '<i class="bi bi-check"></i> Copied!';
//                 this.classList.add('btn-success');
                
//                 setTimeout(() => {
//                     this.innerHTML = originalText;
//                     this.classList.remove('btn-success');
//                 }, 2000);
//             });
//         });
//     });

//     // Progress bar animations
//     document.querySelectorAll('.progress-bar').forEach(bar => {
//         const width = bar.style.width;
//         bar.style.width = '0%';
//         setTimeout(() => {
//             bar.style.width = width;
//         }, 100);
//     });

//     // Card flip animations
//     document.querySelectorAll('.card-flip').forEach(card => {
//         card.addEventListener('click', function() {
//             this.classList.toggle('flipped');
//         });
//     });

//     // Lazy loading for images
//     if ('IntersectionObserver' in window) {
//         const imageObserver = new IntersectionObserver((entries, observer) => {
//             entries.forEach(entry => {
//                 if (entry.isIntersecting) {
//                     const img = entry.target;
//                     img.src = img.dataset.src;
//                     img.classList.remove('lazy');
//                     imageObserver.unobserve(img);
//                 }
//             });
//         });

//         document.querySelectorAll('img[data-src]').forEach(img => {
//             imageObserver.observe(img);
//         });
//     }

//     // Dark mode toggle (if implemented)
//     const darkModeToggle = document.getElementById('darkModeToggle');
//     if (darkModeToggle) {
//         darkModeToggle.addEventListener('click', function() {
//             document.body.classList.toggle('dark-mode');
//             localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
//         });

//         // Load saved dark mode preference
//         if (localStorage.getItem('darkMode') === 'true') {
//             document.body.classList.add('dark-mode');
//         }
//     }

//     // Statistics counter animation
//     function animateCounters() {
//         document.querySelectorAll('.counter').forEach(counter => {
//             const target = parseInt(counter.getAttribute('data-target'));
//             const duration = 2000; // 2 seconds
//             const step = target / (duration / 16); // 60fps
//             let current = 0;

//             const timer = setInterval(() => {
//                 current += step;
//                 if (current >= target) {
//                     current = target;
//                     clearInterval(timer);
//                 }
//                 counter.textContent = Math.floor(current);
//             }, 16);
//         });
//     }

//     // Trigger counter animation when elements are visible
//     const counterObserver = new IntersectionObserver((entries) => {
//         entries.forEach(entry => {
//             if (entry.isIntersecting) {
//                 animateCounters();
//                 counterObserver.unobserve(entry.target);
//             }
//         });
//     });

//     document.querySelectorAll('.counter').forEach(counter => {
//         counterObserver.observe(counter);
//     });
// });

// Code désactivé temporairement pour diagnostic du formulaire d'inscription
// Utility functions
// Theme handling
(function() {
    function setTheme(theme) {
        try {
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.body && document.body.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            const icon = document.getElementById('themeToggleIcon');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
            }
        } catch (e) { /* no-op */ }
    }

    function initTheme() {
        try {
            const saved = localStorage.getItem('theme');
            const theme = saved ? saved : 'light';
            setTheme(theme);
        } catch(e) { /* no-op */ }
    }

    function attachToggleHandler() {
        const toggle = document.getElementById('themeToggle');
        if (toggle && !toggle._themeBound) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const current = (document.documentElement.getAttribute('data-bs-theme') || 'light').toLowerCase();
                setTheme(current === 'light' ? 'dark' : 'light');
            });
            toggle._themeBound = true;
        }
    }

    function boot() {
        initTheme();
        attachToggleHandler();
    }

    // Run ASAP
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        // DOM already ready
        boot();
    }

    // Fallback: also handle clicks globally
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'themeToggle' || e.target.closest && e.target.closest('#themeToggle'))) {
            const current = (document.documentElement.getAttribute('data-bs-theme') || 'light').toLowerCase();
            setTheme(current === 'light' ? 'dark' : 'light');
        }
    });

    // Expose for debugging and inline handlers
    window.setTheme = setTheme;
    window.toggleTheme = function() {
        const current = (document.documentElement.getAttribute('data-bs-theme') || 'light').toLowerCase();
        setTheme(current === 'light' ? 'dark' : 'light');
    };
})();

window.EcheckinEvent = {
    // Show notification
    showNotification: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-fluid') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alert = new bootstrap.Alert(alertDiv);
            alert.close();
        }, 5000);
    },

    // Format date
    formatDate: function(date, format = 'short') {
        const options = format === 'short' 
            ? { year: 'numeric', month: 'short', day: 'numeric' }
            : { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        
        return new Intl.DateTimeFormat('en-US', options).format(new Date(date));
    },

    // Validate email
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Generate QR code URL
    generateQrCodeUrl: function(data, size = 200) {
        return `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(data)}`;
    },

    // Export table to CSV
    exportTableToCSV: function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tr'));
        const csv = rows.map(row => {
            const cells = Array.from(row.querySelectorAll('th, td'));
            return cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
        }).join('\n');

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    }
};