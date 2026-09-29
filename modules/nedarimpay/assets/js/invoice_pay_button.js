/* ============================================================================
 * NedarimPay — invoice-preview pay button injection
 *
 * Runs on the client-area invoice page only. Builds a "Pay with Nedarim Plus"
 * button that opens /nedarimpay/gateway/pay?invoiceid=...&hash=... in a new
 * tab. The invoice id and hash come from data attributes the module's PHP
 * hook stamps into the page — we never parse the DOM blindly.
 *
 * If the data attributes are missing, the script is a no-op. That way the
 * module can also be installed without the JS hook firing (e.g. on admin
 * pages where it doesn't belong).
 * ========================================================================== */
(function () {
    'use strict';

    function init() {
        var marker = document.getElementById('nedarimpay-invoice-marker');
        if (!marker) {
            return; // not on a customer invoice page — nothing to do
        }

        var invoiceId   = marker.getAttribute('data-invoice-id');
        var invoiceHash = marker.getAttribute('data-invoice-hash');
        var payUrlBase  = marker.getAttribute('data-pay-url');
        var balanceDue  = parseFloat(marker.getAttribute('data-balance-due') || '0');
        var i18nLabel   = marker.getAttribute('data-label-pay')
                          || 'Pay with Nedarim Plus';

        if (!invoiceId || !invoiceHash || !payUrlBase) {
            return;
        }

        // If the invoice is already fully paid, don't show the button.
        if (balanceDue <= 0) {
            return;
        }

        // Already rendered? (covers double-include / hot reload edge cases)
        if (document.getElementById('nedarimpay-pay-btn-wrap')) {
            return;
        }

        // Build the URL once
        var payUrl = payUrlBase
                   + (payUrlBase.indexOf('?') === -1 ? '?' : '&')
                   + 'invoiceid=' + encodeURIComponent(invoiceId)
                   + '&hash='     + encodeURIComponent(invoiceHash);

        // Build the button container
        var wrap = document.createElement('div');
        wrap.id = 'nedarimpay-pay-btn-wrap';
        wrap.style.cssText = 'margin:18px 0;text-align:center;';

        var btn = document.createElement('a');
        btn.id = 'nedarimpay-pay-btn';
        btn.href = payUrl;
        btn.target = '_self';
        btn.rel = 'noopener';
        btn.textContent = i18nLabel;
        btn.style.cssText = [
            'display:inline-flex',
            'align-items:center',
            'gap:8px',
            'padding:11px 22px',
            'background:linear-gradient(135deg,#1a73e8 0%,#1557b0 100%)',
            'color:#fff',
            'font-weight:600',
            'font-size:15px',
            'border-radius:6px',
            'text-decoration:none',
            'box-shadow:0 2px 6px rgba(26,115,232,0.25)',
            'transition:transform .15s ease, box-shadow .15s ease',
            'cursor:pointer'
        ].join(';');

        // Lock icon
        var icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        icon.setAttribute('width', '16');
        icon.setAttribute('height', '16');
        icon.setAttribute('viewBox', '0 0 24 24');
        icon.setAttribute('fill', 'currentColor');
        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', 'M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z');
        icon.appendChild(path);
        btn.prepend(icon);

        btn.addEventListener('mouseover', function () {
            btn.style.transform = 'translateY(-1px)';
            btn.style.boxShadow = '0 4px 10px rgba(26,115,232,0.35)';
        });
        btn.addEventListener('mouseout', function () {
            btn.style.transform = '';
            btn.style.boxShadow = '0 2px 6px rgba(26,115,232,0.25)';
        });

        wrap.appendChild(btn);

        // Insert: prefer the marker's parent so the position is predictable;
        // fall back to appending to the marker itself.
        var anchor = marker.parentNode || marker;
        if (anchor === marker) {
            marker.appendChild(wrap);
        } else {
            marker.insertAdjacentElement('afterend', wrap);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
