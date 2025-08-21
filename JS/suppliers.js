// Suppliers Management JavaScript Functions

// Global variables
let currentSupplierID = null;
let availableItems = [];
let selectedItems = [];
let isViewMode = false;

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event listeners
    initializeEventListeners();
});

function initializeEventListeners() {
    // Modal close events
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            closeSupplierModal();
        }
    });

    // Form validation
    const form = document.getElementById('supplierForm');
    if (form) {
        form.addEventListener('submit', validateForm);
    }

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            filterSuppliers(this.value);
        }, 300));
    }
}

// Debounce function for search
function debounce(func, wait) {
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

// Form validation
function validateForm(e) {
    e.preventDefault();
    
    const requiredFields = ['supplierName', 'type', 'phone'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (!field || !field.value.trim()) {
            isValid = false;
            field.classList.add('error');
            showFieldError(field, 'هذا الحقل مطلوب');
        } else {
            field.classList.remove('error');
            hideFieldError(field);
        }
    });
    
    // Email validation (only if provided)
    const emailField = document.getElementById('email');
    if (emailField && emailField.value.trim()) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailField.value)) {
            isValid = false;
            emailField.classList.add('error');
            showFieldError(emailField, 'البريد الإلكتروني غير صحيح');
        } else {
            emailField.classList.remove('error');
            hideFieldError(emailField);
        }
    }
    
    // Phone validation
    const phoneField = document.getElementById('phone');
    if (phoneField && phoneField.value) {
        const phonePattern = /^[0-9+\-\s]+$/;
        if (!phonePattern.test(phoneField.value)) {
            isValid = false;
            phoneField.classList.add('error');
            showFieldError(phoneField, 'رقم الهاتف غير صحيح');
        }
    }
    
    if (isValid) {
        submitSupplierForm();
    }
}

function showFieldError(field, message) {
    let errorElement = field.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('field-error')) {
        errorElement = document.createElement('span');
        errorElement.className = 'field-error';
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }
    errorElement.textContent = message;
    errorElement.style.color = '#e74c3c';
    errorElement.style.fontSize = '0.8rem';
    errorElement.style.display = 'block';
}

function hideFieldError(field) {
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains('field-error')) {
        errorElement.style.display = 'none';
    }
}

function submitSupplierForm() {
    const formData = new FormData(document.getElementById('supplierForm'));
    const action = document.getElementById('supplierID').value ? 'update_supplier' : 'add_supplier';
    formData.append('action', action);
    
    // Show loading state
    const submitButton = document.querySelector('.btn-primary');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> جاري الحفظ...';
    submitButton.disabled = true;
    
    fetch('suppliers_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                showNotification(data.message, 'success');
                if (action === 'add_supplier') {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Save items if editing
                    if (currentSupplierID && selectedItems.length > 0) {
                        saveSupplierItems();
                    } else {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                }
            } else {
                showNotification(data.message, 'error');
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text);
            showNotification('خطأ في تحليل استجابة الخادم', 'error');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showNotification('خطأ في الاتصال بالخادم', 'error');
    })
    .finally(() => {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fa fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}"></i>
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#2ecc71' : type === 'error' ? '#e74c3c' : '#3498db'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1rem;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOut 0.3s ease-out forwards';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        margin-left: 10px;
    }
    
    .field-error {
        display: block;
        margin-top: 5px;
        font-size: 0.8rem;
        color: #e74c3c;
    }
    
    .form-group input.error,
    .form-group select.error,
    .form-group textarea.error {
        border-color: #e74c3c;
        box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.2);
    }
    
    .items-tree {
        margin-bottom: 15px;
    }
    
    .group-header.expanded .fa-chevron-right {
        transform: rotate(90deg);
    }
    
    .group-content {
        transition: max-height 0.3s ease-out;
        overflow: hidden;
    }
    
    .group-content.collapsed {
        max-height: 0;
    }
    
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .item-count {
        background: rgba(52, 152, 219, 0.2);
        color: #3498db;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-right: 5px;
    }
`;
document.head.appendChild(style);

// Enhanced items tree functionality
function createCollapsibleTree() {
    const groupHeaders = document.querySelectorAll('.group-header, .subgroup-header');
    
    groupHeaders.forEach(header => {
        if (!header.querySelector('.fa-chevron-right')) {
            const chevron = document.createElement('i');
            chevron.className = 'fa fa-chevron-right';
            chevron.style.transition = 'transform 0.3s ease';
            chevron.style.marginRight = '5px';
            header.insertBefore(chevron, header.firstChild);
        }
        
        header.style.cursor = 'pointer';
        header.addEventListener('click', function(e) {
            if (e.target.type === 'checkbox') return;
            
            const content = this.nextElementSibling;
            const chevron = this.querySelector('.fa-chevron-right');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                chevron.style.transform = 'rotate(90deg)';
            } else {
                content.style.display = 'none';
                chevron.style.transform = 'rotate(0deg)';
            }
        });
    });
}

// Enhanced item counter
function updateItemCounts() {
    const mainGroups = document.querySelectorAll('.group-header');
    
    mainGroups.forEach(groupHeader => {
        const groupName = groupHeader.textContent.trim();
        const groupContent = groupHeader.nextElementSibling;
        const selectedCount = groupContent.querySelectorAll('input[type="checkbox"]:checked').length;
        const totalCount = groupContent.querySelectorAll('input[type="checkbox"]').length;
        
        let countElement = groupHeader.querySelector('.item-count');
        if (!countElement) {
            countElement = document.createElement('span');
            countElement.className = 'item-count';
            groupHeader.appendChild(countElement);
        }
        
        countElement.textContent = `${selectedCount}/${totalCount}`;
    });
}

// Export functions to global scope
window.showNotification = showNotification;
window.updateItemCounts = updateItemCounts;
window.createCollapsibleTree = createCollapsibleTree;