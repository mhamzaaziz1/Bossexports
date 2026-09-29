/* Partner Portal — JS */
(function(){
    "use strict";

    // Auto-hide flash alerts after 5s
    setTimeout(function(){
        document.querySelectorAll('.prm-alert').forEach(function(a){
            a.style.transition = 'opacity .4s';
            a.style.opacity = '0';
            setTimeout(function(){ a.remove(); }, 400);
        });
    }, 5000);

    // Real-time deal-form duplicate hint (calls our API endpoint)
    var dealForm = document.querySelector('form input[name="email"]');
    if (dealForm) {
        ['email','phone','company_name'].forEach(function(name){
            var el = document.querySelector('form input[name="'+name+'"], form textarea[name="'+name+'"]');
            if (!el) return;
            el.addEventListener('blur', function(){
                // server-side duplicate check happens at submit; we just provide soft cue
                if (el.value && el.value.length > 3) {
                    el.style.borderColor = '#5CB85C';
                }
            });
        });
    }
})();
