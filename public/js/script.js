// Neighborhood Watch Fund System - Main JavaScript

// Utility Functions
function formatCurrency(amount) {
    return 'KES ' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.remove(), 5000);
}

// API Calls
const API = {
    getMembers: async (page = 1) => {
        const response = await fetch(`/members?page=${page}`);
        return response.json();
    },
    
    getMember: async (id) => {
        const response = await fetch(`/members/${id}`);
        return response.json();
    },
    
    createMember: async (data) => {
        const response = await fetch('/members/create', {
            method: 'POST',
            body: new FormData(data)
        });
        return response.json();
    },
    
    getPayments: async (page = 1) => {
        const response = await fetch(`/payments?page=${page}`);
        return response.json();
    },
    
    initiatePayment: async (data) => {
        const response = await fetch('/payments/initiate', {
            method: 'POST',
            body: new FormData(data)
        });
        return response.json();
    },
    
    getDashboard: async () => {
        const response = await fetch('/dashboard');
        return response.json();
    },
    
    getStatements: async () => {
        const response = await fetch('/statements');
        return response.json();
    },
    
    getMemberStatement: async (memberId) => {
        const response = await fetch(`/statements/${memberId}`);
        return response.json();
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event listeners
    console.log('App initialized');
});
