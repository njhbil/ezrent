// EzRent Main JavaScript
class EzRent {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuthStatus();
    }

    setupEventListeners() {
        // Search functionality
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', this.handleSearch.bind(this));
        }

        // Date validation
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        if (startDate && endDate) {
            this.setupDateValidation(startDate, endDate);
        }
    }

    handleSearch(e) {
        e.preventDefault();
        const searchTerm = document.getElementById('searchInput').value;
        if (searchTerm) {
            window.location.href = `vehicles.php?search=${encodeURIComponent(searchTerm)}`;
        }
    }

    setupDateValidation(startDate, endDate) {
        const today = new Date().toISOString().split('T')[0];
        startDate.min = today;
        endDate.min = today;

        startDate.addEventListener('change', function() {
            if (this.value) {
                endDate.min = this.value;
            }
        });
    }

    checkAuthStatus() {
        // Check if user is logged in (simplified)
        const authElements = document.querySelectorAll('[data-auth]');
        authElements.forEach(element => {
            const requiresAuth = element.getAttribute('data-auth') === 'required';
            // In real app, check session/token
        });
    }

    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.prepend(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    formatPrice(price) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(price);
    }
}

// Initialize EzRent when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.ezrent = new EzRent();
});