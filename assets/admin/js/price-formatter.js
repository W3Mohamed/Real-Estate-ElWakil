// assets/admin/js/price-formatter.js
document.addEventListener('DOMContentLoaded', function() {
    console.log('Price formatter script loaded');
    const priceInputs = document.querySelectorAll('.price-input');
    
    priceInputs.forEach(input => {
        const targetId = input.getAttribute('data-target');
        const targetElement = document.createElement('div');
        targetElement.id = targetId;
        targetElement.className = 'formatted-price';
        input.parentNode.appendChild(targetElement);
        
        input.addEventListener('input', function() {
            const value = this.value;
            fetch('/admin/format-price?price=' + value)
                .then(response => response.text())
                .then(formatted => {
                    targetElement.textContent = formatted;
                });
        });
    });
});