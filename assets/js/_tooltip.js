// Dans `_tooltip.js`
function initTooltips() {
    if (window.bootstrap && window.bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new window.bootstrap.Tooltip(tooltipTriggerEl);
        });
    } else {
        console.error('Bootstrap is not available');
        // réessayer l'initialisation plus tard
        setTimeout(initTooltips, 1000);
    }
}

document.addEventListener('DOMContentLoaded', initTooltips);
document.addEventListener('turbo:load', initTooltips);