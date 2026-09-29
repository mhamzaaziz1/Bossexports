<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Bd_expenses_model extends App_Model
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
     * Get expense(s)
     * @param  mixed $id Optional expense id
     * @return mixed     object or array
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'bd_expenses.id as id,' . db_prefix() . 'expenses_categories.name as category_name,' . db_prefix() . 'payment_modes.name as payment_mode_name,' . db_prefix() . 'taxes.name as tax_name, ' . db_prefix() . 'taxes.taxrate as taxrate,' . db_prefix() . 'taxes_2.name as tax_name2, ' . db_prefix() . 'taxes_2.taxrate as taxrate2, ' . db_prefix() . 'bd_expenses.id as expenseid,' . db_prefix() . 'bd_expenses.addedfrom as addedfrom, recurring_from');
        $this->db->from(db_prefix() . 'bd_expenses');
        $this->db->join(db_prefix() . 'clients', '' . db_prefix() . 'clients.userid = ' . db_prefix() . 'bd_expenses.clientid', 'left');
        $this->db->join(db_prefix() . 'payment_modes', '' . db_prefix() . 'payment_modes.id = ' . db_prefix() . 'bd_expenses.paymentmode', 'left');
        $this->db->join(db_prefix() . 'taxes', '' . db_prefix() . 'taxes.id = ' . db_prefix() . 'bd_expenses.tax', 'left');
        $this->db->join('' . db_prefix() . 'taxes as ' . db_prefix() . 'taxes_2', '' . db_prefix() . 'taxes_2.id = ' . db_prefix() . 'bd_expenses.tax2', 'left');
        $this->db->join(db_prefix() . 'expenses_categories', '' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'bd_expenses.category');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'bd_expenses.id', $id);
            $expense = $this->db->get()->row();
            if ($expense) {
                $expense->attachment            = '';
                $expense->filetype              = '';
                $expense->attachment_added_from = 0;

                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'bd_expense');
                $file = $this->db->get(db_prefix() . 'files')->row();

                if ($file) {
                    $expense->attachment            = $file->file_name;
                    $expense->filetype              = $file->filetype;
                    $expense->attachment_added_from = $file->staffid;
                }

                $this->load->model('projects_model');
                $expense->currency_data = get_currency($expense->currency);
                if ($expense->project_id != 0) {
                    $expense->project_data = $this->projects_model->get($expense->project_id);
                }

                if (is_null($expense->payment_mode_name)) {
                    // is online payment mode
                    $this->load->model('payment_modes_model');
                    $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
                    foreach ($payment_gateways as $gateway) {
                        if ($expense->paymentmode == $gateway['id']) {
                            $expense->payment_mode_name = $gateway['name'];
                        }
                    }
                }
            }

            return $expense;
        }
        $this->db->order_by('date', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Add new expense
     * @param mixed $data All $_POST data
     * @return  mixed
     */
    public function add($data)
    {
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);
        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }
        if (isset($data['create_invoice_billable'])) {
            $data['create_invoice_billable'] = 1;
        } else {
            $data['create_invoice_billable'] = 0;
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['send_invoice_to_customer'])) {
            $data['send_invoice_to_customer'] = 1;
        } else {
            $data['send_invoice_to_customer'] = 0;
        }

        if (isset($data['repeat_every']) && $data['repeat_every'] != '') {
            $data['recurring'] = 1;
            if ($data['repeat_every'] == 'custom') {
                $data['repeat_every']     = $data['repeat_every_custom'];
                $data['recurring_type']   = $data['repeat_type_custom'];
                $data['custom_recurring'] = 1;
            } else {
                $_temp                    = explode('-', $data['repeat_every']);
                $data['recurring_type']   = $_temp[1];
                $data['repeat_every']     = $_temp[0];
                $data['custom_recurring'] = 0;
            }
        } else {
            $data['recurring'] = 0;
        }
        unset($data['repeat_type_custom']);
        unset($data['repeat_every_custom']);

        if ((isset($data['project_id']) && $data['project_id'] == '') || !isset($data['project_id'])) {
            $data['project_id'] = 0;
        }
        $data['addedfrom'] = get_staff_user_id();
        $data['dateadded'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'bd_expenses', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if (isset($custom_fields)) {
				$custom_fields['bd_expenses']=$custom_fields['expenses'];
				unset($custom_fields['expenses']);
                handle_custom_fields_post($insert_id, $custom_fields);
            }
            log_activity('New Expense for Approval Added [' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }
	
	/**
     * Update expense
     * @param  mixed $data All $_POST data
     * @param  mixed $id   expense id to update
     * @return boolean
     */
    public function update($data, $id)
    {
        $original_expense = $this->get($id);

        $affectedRows = 0;
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);

        // Recurring expense set to NO, Cancelled
        if ($original_expense->repeat_every != '' && $data['repeat_every'] == '') {
            $data['cycles']              = 0;
            $data['total_cycles']        = 0;
            $data['last_recurring_date'] = null;
        }

        if ($data['repeat_every'] != '') {
            $data['recurring'] = 1;
            if ($data['repeat_every'] == 'custom') {
                $data['repeat_every']     = $data['repeat_every_custom'];
                $data['recurring_type']   = $data['repeat_type_custom'];
                $data['custom_recurring'] = 1;
            } else {
                $_temp                    = explode('-', $data['repeat_every']);
                $data['recurring_type']   = $_temp[1];
                $data['repeat_every']     = $_temp[0];
                $data['custom_recurring'] = 0;
            }
        } else {
            $data['recurring'] = 0;
        }

        $data['cycles'] = !isset($data['cycles']) || $data['recurring'] == 0 ? 0 : $data['cycles'];

        unset($data['repeat_type_custom']);
        unset($data['repeat_every_custom']);

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
			$custom_fields['bd_expenses']=$custom_fields['expenses'];
			unset($custom_fields['expenses']);
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if (isset($data['create_invoice_billable'])) {
            $data['create_invoice_billable'] = 1;
        } else {
            $data['create_invoice_billable'] = 0;
        }

        if (isset($data['billable'])) {
            $data['billable'] = 1;
        } else {
            $data['billable'] = 0;
        }

        if (isset($data['send_invoice_to_customer'])) {
            $data['send_invoice_to_customer'] = 1;
        } else {
            $data['send_invoice_to_customer'] = 0;
        }

        if (isset($data['project_id']) && $data['project_id'] == '' || !isset($data['project_id'])) {
            $data['project_id'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'bd_expenses', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Updated before Approval [' . $id . ']');
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete expense from database, if used return
     */
    public function delete($id, $showActivity = true)
    {
        $_expense = $this->get($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'bd_expenses');

        if ($this->db->affected_rows() > 0) {
            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'bd_expenses');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->delete_expense_attachment($id);

            $this->db->where('recurring_from', $id);
            $this->db->update(db_prefix() . 'bd_expenses', ['recurring_from' => null]);

			if($showActivity)
            	log_activity('Expense Deleted before Approval [' . $id . ']');

            return true;
        }

        return false;
    }
	
	/**
	Approve expense and add to expense table
	*/
	public function approve($id)
	{
		$this->db->where('id', $id);
        $data = (array) $this->db->get('bd_expenses')->row();
		
		if(empty($data))
		{
			return false;
		}
		unset($data['id']);
		$this->db->insert(db_prefix() . 'expenses', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            //copy custom fields
			$this->copy_custom_fields($insert_id, $id);
			// copy attachment
			$this->copy_attachments($insert_id, $id);
            if (isset($data['project_id']) && !empty($data['project_id'])) {
                $this->load->model('projects_model');
                $project_settings = $this->projects_model->get_project_settings($data['project_id']);
                $visible_activity = 0;
                foreach ($project_settings as $s) {
                    if ($s['name'] == 'view_finance_overview') {
                        if ($s['value'] == 1) {
                            $visible_activity = 1;

                            break;
                        }
                    }
                }
                $expense                  = $this->expenses_model->get($insert_id);
                $activity_additional_data = $expense->name . '<br />';
                $activity_additional_data .= app_format_money($expense->amount, $expense->currency_data->name);
                $this->projects_model->log_activity($data['project_id'], 'project_activity_recorded_expense', $activity_additional_data, $visible_activity);
            }
			$this->delete($id);//remove from bd_expenses table
            log_activity('Expense Approved [' . $insert_id . ']');

            return $insert_id;
        }
		return false;
	}

	public function get_expenses_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'bd_expenses ORDER by year DESC')->result_array();
    }
	
	public function get_child_expenses($id)
    {
        $this->db->select('id');
        $this->db->where('recurring_from', $id);
        $expenses = $this->db->get(db_prefix() . 'bd_expenses')->result_array();

        $_expenses = [];
        foreach ($expenses as $expense) {
            $_expenses[] = $this->get($expense['id']);
        }

        return $_expenses;
    }

	public function get_expenses_total($data)
    {
        $this->load->model('currencies_model');
        $base_currency     = $this->currencies_model->get_base_currency()->id;
        $base              = true;
        $currency_switcher = false;
        if (isset($data['currency'])) {
            $currencyid        = $data['currency'];
            $currency_switcher = true;
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $base_currency;
            } else {
                if (total_rows(db_prefix() . 'bd_expenses', [
                    'currency' => $base_currency,
                    'clientid' => $data['customer_id'],
                ])) {
                    $currency_switcher = true;
                }
            }
        } elseif (isset($data['project_id']) && $data['project_id'] != '') {
            $this->load->model('projects_model');
            $currencyid = $this->projects_model->get_currency($data['project_id'])->id;
        } else {
            $currencyid = $base_currency;
            if (total_rows(db_prefix() . 'bd_expenses', [
                'currency !=' => $base_currency,
            ])) {
                $currency_switcher = true;
            }
        }

        $currency = get_currency($currencyid);

        $has_permission_view = has_permission('bd_expenses', '', 'view');
        $_result             = [];

        for ($i = 1; $i <= 5; $i++) {
            $this->db->select('amount,tax,tax2,invoiceid');
            $this->db->where('currency', $currencyid);

            if (isset($data['years']) && count($data['years']) > 0) {
                $this->db->where('YEAR(date) IN (' . implode(', ', $data['years']) . ')');
            } else {
                $this->db->where('YEAR(date) = ' . date('Y'));
            }
            if (isset($data['customer_id']) && $data['customer_id'] != '') {
                $this->db->where('clientid', $data['customer_id']);
            }
            if (isset($data['project_id']) && $data['project_id'] != '') {
                $this->db->where('project_id', $data['project_id']);
            }

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            switch ($i) {
                case 1:
                    $key = 'all';

                    break;
                case 2:
                    $key = 'billable';
                    $this->db->where('billable', 1);

                    break;
                case 3:
                    $key = 'non_billable';
                    $this->db->where('billable', 0);

                    break;
                case 4:
                    $key = 'billed';
                    $this->db->where('billable', 1);
                    $this->db->where('invoiceid IS NOT NULL');
                    $this->db->where('invoiceid IN (SELECT invoiceid FROM ' . db_prefix() . 'invoices WHERE status=2 AND id=' . db_prefix() . 'bd_expenses.invoiceid)');

                    break;
                case 5:
                    $key = 'unbilled';
                    $this->db->where('billable', 1);
                    $this->db->where('invoiceid IS NULL');

                    break;
            }
            $all_expenses = $this->db->get(db_prefix() . 'bd_expenses')->result_array();
            $_total_all   = [];
            $cached_taxes = [];
            foreach ($all_expenses as $expense) {
                $_total = $expense['amount'];
                if ($expense['tax'] != 0) {
                    if (!isset($cached_taxes[$expense['tax']])) {
                        $tax                           = get_tax_by_id($expense['tax']);
                        $cached_taxes[$expense['tax']] = $tax;
                    } else {
                        $tax = $cached_taxes[$expense['tax']];
                    }
                    $_total += ($_total / 100 * $tax->taxrate);
                }
                if ($expense['tax2'] != 0) {
                    if (!isset($cached_taxes[$expense['tax2']])) {
                        $tax                            = get_tax_by_id($expense['tax2']);
                        $cached_taxes[$expense['tax2']] = $tax;
                    } else {
                        $tax = $cached_taxes[$expense['tax2']];
                    }
                    $_total += ($expense['amount'] / 100 * $tax->taxrate);
                }
                array_push($_total_all, $_total);
            }
            $_result[$key]['total'] = app_format_money(array_sum($_total_all), $currency);
        }
        $_result['currency_switcher'] = $currency_switcher;
        $_result['currencyid']        = $currencyid;

        return $_result;
    }
	
	/**
     * Delete Expense attachment
     * @param  mixed $id expense id
     * @return boolean
     */
    public function delete_expense_attachment($id)
    {
        if (is_dir(BD_EXPENSE_ATTACHMENTS_FOLDER . $id)) {
            if (delete_dir(BD_EXPENSE_ATTACHMENTS_FOLDER . $id)) {
                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'bd_expense');
                $this->db->delete(db_prefix() . 'files');
                log_activity('SI Expense Receipt Deleted [ExpenseID: ' . $id . ']');

                return true;
            }
        }

        return false;
    }
	
	//copy custom fields from bd_expenses to expense
	function copy_custom_fields($expense_id,$bd_expense_id)
	{
		$this->db->where('fieldto','bd_expenses');
		$this->db->where('relid',$bd_expense_id);
		$this->db->set('fieldto','expenses');
		$this->db->set('relid',$expense_id);
		$this->db->update(db_prefix() . 'customfieldsvalues');
	}
	//copy custom fields from bd_expenses to expense
	function copy_attachments($expense_id,$bd_expense_id)
	{
		$this->db->where('rel_id', $bd_expense_id);
		$this->db->where('rel_type', 'bd_expense');
		$file = $this->db->get(db_prefix() . 'files')->row();

		if ($file) {
			$file_name = $file->file_name;
			$path = BD_EXPENSE_ATTACHMENTS_FOLDER . $bd_expense_id . '/';
			$new_path = get_upload_path_by_type('expense') . $expense_id . '/';
			if(file_exists($path.$file_name))
			{
				_maybe_create_upload_path($new_path);
				if(copy($path.$file_name,$new_path.$file_name)){
					$this->db->where('rel_type','bd_expense');
					$this->db->where('rel_id',$bd_expense_id);
					$this->db->set('rel_type','expense');
					$this->db->set('rel_id',$expense_id);
					$this->db->update(db_prefix() . 'files');
				}
			}
		}
	}

}
