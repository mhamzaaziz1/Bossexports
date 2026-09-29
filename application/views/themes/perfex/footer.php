<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <span
                    class="copyright-footer"><?= date('Y'); ?>
                    <?= e(_l('clients_copyright', get_option('companyname'))); ?>
                </span>
                <?php if (is_gdpr() && get_option('gdpr_show_terms_and_conditions_in_footer') == '1') { ?>
                - <a href="<?= terms_url(); ?>"
                    class="terms-and-conditions-footer">
                    <?= _l('terms_and_conditions'); ?>
                </a>
                <?php } ?>
                <?php if (is_gdpr() && is_client_logged_in() && get_option('show_gdpr_link_in_footer') == '1') { ?>
                - <a href="<?= site_url('clients/gdpr'); ?>"
                    class="gdpr-footer">
                    <?= _l('gdpr_short'); ?>
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
</footer>
<?php hooks()->do_action('after_js_scripts_render'); ?>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?= base_url('sw.js'); ?>')
                .then(reg => console.log('Service Worker registered', reg))
                .catch(err => console.log('Service Worker registration failed', err));
        });
    }
</script>
</body>
</html>