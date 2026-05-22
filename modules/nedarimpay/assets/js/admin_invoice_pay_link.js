/* ============================================================================
 * NedarimPay — admin invoice action-toolbar buttons
 *
 * Renders TWO real <button>/<a> elements into the admin invoice preview
 * action toolbar (next to "Send to client" / "Record Payment"):
 *
 *   [Pay with Nedarim Plus]   — primary gradient button, opens the gateway
 *                                pay URL in a new tab
 *   [Copy Link]               — secondary outline button, copies the same
 *                                URL to the clipboard with a toast
 *
 * Both are built client-side from a single hidden <span class="nedarimpay-
 * admin-bigbtn-marker"> the PHP hook stamps somewhere on the page (we
 * don't care where — the marker only carries data attributes).
 *
 * Lives entirely inside the module — no Perfex core CSS / template edits.
 * ========================================================================== */
(function () {
    'use strict';

    var BIG_BTN_ID  = 'nedarimpay-admin-bigbtn';
    var COPY_BTN_ID = 'nedarimpay-admin-copybtn';

    // ─── Clipboard helpers ──────────────────────────────────────────────────
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            try {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'absolute';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                var ok = document.execCommand('copy');
                document.body.removeChild(ta);
                ok ? resolve() : reject(new Error('execCommand failed'));
            } catch (e) { reject(e); }
        });
    }

    function toast(message, type) {
        type = type || 'success';
        if (typeof alert_float === 'function') { alert_float(type, message); return; }
        if (typeof window.toastr !== 'undefined' && window.toastr[type]) {
            window.toastr[type](message); return;
        }
        // Last resort
        try { console.log('[NedarimPay] ' + message); } catch (e) {}
    }

    // ─── DOM builders ───────────────────────────────────────────────────────
    function makeLockIcon(size) {
        size = size || 14;
        var svgNS = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('width', size);
        svg.setAttribute('height', size);
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'currentColor');
        svg.setAttribute('aria-hidden', 'true');
        var p = document.createElementNS(svgNS, 'path');
        p.setAttribute('d', 'M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z');
        svg.appendChild(p);
        return svg;
    }

    function makeCopyIcon(size) {
        size = size || 14;
        var svgNS = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('width', size);
        svg.setAttribute('height', size);
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'currentColor');
        svg.setAttribute('aria-hidden', 'true');
        var p = document.createElementNS(svgNS, 'path');
        p.setAttribute('d', 'M16 1H4a2 2 0 0 0-2 2v14h2V3h12V1zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2zm0 16H8V7h11v14z');
        svg.appendChild(p);
        return svg;
    }

    function makePayButton(payUrl, label) {
        var btn = document.createElement('a');
        btn.id = BIG_BTN_ID;
        btn.href = payUrl;
        btn.target = '_blank';
        btn.rel = 'noopener';
        btn.className = 'btn btn-info mleft5 nedarimpay-admin-bigbtn';
        btn.style.cssText = [
            'background:linear-gradient(135deg,#1a73e8 0%,#1557b0 100%)',
            'border:none',
            'color:#fff',
            'font-weight:600',
            'box-shadow:0 2px 6px rgba(26,115,232,0.25)',
            'display:inline-flex',
            'align-items:center',
            'gap:6px',
            'vertical-align:top',
            'transition:transform .15s ease, box-shadow .15s ease'
        ].join(';');
        btn.appendChild(makeLockIcon(14));
        var span = document.createElement('span');
        span.textContent = label;
        btn.appendChild(span);

        btn.addEventListener('mouseover', function () {
            btn.style.transform = 'translateY(-1px)';
            btn.style.boxShadow = '0 4px 10px rgba(26,115,232,0.35)';
        });
        btn.addEventListener('mouseout', function () {
            btn.style.transform = '';
            btn.style.boxShadow = '0 2px 6px rgba(26,115,232,0.25)';
        });
        return btn;
    }

    function makeCopyButton(payUrl, label, msgCopied, msgFail) {
        var btn = document.createElement('button');
        btn.id = COPY_BTN_ID;
        btn.type = 'button';
        btn.className = 'btn btn-default mleft5 nedarimpay-admin-copybtn';
        btn.style.cssText = [
            'display:inline-flex',
            'align-items:center',
            'gap:6px',
            'vertical-align:top',
            'border:1px solid #d0d5dd',
            'color:#1a73e8',
            'font-weight:500'
        ].join(';');
        btn.appendChild(makeCopyIcon(14));
        var span = document.createElement('span');
        span.textContent = label;
        btn.appendChild(span);

        btn.title = payUrl; // hover shows the full URL
        btn.setAttribute('data-toggle', 'tooltip');
        btn.setAttribute('data-placement', 'bottom');

        btn.addEventListener('click', function (event) {
            event.preventDefault();
            copyToClipboard(payUrl).then(function () {
                toast(msgCopied + ': ' + payUrl, 'success');
            }).catch(function () {
                toast(msgFail, 'warning');
                window.prompt(msgFail, payUrl);
            });
        });
        return btn;
    }

    // ─── Locate toolbar + render ────────────────────────────────────────────
    function findToolbarTargets() {
        // Try increasingly broad selectors so we survive minor template
        // tweaks. The .btn-group containing the "More" dropdown is the
        // ideal insertion point because the existing toolbar buttons all
        // sit next to it.
        var dropdown = document.querySelector(
            '.invoice-preview-wrapper .btn-group .dropdown-toggle'
        ) || document.querySelector('.btn-group > .dropdown-toggle');

        var btnGroup = dropdown ? dropdown.closest('.btn-group') : null;
        var anchor   = btnGroup
                    || document.querySelector('[onclick^="record_payment"]')
                    || document.querySelector('.invoice-send-to-client');

        return { btnGroup: btnGroup, anchor: anchor };
    }

    function buildButtons(marker) {
        if (document.getElementById(BIG_BTN_ID) && document.getElementById(COPY_BTN_ID)) {
            return; // already rendered
        }

        var payUrl    = marker.getAttribute('data-pay-url');
        var labelPay  = marker.getAttribute('data-label-pay')  || 'Pay with Nedarim Plus';
        var labelCopy = marker.getAttribute('data-label-copy') || 'Copy Link';
        var msgCopied = marker.getAttribute('data-msg-copied') || 'Link copied';
        var msgFail   = marker.getAttribute('data-msg-fail')   || 'Copy failed';

        if (!payUrl) {
            return;
        }

        var targets = findToolbarTargets();
        if (!targets.anchor) {
            return; // not on the invoice preview
        }

        var payBtn  = document.getElementById(BIG_BTN_ID)  || makePayButton(payUrl, labelPay);
        var copyBtn = document.getElementById(COPY_BTN_ID) || makeCopyButton(payUrl, labelCopy, msgCopied, msgFail);

        // Order in the toolbar:
        //   [Send] [Pay with Nedarim] [Copy Link] [More ▾] [Record Payment]
        if (targets.btnGroup && targets.btnGroup.parentNode) {
            targets.btnGroup.parentNode.insertBefore(payBtn,  targets.btnGroup);
            targets.btnGroup.parentNode.insertBefore(copyBtn, targets.btnGroup);
        } else if (targets.anchor.parentNode) {
            targets.anchor.parentNode.insertBefore(payBtn,  targets.anchor);
            targets.anchor.parentNode.insertBefore(copyBtn, targets.anchor);
        }
    }

    function init() {
        var marker = document.querySelector('.nedarimpay-admin-bigbtn-marker');
        if (marker) {
            buildButtons(marker);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
