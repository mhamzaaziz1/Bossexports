<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shipments extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('shipments_model');
    }

    /* List all shipments */
    public function index()
    {
        if (!has_permission('shipments', '', 'view')) {
            access_denied('shipments');
        }

        $data['title'] = _l('shipments_list');
        $data['shipments'] = $this->shipments_model->get();
        
        $this->load->view('manage', $data);
    }

    /* Add or Edit Shipment */
    public function shipment($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('shipments', '', 'create')) {
                    access_denied('shipments');
                }
                $id = $this->shipments_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('shipment')));
                    redirect(admin_url('shipments/shipment/' . $id));
                }
            } else {
                if (!has_permission('shipments', '', 'edit')) {
                    access_denied('shipments');
                }
                $success = $this->shipments_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('shipment')));
                }
                redirect(admin_url('shipments/shipment/' . $id));
            }
        }

        if ($id == '') {
            $data['title'] = _l('add_new', _l('shipment_number'));
        } else {
            $shipment = $this->shipments_model->get($id);
            
            // Populate related data
            $shipment->lines = $this->shipments_model->get_lines($id);
            $shipment->costs = $this->shipments_model->get_costs($id);
            
            $data['shipment'] = $shipment;
            $data['title'] = $data['shipment']->shipment_number;
        }

        $this->load->view('shipment', $data);
    }
    
    public function delete($id)
    {
        if (!has_permission('shipments', '', 'delete')) {
            access_denied('shipments');
        }
        
        if (!$id) {
            redirect(admin_url('shipments'));
        }
        
        $response = $this->shipments_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('shipment')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('shipment')));
        }
        redirect(admin_url('shipments'));
    }

    public function cost_definitions()
    {
        if (!has_permission('shipments', '', 'view')) {
            access_denied('shipments');
        }

        if ($this->input->post()) {
            if (!has_permission('shipments', '', 'create') && !has_permission('shipments', '', 'edit')) {
                access_denied('shipments');
            }
            
            $data = $this->input->post();
            if ($data['id'] == '') {
                unset($data['id']);
                $id = $this->shipments_model->add_cost_definition($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('cost_definitions')));
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->shipments_model->update_cost_definition($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('cost_definitions')));
                }
            }
            redirect(admin_url('shipments/cost_definitions'));
        }

        $data['title'] = _l('cost_definitions');
        $data['cost_definitions'] = $this->shipments_model->get_cost_definitions();
        $this->load->view('cost_definitions', $data);
    }

    public function delete_cost_definition($id)
    {
        if (!has_permission('shipments', '', 'delete')) {
            access_denied('shipments');
        }
        if (!$id) {
            redirect(admin_url('shipments/cost_definitions'));
        }
        $response = $this->shipments_model->delete_cost_definition($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('cost_definitions')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('cost_definitions')));
        }
        redirect(admin_url('shipments/cost_definitions'));
    }

    /* CRUD for Lines */
    public function add_line()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $insert_id = $this->shipments_model->add_line($data);
            if ($insert_id) {
                echo json_encode(['success' => true, 'id' => $insert_id]);
            } else {
                echo json_encode(['success' => false]);
            }
        }
    }
    
    public function update_line($id)
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->shipments_model->update_line($data, $id);
            echo json_encode(['success' => $success]);
        }
    }
    
    public function delete_line($id)
    {
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'shipment_lines');
        echo json_encode(['success' => true]);
    }

    /* AJAX Search */
    public function search_items()
    {
        if ($this->input->is_ajax_request()) {
            $search = $this->input->get('q');
            $this->db->like('description', $search);
            $this->db->or_like('commodity_code', $search);
            $items = $this->db->get(db_prefix() . 'items')->result_array();
            echo json_encode($items);
        }
    }
    
    public function get_purchase_orders()
    {
        if ($this->input->is_ajax_request()) {
            
            $search = $this->input->get('q');
            $results = [];

            // STRICTLY SEARCH ONLY QUOTATIONS (tblpur_estimates)
            if ($this->db->table_exists(db_prefix() . 'pur_estimates')) {
                $this->db->select('id, number, prefix, reference_no'); 
                
                if (!empty($search)) {
                    $this->db->group_start();
                    $this->db->like('number', $search);
                    $this->db->or_like('reference_no', $search);
                    $this->db->or_like('prefix', $search);
                    $this->db->group_end();
                }
                
                // Optional: Order by most recent
                $this->db->order_by('datecreated', 'DESC');
                $this->db->limit(50);
                
                $estimates = $this->db->get(db_prefix() . 'pur_estimates')->result_array();

                foreach($estimates as $row){
                     // Format number safely
                     $prefix = isset($row['prefix']) ? $row['prefix'] : '';
                     $number = isset($row['number']) ? str_pad($row['number'], 6, '0', STR_PAD_LEFT) : $row['id'];
                     
                     $display_text = '[QT] ' . $prefix . $number;
                     if (!empty($row['reference_no'])) {
                         $display_text .= ' - ' . $row['reference_no'];
                     }

                     $results[] = [
                        'id' => $row['id'], // Raw ID, assuming no conflict since we only use Estimates
                        'text' => $display_text
                    ];
                }
            }

            echo json_encode($results);
        }
    }
    
    public function get_po_items($id)
    {
         if ($this->input->is_ajax_request()) {
             // Fool-proof check for table existence
             if (!$this->db->table_exists(db_prefix() . 'pur_estimate_detail')) {
                 echo json_encode([]);
                 return;
             }
             
             // Get estimate items strictly
             // Note: pur_estimate_detail uses 'pur_estimate' as FK (from install.php analysis)
             // item_code is the item FK.
             
             $this->db->select(db_prefix() . 'pur_estimate_detail.*, ' . db_prefix() . 'items.description as item_name, ' . db_prefix() . 'items.commodity_code');
             $this->db->from(db_prefix() . 'pur_estimate_detail');
             
             // Join to tblitems to get description if needed
             $this->db->join(db_prefix() . 'items', db_prefix() . 'items.id = ' . db_prefix() . 'pur_estimate_detail.item_code', 'left');
             
             $this->db->where('pur_estimate', $id);
             $items = $this->db->get()->result_array();
             
             // Normalize keys for frontend (js expects item_code, quantity, unit_price, item_name)
             // The query above selects * from detail, so item_code is there.
             // We ensure item_name is populated.
             
             echo json_encode($items);
         }
    }
    
    /* CRUD for Costs */
    public function add_cost()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $insert_id = $this->shipments_model->add_cost($data);
            if ($insert_id) {
                echo json_encode(['success' => true, 'id' => $insert_id]);
            } else {
                echo json_encode(['success' => false]);
            }
        }
    }
    
    public function delete_cost($id)
    {
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'shipment_cost_allocations');
        echo json_encode(['success' => true]);
    }
    
    /* Engine Preview */
    public function calculate($shipment_id)
    {
        // Clean any previous output (warnings etc) to ensure valid JSON
        if (ob_get_length()) ob_clean();
        
        try {
            $this->load->library('shipments/landed_cost_engine');
            $result = $this->landed_cost_engine->calculate_shipment($shipment_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function pdf($id)
    {
        if (!has_permission('shipments', '', 'view')) {
            access_denied('shipments');
        }
        
        $this->load->helper('pdf');
        $this->load->helper('sales');
        
        $shipment = $this->shipments_model->get($id);
        
        if (!$shipment) {
            show_404();
        }
        
        $shipment->lines = $this->shipments_model->get_lines($id);
        $shipment->costs = $this->shipments_model->get_costs($id);

        $this->load->library('shipments/shipment_pdf', $shipment);
        
        if (ob_get_length()) {
            ob_end_clean();
        }
        
        $pdf = $this->shipment_pdf->prepare();
        $pdf->Output(slug_it(_l('shipment') . '-' . $shipment->shipment_number) . '.pdf', 'D');
    }
    
    /* Commit Shipment */
    public function commit($id)
    {
        if (!has_permission('shipments', '', 'edit')) {
            access_denied('shipments');
        }
        
        $success = $this->shipments_model->commit_shipment($id);
        
        if ($success) {
            set_alert('success', _l('shipment_committed_successfully'));
        } else {
            set_alert('danger', _l('shipment_commit_failed'));
        }
        
        redirect(admin_url('shipments/shipment/' . $id));
    }
}
