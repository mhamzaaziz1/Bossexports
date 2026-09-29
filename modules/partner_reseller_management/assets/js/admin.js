/* Partner & Reseller Management — admin.js */
(function(){
    "use strict";
    // Initialize datatables for any .dt-table that hasn't been initialized
    document.addEventListener('DOMContentLoaded', function(){
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') return;
        jQuery('.dt-table').each(function(){
            if (!jQuery.fn.DataTable.isDataTable(this)) {
                jQuery(this).DataTable({
                    pageLength: 25,
                    order: [],
                    language: { search: 'Filter:' }
                });
            }
        });
    });

    // Inline confirm for clawback / delete
    document.addEventListener('click', function(e){
        var t = e.target.closest('[data-confirm]');
        if (!t) return;
        if (!confirm(t.getAttribute('data-confirm'))) e.preventDefault();
    });
})();
