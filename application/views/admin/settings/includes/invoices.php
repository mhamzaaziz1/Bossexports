<div class="form-group">
    <label class="control-label"
        for="invoice_prefix"><?= _l('settings_sales_invoice_prefix'); ?></label>
    <input type="text" name="settings[invoice_prefix]" class="form-control"
        value="<?= get_option('invoice_prefix'); ?>">
</div>
<hr />
<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
    data-title="<?= _l('settings_sales_next_invoice_number_tooltip'); ?>"></i>
<?= render_input('settings[next_invoice_number]', 'settings_sales_next_invoice_number', get_option('next_invoice_number'), 'number', ['min' => 1]); ?>
<hr />
<i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
    data-title="<?= _l('invoice_due_after_help'); ?>"></i>
<?= render_input('settings[invoice_due_after]', 'settings_sales_invoice_due_after', get_option('invoice_due_after')); ?>
<hr />
<?= render_yes_no_option('allow_staff_view_invoices_assigned', 'allow_staff_view_invoices_assigned'); ?>
<hr />
<?php render_yes_no_option('view_invoice_only_logged_in', 'settings_sales_require_client_logged_in_to_view_invoice'); ?>
<hr />
<?php render_yes_no_option('delete_only_on_last_invoice', 'settings_delete_only_on_last_invoice'); ?>
<hr />
<?php render_yes_no_option('invoice_number_decrement_on_delete', 'settings_sales_decrement_invoice_number_on_delete', 'settings_sales_decrement_invoice_number_on_delete_tooltip'); ?>
<hr />
<?php render_yes_no_option('exclude_invoice_from_client_area_with_draft_status', 'exclude_invoices_draft_from_client_area'); ?>
<hr />
<?php render_yes_no_option('show_sale_agent_on_invoices', 'settings_show_sale_agent_on_invoices'); ?>
<hr />
<?php render_yes_no_option('show_project_on_invoice', 'show_project_on_invoice'); ?>
<hr />
<?php render_yes_no_option('show_total_paid_on_invoice', 'show_total_paid_on_invoice'); ?>
<hr />
<?php render_yes_no_option('show_credits_applied_on_invoice', 'show_credits_applied_on_invoice'); ?>
<hr />
<?php render_yes_no_option('show_amount_due_on_invoice', 'show_amount_due_on_invoice'); ?>
<hr />
<?php render_yes_no_option('attach_invoice_to_payment_receipt_email', 'attach_invoice_to_payment_receipt_email'); ?>
<hr />
<div class="form-group">
    <label for="invoice_number_format"
        class="control-label clearfix"><?= _l('settings_sales_invoice_number_format'); ?></label>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="number_based" name="settings[invoice_number_format]" value="1"
            <?= get_option('invoice_number_format') == '1' ? 'checked' : '' ?>>
        <label
            for="number_based"><?= _l('settings_sales_invoice_number_format_number_based'); ?></label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" name="settings[invoice_number_format]" value="2" id="year_based"
            <?= get_option('invoice_number_format') == '2' ? 'checked' : '' ?>>
        <label
            for="year_based"><?= _l('settings_sales_invoice_number_format_year_based'); ?>
            (YYYY/000001)</label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" name="settings[invoice_number_format]" value="3" id="short_year_based"
            <?= get_option('invoice_number_format') == '3' ? 'checked' : '' ?>>
        <label for="short_year_based">000001-YY</label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" name="settings[invoice_number_format]" value="4" id="year_month_based"
            <?= get_option('invoice_number_format') == '4' ? 'checked' : '' ?>>
        <label for="year_month_based">000001/MM/YYYY</label>
    </div>
    <hr />
</div>
<?= render_textarea('settings[predefined_clientnote_invoice]', 'settings_predefined_clientnote', get_option('predefined_clientnote_invoice'), ['rows' => 6]); ?>
<?= render_textarea('settings[predefined_terms_invoice]', 'settings_predefined_predefined_term', get_option('predefined_terms_invoice'), ['rows' => 6]); ?>

<h4 class="bold mtop15"><?= _l('Automatic Task Creation'); ?></h4>
<p class="text-muted"><?= _l('Automatically create a task when a new invoice is created.'); ?></p>
<hr />
<?php render_yes_no_option('invoice_auto_create_task', 'Enable Auto Task'); ?>
<hr />

<div class="form-group">
    <label class="bold">Auto-Created Tasks List</label>
    <p class="text-muted">Define the tasks that should be created automatically.</p>
    
    <!-- Hidden input to store the JSON -->
    <textarea name="settings[invoice_auto_tasks_list]" id="invoice_auto_tasks_list" class="hide"><?= get_option('invoice_auto_tasks_list'); ?></textarea>

    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="auto-tasks-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Description</th>
                    <th>Priority</th>
                    <th>Due Days</th>
                    <th>Assignee</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows will be populated by JS -->
            </tbody>
            <tfoot>
                <tr>
                    <td><input type="text" id="new_task_subject" class="form-control" placeholder="Subject"></td>
                    <td><input type="text" id="new_task_description" class="form-control" placeholder="Description"></td>
                    <td>
                        <select id="new_task_priority" class="form-control">
                            <option value="1"><?= _l('task_priority_low'); ?></option>
                            <option value="2"><?= _l('task_priority_medium'); ?></option>
                            <option value="3"><?= _l('task_priority_high'); ?></option>
                            <option value="4"><?= _l('task_priority_urgent'); ?></option>
                        </select>
                    </td>
                    <td><input type="number" id="new_task_due_days" class="form-control" value="0" placeholder="0"></td>
                    <td>
                         <select id="new_task_assignee" class="form-control">
                            <option value="creator">Invoice Creator</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-info btn-block" onclick="addAutoTaskRow()"><i class="fa fa-plus"></i> Add</button></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    var autoTasks = [];
    
    // Load existing data
    try {
        var existingData = document.getElementById('invoice_auto_tasks_list').value;
        if(existingData) {
            autoTasks = JSON.parse(existingData);
        }
    } catch(e) {
        console.log('Error parsing auto tasks', e);
        autoTasks = [];
    }

    function renderAutoTasksTable() {
        var tbody = document.getElementById('auto-tasks-table').getElementsByTagName('tbody')[0];
        tbody.innerHTML = '';
        
        autoTasks.forEach(function(task, index) {
            var row = tbody.insertRow();
            
            var cellSubject = row.insertCell(0);
            cellSubject.innerHTML = task.subject;
            
            var cellDesc = row.insertCell(1);
            cellDesc.innerHTML = task.description;
            
            var cellPriority = row.insertCell(2);
            var priorityName = 'Unknown';
            if(task.priority == 1) priorityName = 'Low';
            if(task.priority == 2) priorityName = 'Medium';
            if(task.priority == 3) priorityName = 'High';
            if(task.priority == 4) priorityName = 'Urgent';
            cellPriority.innerHTML = priorityName;
            
            var cellDue = row.insertCell(3);
            cellDue.innerHTML = task.due_days;

            var cellAssignee = row.insertCell(4);
            cellAssignee.innerHTML = (task.assignee == 'creator' ? 'Invoice Creator' : 'Unknown');

            var cellAction = row.insertCell(5);
            cellAction.innerHTML = '<button type="button" class="btn btn-danger btn-icon" onclick="removeAutoTaskRow('+index+')"><i class="fa fa-remove"></i></button>';
        });
        
        // Update hidden input
        document.getElementById('invoice_auto_tasks_list').value = JSON.stringify(autoTasks);
    }

    function addAutoTaskRow() {
        var subject = document.getElementById('new_task_subject').value;
        if(!subject) { alert('Subject is required'); return; }
        
        var task = {
            subject: subject,
            description: document.getElementById('new_task_description').value,
            priority: document.getElementById('new_task_priority').value,
            due_days: document.getElementById('new_task_due_days').value,
            assignee: document.getElementById('new_task_assignee').value
        };
        
        autoTasks.push(task);
        renderAutoTasksTable();
        
        // Clear inputs
        document.getElementById('new_task_subject').value = '';
        document.getElementById('new_task_description').value = '';
        document.getElementById('new_task_due_days').value = '0';
    }

    function removeAutoTaskRow(index) {
        autoTasks.splice(index, 1);
        renderAutoTasksTable();
    }

    // Initial render
    document.addEventListener("DOMContentLoaded", function(event) {
       renderAutoTasksTable();
    });
</script>

<h4 class="bold mtop15"><?= _l('PDF Settings'); ?></h4>
<hr />
<?= render_textarea('settings[invoice_pdf_header]', 'PDF Header', get_option('invoice_pdf_header'), ['rows' => 4]); ?>
<p class="text-muted">Custom text to appear at the top of invoice PDFs</p>
<hr />
<?= render_textarea('settings[invoice_pdf_footer]', 'PDF Footer', get_option('invoice_pdf_footer'), ['rows' => 4]); ?>
<p class="text-muted">Custom text to appear at the bottom of invoice PDFs</p>
