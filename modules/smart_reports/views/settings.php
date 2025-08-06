<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <?php echo render_input('settings[openai_api_key]', 'OpenAI API Key', get_option('openai_api_key'), 'password', ['data-toggle' => 'tooltip', 'title' => 'Enter your OpenAI API key here. You can get one from https://platform.openai.com/api-keys']); ?>
        
        <hr class="hr-panel-separator" />
        
        <div class="alert alert-info">
            <h4>AI-Powered Reports</h4>
            <p>This module uses OpenAI's API to convert natural language queries into SQL for generating reports. To use this feature, you need to provide an OpenAI API key.</p>
            <p><strong>How to get an API key:</strong></p>
            <ol>
                <li>Go to <a href="https://platform.openai.com/signup" target="_blank">https://platform.openai.com/signup</a> and create an account if you don't have one.</li>
                <li>Navigate to <a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a>.</li>
                <li>Click on "Create new secret key" and give it a name (e.g., "Perfex CRM Smart Reports").</li>
                <li>Copy the generated API key and paste it in the field above.</li>
            </ol>
            <p><strong>Note:</strong> OpenAI API usage is not free and will be billed according to your usage. Please check <a href="https://openai.com/pricing" target="_blank">OpenAI's pricing</a> for more information.</p>
        </div>
    </div>
</div>