<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><i class="fa fa-robot"></i> AI Receptionist System Configuration Guide</h4>
                        <hr class="hr-panel-heading" />

                        <!-- Step 1: Webhook & Infrastructure -->
                        <div class="alert alert-info border-left">
                            <h5 class="bold"><i class="fa fa-plug"></i> Step 1: Yeastar PBX & n8n Integration</h5>
                            <p>Connect your Yeastar PBX to the n8n entry point for real-time call tracking.</p>
                            <ul>
                                <li><strong>Webhook URL:</strong> <code><?php echo site_url('bdm_operations/ai_api/incoming_call'); ?></code></li>
                                <li><strong>Yeastar Action:</strong> Go to Settings > Event Center > API Integration. Add a Webhook with <b>HTTP POST</b> pointing to your n8n Production URL.</li>
                                <li><strong>Logged Fields:</strong> Ensure your PBX sends <code>CallerID</code> and <code>DID</code> in the request body.</li>
                            </ul>
                        </div>

                        <!-- Step 2: Speech Processing -->
                        <div class="alert alert-warning border-left">
                            <h5 class="bold"><i class="fa fa-microphone"></i> Step 2: Multilingual STT & TTS (Deepgram & Google)</h5>
                            <p>Configuration for real-time speech-to-text and high-quality voice output.</p>
                            <table class="table table-bordered bg-white">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Model / Voice</th>
                                        <th>Configuration Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Deepgram</strong></td>
                                        <td>Nova-3</td>
                                        <td>Use WebSocket: <code>wss://api.deepgram.com/v1/listen?model=nova-3&language_detection=true</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Google TTS</strong></td>
                                        <td>Neural2</td>
                                        <td>Supports 100+ languages. Profiles are dynamic based on the company lookup table.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Step 3: AI Brain & Intent -->
                        <div class="alert alert-success border-left">
                            <h5 class="bold"><i class="fa fa-brain"></i> Step 3: AI Logic (GPT-4o-mini)</h5>
                            <p>The AI handles the conversation and extracts structured lead data.</p>
                            <div class="well bg-white">
                                <strong>System Prompt Requirements:</strong>
                                <ol>
                                    <li>Company Name & Service List (Dynamic from DB)</li>
                                    <li>Current Time context</li>
                                    <li>Instruction: Ask one question at a time (Name, Service, Urgency, Callback).</li>
                                    <li>Output: Valid JSON wrapped in <code>[LEAD_DATA]</code> tags.</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Step 4: CRM Synchronization -->
                        <div class="alert alert-danger border-left">
                            <h5 class="bold"><i class="fa fa-database"></i> Step 4: CRM API & Retry Queue</h5>
                            <p>Automatic lead creation and failure recovery.</p>
                            <ul>
                                <li><strong>Lead Source:</strong> AI Receptionist (ID 1).</li>
                                <li><strong>Retry Logic:</strong> Any failed API calls are pushed to <code>tblcrm_retry_queue</code> for processing every 5 minutes.</li>
                            </ul>
                        </div>

                        <!-- Step 5: Notifications -->
                        <div class="alert alert-primary border-left">
                            <h5 class="bold"><i class="fa fa-whatsapp"></i> Step 5: WhatsApp Alerts & Admin Reporting</h5>
                            <p>Real-time team notifications and daily 8:00 AM summaries.</p>
                            <ul>
                                <li><strong>Team Alerts:</strong> Sent to WhatsApp Group IDs stored in <code>tblcompany_configs</code>.</li>
                                <li><strong>Daily Report:</strong> Includes Total Calls, Successful Leads, and System Errors.</li>
                            </ul>
                        </div>

                        <!-- n8n Workflow Template -->
                        <hr />
                        <h4 class="bold"><i class="fa fa-code"></i> Master n8n Workflow (JSON)</h4>
                        <p>Copy the JSON below and paste it into your n8n workflow editor (Ctrl+V) to import the entire system at once.</p>
                        <div class="well bg-white">
                            <textarea id="n8n_json" class="form-control" rows="10" readonly><?php echo json_encode([
                                "name" => "AI Receptionist (Master)",
                                "nodes" => [
                                    ["parameters" => ["httpMethod" => "POST", "path" => "yeastar-call"], "name" => "Yeastar Webhook", "type" => "n8n-nodes-base.webhook", "position" => [200, 300]],
                                    ["parameters" => ["method" => "POST", "url" => site_url('bdm_operations/ai_api/incoming_call')], "name" => "Perfex Logger", "type" => "n8n-nodes-base.httpRequest", "position" => [420, 300]],
                                    ["parameters" => ["url" => "wss://api.deepgram.com/v1/listen?model=nova-3"], "name" => "STT (Deepgram)", "type" => "n8n-nodes-base.webSocket", "position" => [640, 300]],
                                    ["parameters" => ["model" => "gpt-4o-mini"], "name" => "AI Brain", "type" => "n8n-nodes-base.openAi", "position" => [860, 300]],
                                    ["parameters" => ["method" => "POST", "url" => site_url('bdm_operations/ai_api/create_lead')], "name" => "Create Lead", "type" => "n8n-nodes-base.httpRequest", "position" => [1080, 300]],
                                    ["parameters" => ["method" => "POST", "url" => site_url('api/whatsapp/send')], "name" => "WhatsApp Alert", "type" => "n8n-nodes-base.httpRequest", "position" => [1300, 300]]
                                ]
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></textarea>
                            <br />
                            <button class="btn btn-primary" onclick="copyToClipboard()"><i class="fa fa-copy"></i> Copy Workflow JSON</button>
                        </div>

                        <script>
                            function copyToClipboard() {
                                var copyText = document.getElementById("n8n_json");
                                copyText.select();
                                copyText.setSelectionRange(0, 99999);
                                document.execCommand("copy");
                                alert("n8n Workflow JSON copied to clipboard!");
                            }
                        </script>

                        <hr />
                        <div class="text-right">
                            <button class="btn btn-info" onclick="window.print()"><i class="fa fa-print"></i> Print Config Guide</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
