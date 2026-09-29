<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold">
                            <i class="fa fa-cogs text-info mright5"></i>
                            Inquiry Settings
                        </h4>
                        <hr class="hr-panel-heading" />
                        <p class="text-muted mbot15">
                            Configure the default templates for product inquiries sent to vendors via Email and SMS.
                            Use merge fields to dynamically insert product and vendor information.
                        </p>

                        <!-- Merge Fields Reference -->
                        <div class="panel panel-default mbot20">
                            <div class="panel-heading" style="cursor:pointer;" data-toggle="collapse" data-target="#merge-fields-help">
                                <strong><i class="fa fa-info-circle text-info mright5"></i>Available Merge Fields</strong>
                                <span class="pull-right"><i class="fa fa-chevron-down"></i></span>
                            </div>
                            <div class="panel-body collapse" id="merge-fields-help">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-condensed table-bordered no-margin">
                                            <thead>
                                                <tr><th>Field</th><th>Description</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr><td><code>{item_name}</code></td><td>Product / Item name</td></tr>
                                                <tr><td><code>{item_sku}</code></td><td>Product SKU</td></tr>
                                                <tr><td><code>{vendor_name}</code></td><td>Vendor company name</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-condensed table-bordered no-margin">
                                            <thead>
                                                <tr><th>Field</th><th>Description</th></tr>
                                            </thead>
                                            <tbody>
                                                <tr><td><code>{vendor_code}</code></td><td>Vendor's item code/SKU</td></tr>
                                                <tr><td><code>{sender_name}</code></td><td>Staff member who sent</td></tr>
                                                <tr><td><code>{company_name}</code></td><td>Your company name</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php echo form_open(admin_url('vendor_sourcing/inquiry_settings')); ?>

                        <!-- EMAIL TEMPLATE SECTION -->
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:4px 4px 0 0;">
                                <strong><i class="fa fa-envelope mright5"></i>Email Template</strong>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="vs_inquiry_email_subject" class="control-label">
                                        <i class="fa fa-pencil mright5"></i>Default Subject Line
                                    </label>
                                    <input type="text" name="vs_inquiry_email_subject" id="vs_inquiry_email_subject"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($email_subject); ?>"
                                        placeholder="Product Inquiry: {item_name}">
                                    <small class="text-muted">You can use merge fields like <code>{item_name}</code>, <code>{vendor_name}</code></small>
                                </div>

                                <div class="form-group">
                                    <label for="vs_inquiry_email_template" class="control-label">
                                        <i class="fa fa-file-text mright5"></i>Default Email Body
                                    </label>
                                    <textarea name="vs_inquiry_email_template" id="vs_inquiry_email_template"
                                        class="form-control" rows="12"
                                        placeholder="Enter your email template here..."><?php echo htmlspecialchars($email_template); ?></textarea>
                                    <small class="text-muted">HTML is supported. The body can be modified per inquiry before sending.</small>
                                </div>

                                <!-- Email Preview -->
                                <div class="well well-sm" id="email-preview-container">
                                    <label class="control-label text-muted"><i class="fa fa-eye mright5"></i>Live Preview</label>
                                    <div id="email-preview" style="background:#fff;border:1px solid #e8ecf5;border-radius:6px;padding:15px;min-height:80px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- SMS TEMPLATE SECTION -->
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%);color:#fff;border-radius:4px 4px 0 0;">
                                <strong><i class="fa fa-comment mright5"></i>SMS Template</strong>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label for="vs_inquiry_sms_template" class="control-label">
                                        <i class="fa fa-comment mright5"></i>Default SMS Message
                                    </label>
                                    <textarea name="vs_inquiry_sms_template" id="vs_inquiry_sms_template"
                                        class="form-control" rows="4" maxlength="500"
                                        placeholder="Hi {vendor_name}, inquiry for {item_name}..."><?php echo htmlspecialchars($sms_template); ?></textarea>
                                    <small class="text-muted">
                                        <span id="sms-char-count">0</span>/500 characters.
                                        Keep SMS messages concise.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- SMS GATEWAY SECTION -->
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:linear-gradient(135deg,#f093fb 0%,#f5576c 100%);color:#fff;border-radius:4px 4px 0 0;">
                                <strong><i class="fa fa-server mright5"></i>SMS Gateway Configuration</strong>
                            </div>
                            <div class="panel-body">
                                <p class="text-muted mbot15">
                                    <i class="fa fa-info-circle mright5"></i>
                                    Configure your SMS gateway API credentials. The system will POST to the API URL
                                    with parameters: <code>api_key</code>, <code>sender_id</code>, <code>to</code>, <code>message</code>.
                                </p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vs_inquiry_sms_api_url" class="control-label">API URL</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="fa fa-link"></i></div>
                                                <input type="url" name="vs_inquiry_sms_api_url" id="vs_inquiry_sms_api_url"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($sms_api_url); ?>"
                                                    placeholder="https://api.smsprovider.com/send">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vs_inquiry_sms_api_key" class="control-label">API Key</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="fa fa-key"></i></div>
                                                <input type="password" name="vs_inquiry_sms_api_key" id="vs_inquiry_sms_api_key"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($sms_api_key); ?>"
                                                    placeholder="Your API key">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vs_inquiry_sms_sender_id" class="control-label">Sender ID</label>
                                            <div class="input-group">
                                                <div class="input-group-addon"><i class="fa fa-id-badge"></i></div>
                                                <input type="text" name="vs_inquiry_sms_sender_id" id="vs_inquiry_sms_sender_id"
                                                    class="form-control"
                                                    value="<?php echo htmlspecialchars($sms_sender_id); ?>"
                                                    placeholder="MYCOMPANY">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fa fa-check mright5"></i>Save Inquiry Settings
                            </button>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
$(function(){
    // Live email preview
    function updateEmailPreview(){
        var html = $('#vs_inquiry_email_template').val();
        // Replace merge fields with sample data for preview
        var samples = {
            '{item_name}': '<strong style="color:#667eea;">Sample Widget</strong>',
            '{item_sku}': '<strong>SKU-12345</strong>',
            '{vendor_name}': '<strong style="color:#11998e;">Acme Corp</strong>',
            '{vendor_code}': '<strong>VC-001</strong>',
            '{sender_name}': '<strong>John Doe</strong>',
            '{company_name}': '<strong><?php echo htmlspecialchars(get_option('companyname')); ?></strong>'
        };
        for(var k in samples){ html = html.split(k).join(samples[k]); }
        $('#email-preview').html(html || '<em class="text-muted">No template content yet...</em>');
    }
    $('#vs_inquiry_email_template').on('input', updateEmailPreview);
    updateEmailPreview();

    // SMS character count
    function updateSmsCount(){
        var len = $('#vs_inquiry_sms_template').val().length;
        $('#sms-char-count').text(len);
        if(len > 160) {
            $('#sms-char-count').css('color', '#e74c3c');
        } else {
            $('#sms-char-count').css('color', '#2ecc71');
        }
    }
    $('#vs_inquiry_sms_template').on('input', updateSmsCount);
    updateSmsCount();
});
</script>
</body>
</html>
