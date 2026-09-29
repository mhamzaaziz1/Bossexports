<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Bd_expenses extends AdminController
{
	public function __construct()
	{
		parent::__construct(); 
		$this->load->model('expenses_model');
		if (!is_admin() && !has_permission('bd_expenses', '', 'view') && !has_permission('bd_expenses', '', 'view_own')) {
			access_denied(_l('bd_expenses'));
		}
	}
	
	public function index($id = '')
    {
        $this->list_expenses($id);
    }

    public function list_expenses($id = '')
    {
        close_setup_menu();

        if (!has_permission('bd_expenses', '', 'view') && !has_permission('bd_expenses', '', 'view_own')) {
            access_denied('bd_expenses');
        }
		
		$this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], false);
        $data['expenseid']  = $id;
        $data['categories'] = $this->expenses_model->get_category();
        $data['years']      = $this->bd_expenses_model->get_expenses_years();
        $data['title']      = _l('bd_expenses');

        $this->load->view('expenses/manage', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('bd_expenses', '', 'view') && !has_permission('bd_expenses', '', 'view_own')) {
            ajax_access_denied();
        }
		
		$this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], true);
        $this->app->get_table_data(module_views_path(BD_EXPENSES_MODULE_NAME,'tables/bd_expenses'), [
            'clientid' => $clientid,
			'data'     => $data,
        ]);
    }
	
	public function expense($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('bd_expenses', '', 'create')) {
                    set_alert('danger', _l('access_denied'));
                    echo json_encode([
                        'url' => admin_url('bd_expenses/expense'),
                    ]);
                    die;
                }
                $id = $this->bd_expenses_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('expense')));
                    echo json_encode([
                        'url'       => admin_url('bd_expenses/list_expenses/' . $id),
                        'expenseid' => $id,
                    ]);
                    die;
                }
                echo json_encode([
                    'url' => admin_url('bd_expenses/expense'),
                ]);
                die;
            }
            if (!has_permission('bd_expenses', '', 'edit')) {
                set_alert('danger', _l('access_denied'));
                echo json_encode([
                        'url' => admin_url('bd_expenses/expense/' . $id),
                    ]);
                die;
            }
            $success = $this->bd_expenses_model->update($this->input->post(), $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('expense')));
            }
            echo json_encode([
                    'url'       => admin_url('bd_expenses/list_expenses/' . $id),
                    'expenseid' => $id,
                ]);
            die;
        }
        if ($id == '') {
            $title = _l('add_new', _l('expense_lowercase'));
        } else {
            $data['expense'] = $this->bd_expenses_model->get($id);

            if (!$data['expense'] || (!has_permission('bd_expenses', '', 'view') && $data['expense']->addedfrom != get_staff_user_id())) {
                blank_page(_l('expense_not_found'));
            }

            $title = _l('edit', _l('expense_lowercase'));
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $this->load->model('taxes_model');
        $this->load->model('payment_modes_model');
        $this->load->model('currencies_model');

        $data['taxes']         = $this->taxes_model->get();
        $data['categories']    = $this->expenses_model->get_category();
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'invoices_only !=' => 1,
        ]);
        $data['bodyclass']  = 'expense';
        $data['currencies'] = $this->currencies_model->get();
        $data['title']      = $title;
        $this->load->view('expenses/expense', $data);
    }

    public function get_expenses_total()
    {
        if ($this->input->post()) {
            $data['totals'] = $this->bd_expenses_model->get_expenses_total($this->input->post());

            if ($data['totals']['currency_switcher'] == true) {
                $this->load->model('currencies_model');
                $data['currencies'] = $this->currencies_model->get();
            }

            $data['expenses_years'] = $this->bd_expenses_model->get_expenses_years();

            if (count($data['expenses_years']) >= 1 && $data['expenses_years'][0]['year'] != date('Y')) {
                array_unshift($data['expenses_years'], ['year' => date('Y')]);
            }

            $data['_currency'] = $data['totals']['currencyid'];
            $this->load->view('expenses/expenses_total_template', $data);
        }
    }

	public function get_expense_data_ajax($id)
    {
        if (!has_permission('bd_expenses', '', 'view') && !has_permission('bd_expenses', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }
        $expense = $this->bd_expenses_model->get($id);

        if (!$expense || (!has_permission('bd_expenses', '', 'view') && $expense->addedfrom != get_staff_user_id())) 	
		{
            echo _l('expense_not_found');
            die;
        }

        $data['expense'] = $expense;
        if ($expense->billable == 1) {
            if ($expense->invoiceid !== null) {
                $this->load->model('invoices_model');
                $data['invoice'] = $this->invoices_model->get($expense->invoiceid);
            }
        }

        $data['child_expenses'] = $this->bd_expenses_model->get_child_expenses($id);
        $data['members']        = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('expenses/expense_preview_template', $data);
    }
	
	public function delete($id)
    {
        if (!has_permission('bd_expenses', '', 'delete')) {
            access_denied('bd_expenses');
        }
        if (!$id) {
            redirect(admin_url('bd_expenses/list_expenses'));
        }
        $response = $this->bd_expenses_model->delete($id);
        if ($response === true) {
            set_alert('success', _l('deleted', _l('expense')));
        } else {
            if (is_array($response) && $response['invoiced'] == true) {
                set_alert('warning', _l('expense_invoice_delete_not_allowed'));
            } else {
                set_alert('warning', _l('problem_deleting', _l('expense_lowercase')));
            }
        }

        if (strpos($_SERVER['HTTP_REFERER'], 'bd_expenses/') !== false) {
            redirect(admin_url('bd_expenses/list_expenses'));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
	
	 public function add_expense_attachment($id)
    {
        handle_bd_expense_attachments($id);
        echo json_encode([
            'url' => admin_url('bd_expenses/list_expenses/' . $id),
        ]);
    }

    public function delete_expense_attachment($id, $preview = '')
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'bd_expense');
        $file = $this->db->get(db_prefix().'files')->row();

        if ($file->staffid == get_staff_user_id() || is_admin()) {
            $success = $this->bd_expenses_model->delete_expense_attachment($id);
            if ($success) {
                set_alert('success', _l('deleted', _l('expense_receipt')));
            } else {
                set_alert('warning', _l('problem_deleting', _l('expense_receipt_lowercase')));
            }
            if ($preview == '') {
                redirect(admin_url('bd_expenses/expense/' . $id));
            } else {
                redirect(admin_url('bd_expenses/list_expenses/' . $id));
            }
        } else {
            access_denied('bd_expenses');
        }
    }
	
	public function approve_expenses($id)
	{
		if (!has_permission('bd_expenses', '', 'approve')) {
            access_denied('bd_expenses');
        }
		if (!$id) {
            redirect(admin_url('bd_expenses/list_expenses'));
        }
        $response = $this->bd_expenses_model->approve($id);
        if ($response === false) {
            
			 set_alert('warning', _l('bd_expenses_problem_approving', _l('expense_lowercase')));
        } else {
             set_alert('success', _l('bd_expenses_approved', _l('expense')));  
        }

        if (strpos($_SERVER['HTTP_REFERER'], 'bd_expenses/') !== false) {
            redirect(admin_url('bd_expenses/list_expenses'));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
		
	}
}