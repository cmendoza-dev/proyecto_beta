import Alpine from 'alpinejs';
import Swal from 'sweetalert2';

// Importar módulos de documentos
import './modules/documentService.js';
import './modules/attendanceScanner.js';

// Make Alpine available globally
window.Alpine = Alpine;
window.Swal = Swal;
// Start Alpine
Alpine.start();

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Auto focus on DNI input in attendance register
    const dniInput = document.getElementById('dni');
    if (dniInput) {
        dniInput.focus();
    }

    // Handle barcode scanner input from USB devices
    let barcodeBuffer = '';
    let barcodeTimeout = null;

    document.addEventListener('keypress', function(e) {
        // Check if we're in an input field (except DNI field)
        if (e.target.tagName === 'INPUT' && e.target.id !== 'dni') {
            return;
        }

        // Only process if we're on attendance register page
        if (!window.location.pathname.includes('/attendance/register')) {
            return;
        }

        clearTimeout(barcodeTimeout);

        // Add character to buffer
        if (e.key !== 'Enter') {
            barcodeBuffer += e.key;
        }

        // Set timeout to clear buffer if no more input
        barcodeTimeout = setTimeout(() => {
            // If buffer is 8 digits, it's likely a DNI
            if (/^\d{8}$/.test(barcodeBuffer)) {
                const dniField = document.getElementById('dni');
                if (dniField) {
                    dniField.value = barcodeBuffer;
                    // Auto submit the form
                    const form = document.getElementById('attendance-form');
                    if (form) {
                        form.submit();
                    }
                }
            }
            barcodeBuffer = '';
        }, 100);
    });
});

// Confirm before leaving page with unsaved changes
window.addEventListener('beforeunload', function (e) {
    const forms = document.querySelectorAll('form[data-confirm-leave]');
    for (let form of forms) {
        if (form.dataset.changed === 'true') {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    }
});

// Mark forms as changed when inputs change
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-confirm-leave]');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                form.dataset.changed = 'true';
            });
        });

        form.addEventListener('submit', function() {
            form.dataset.changed = 'false';
        });
    });
});
