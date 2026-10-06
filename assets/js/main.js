/* 
 * Online Auditorium Booking System - Master Client-Side JavaScript
 * Handles real-time price calculations, dynamic form fields, and print utilities.
 */

// 1. Real-Time Price Calculator for Booking Page
function calculateBookingTotal(baseRent) {
    let servicesCost = 0;
    const checkboxes = document.querySelectorAll('input[name="services[]"]:checked');
    
    checkboxes.forEach(function(cb) {
        servicesCost += parseFloat(cb.getAttribute('data-price')) || 0;
    });

    const grandTotal = baseRent + servicesCost;

    const displayCostEl = document.getElementById('services_display_cost');
    const displayGrandTotalEl = document.getElementById('grand_total_display');

    if (displayCostEl) {
        displayCostEl.innerText = '₹' + servicesCost.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    if (displayGrandTotalEl) {
        displayGrandTotalEl.innerText = '₹' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

// 2. Dynamic Payment Gateway Mode Switcher (Full vs 50% Advance)
function updatePaymentButtonAmount(fullAmount, advanceAmount, isBalancePaid) {
    if (isBalancePaid) return;
    
    const modes = document.getElementsByName('payment_mode');
    let selectedMode = 'Full';
    for (let m of modes) {
        if (m.checked) selectedMode = m.value;
    }

    const btn = document.getElementById('pay_btn');
    if (btn) {
        if (selectedMode === 'Advance') {
            btn.innerText = 'Pay 50% Advance (₹' + advanceAmount.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ') & Confirm';
        } else {
            btn.innerText = 'Pay Full Amount (₹' + fullAmount.toLocaleString('en-IN', {minimumFractionDigits: 2}) + ') & Confirm';
        }
    }
}

// 3. Toggle Credit/Debit Card Fields depending on Payment Method
function togglePaymentMethodFields() {
    const methodSelect = document.getElementById('payment_method');
    const cardSection = document.getElementById('card_section');
    if (methodSelect && cardSection) {
        if (methodSelect.value.includes('Card')) {
            cardSection.style.display = 'block';
        } else {
            cardSection.style.display = 'none';
        }
    }
}

// 4. Action Confirmation Utility
function confirmDeletion(message) {
    return confirm(message || 'Are you sure you want to proceed with this action?');
}

// 5. Printable Pass / Ticket Utility
function printTicketPass() {
    window.print();
}

// 6. Filter Popover Dropdown Toggle & Auto-Close
function toggleFilterDropdown() {
    const popover = document.getElementById('filterDropdown');
    const btn = document.getElementById('filterToggleBtn');
    if (popover) {
        popover.classList.toggle('show');
        if (btn) btn.classList.toggle('active');
    }
}

document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.search-bar-wrapper');
    const popover = document.getElementById('filterDropdown');
    const btn = document.getElementById('filterToggleBtn');
    if (wrapper && !wrapper.contains(e.target) && popover && popover.classList.contains('show')) {
        popover.classList.remove('show');
        if (btn) btn.classList.remove('active');
    }
});

function resetFiltersInPopover(formId) {
    const form = document.getElementById(formId);
    if (form) {
        const selects = form.querySelectorAll('select');
        selects.forEach(function(s) { s.selectedIndex = 0; });
        const numInputs = form.querySelectorAll('input[type="number"]');
        numInputs.forEach(function(i) { i.value = ''; });
    }
}