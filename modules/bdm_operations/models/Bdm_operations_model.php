<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Bdm_operations_model
 *
 * Senior-grade model implementing the BDM Process & Marketing Plan
 * (Advanced Planning Document, IT & Fiber Optic Infrastructure, UAE).
 *
 * Sections:
 *   1. Brands
 *   2. Distributors / Vendors (Supplier Onboarding)
 *   3. VR Registrations (10-step pipeline)
 *   4. Master Companies
 *   5. BDM Activities (Visit/Call/Email/WhatsApp/LinkedIn)
 *   6. Partners (Inbound/Outbound) + Commissions
 *   7. Tenders
 *   8. Marketing Campaigns
 *   9. KPI Targets
 *  10. Dashboard / Pipeline / Funnel / Performance / Forecast
 *  11. Email cadence + Lead-to-BDM referral
 */
class Bdm_operations_model extends App_Model
{
    /** Pipeline step canonical labels — 10-step BDM workflow */
    public static $pipeline_steps = [
        1  => 'Lead Identified',
        2  => 'Classified',
        3  => 'Registered/Onboarded',
        4  => 'Relationship Building',
        5  => 'Requirement Captured',
        6  => 'Quote Sent',
        7  => 'Deal Closed',
        8  => 'Execution',
        9  => 'Payment & Commission',
        10 => 'Long-Term Growth',
    ];

    /** Status labels (legacy 9-stage compatibility) */
    public static $vr_statuses = [
        'New Referral'          => 1,
        'Contact Initiated'     => 2,
        'Information Requested' => 3,
        'Under Review'          => 4,
        'Qualified'             => 5,
        'Verification'          => 6,
        'Final Approval'        => 7,
        'Registered'            => 8,
        'Rejected'              => 9,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Apply visibility logic to a query — global view if user has perms, else own-only.
     */
    private function apply_visibility_logic($field_name = 'assigned_bdm')
    {
        if (!is_admin() && !has_permission('bdm_operations', '', 'view')) {
            $this->db->where($field_name, get_staff_user_id());
        }
    }

    /* ===================================================================== */
    /* 1. BRANDS                                                              */
    /* ===================================================================== */

    public function get_brands($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'brands')->row();
        }
        $this->apply_visibility_logic('created_by');
        $this->db->order_by('name', 'asc');
        return $this->db->get(db_prefix() . 'brands')->result_array();
    }

    public function add_brand($data)
    {
        $data = array_intersect_key($data, array_flip([
            'name', 'description', 'country_id', 'category', 'website', 'logo',
        ]));
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'brands', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('BDM Brand Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }
        return $insert_id;
    }

    public function update_brand($data, $id)
    {
        $data = array_intersect_key($data, array_flip([
            'name', 'description', 'country_id', 'category', 'website', 'logo',
        ]));
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'brands', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('BDM Brand Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function delete_brand($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'brands');
        if ($this->db->affected_rows() > 0) {
            log_activity('BDM Brand Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /* ===================================================================== */
    /* 2. DISTRIBUTORS / VENDORS                                              */
    /* ===================================================================== */

    public function get_distributors($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'distributors')->row();
        }
        $this->apply_visibility_logic('assigned_bdm');
        $this->db->order_by('created_at', 'desc');
        return $this->db->get(db_prefix() . 'distributors')->result_array();
    }

    public function add_distributor($data)
    {
        $data = array_intersect_key($data, array_flip([
            'name', 'brand_id', 'classification', 'segment', 'contact_name',
            'email', 'phone', 'whatsapp', 'website', 'address', 'emirate',
            'trade_license_no', 'vat_trn', 'cr_number', 'iso_certified',
            'vendor_role', 'products_services', 'price_range',
            'assigned_bdm', 'follow_up_date', 'status', 'notes',
        ]));
        $data['iso_certified'] = !empty($data['iso_certified']) ? 1 : 0;
        $data['created_at']    = date('Y-m-d H:i:s');
        if (empty($data['assigned_bdm'])) {
            $data['assigned_bdm'] = get_staff_user_id();
        }
        $this->db->insert(db_prefix() . 'distributors', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('BDM Vendor Added [ID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }
        return $insert_id;
    }

    public function update_distributor($data, $id)
    {
        $data = array_intersect_key($data, array_flip([
            'name', 'brand_id', 'classification', 'segment', 'contact_name',
            'email', 'phone', 'whatsapp', 'website', 'address', 'emirate',
            'trade_license_no', 'vat_trn', 'cr_number', 'iso_certified',
            'vendor_role', 'products_services', 'price_range',
            'assigned_bdm', 'follow_up_date', 'status', 'notes',
        ]));
        $data['iso_certified'] = !empty($data['iso_certified']) ? 1 : 0;
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'distributors', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('BDM Vendor Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function delete_distributor($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'distributors');
        if ($this->db->affected_rows() > 0) {
            log_activity('BDM Vendor Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Push distributor record to Perfex Purchase Module Vendor (purchase add-on).
     * Returns vendor_id on success, false on failure.
     */
    public function push_to_purchase_module($id)
    {
        $distributor = $this->get_distributors($id);
        if (!$distributor || !$this->db->table_exists(db_prefix() . 'pur_vendor')) {
            return false;
        }

        $this->db->where('company', $distributor->name);
        $exists = $this->db->get(db_prefix() . 'pur_vendor')->row();
        if ($exists) {
            return $exists->userid;
        }

        $vendor_data = [
            'company'     => $distributor->name,
            'phonenumber' => $distributor->phone,
            'website'     => $distributor->website ?? '',
            'address'     => $distributor->address ?? '',
            'datecreated' => date('Y-m-d H:i:s'),
            'active'      => 1,
            'addedfrom'   => get_staff_user_id(),
        ];

        $brand = $this->get_brands($distributor->brand_id);
        if ($brand && $brand->country_id) {
            $vendor_data['country'] = $brand->country_id;
        }

        $this->db->insert(db_prefix() . 'pur_vendor', $vendor_data);
        $vendor_id = $this->db->insert_id();

        if ($vendor_id) {
            $admins = $this->db->where('admin', 1)->get(db_prefix() . 'staff')->result_array();
            foreach ($admins as $admin) {
                add_notification([
                    'description' => 'New Vendor Pushed from BDM: ' . $distributor->name,
                    'tostaffid'   => $admin['staffid'],
                    'fromcompany' => true,
                    'link'        => 'purchase/vendors',
                ]);
            }

            if (!empty($distributor->contact_name) && $this->db->table_exists(db_prefix() . 'pur_contacts')) {
                $parts     = explode(' ', $distributor->contact_name, 2);
                $firstname = $parts[0];
                $lastname  = $parts[1] ?? '';

                $this->db->insert(db_prefix() . 'pur_contacts', [
                    'userid'      => $vendor_id,
                    'firstname'   => $firstname,
                    'lastname'    => $lastname,
                    'email'       => $distributor->email,
                    'phonenumber' => $distributor->phone,
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return $vendor_id;
    }

    /* ===================================================================== */
    /* 3. VR REGISTRATIONS — 10-step pipeline                                  */
    /* ===================================================================== */

    public function get_vr_registrations($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'vr_registrations')->row();
        }
        $this->apply_visibility_logic('assigned_bdm');
        $this->db->order_by('updated_at', 'desc');
        return $this->db->get(db_prefix() . 'vr_registrations')->result_array();
    }

    public function add_vr_registration($data)
    {
        $allowed = [
            'company_id', 'lead_id', 'client_id', 'registration_type', 'pipeline_step',
            'status', 'priority', 'segment', 'decision_maker', 'decision_maker_role',
            'email', 'phone', 'expected_value', 'won_value', 'currency', 'lead_source',
            'documents_submitted',
            'compliance_iso', 'compliance_trade_license', 'compliance_vat_trn', 'compliance_cr',
            'registration_date', 'target_close_date', 'follow_up_date',
            'assigned_bdm', 'notes',
        ];
        $row = array_intersect_key($data, array_flip($allowed));
        foreach (['compliance_iso', 'compliance_trade_license', 'compliance_vat_trn', 'compliance_cr'] as $f) {
            $row[$f] = !empty($row[$f]) ? 1 : 0;
        }
        if (empty($row['status']))            $row['status']            = 'New Referral';
        if (empty($row['pipeline_step']))     $row['pipeline_step']     = 1;
        if (empty($row['registration_date'])) $row['registration_date'] = date('Y-m-d');
        if (empty($row['assigned_bdm']))      $row['assigned_bdm']      = get_staff_user_id();
        if (empty($row['currency']))          $row['currency']          = 'AED';
        $row['created_at'] = date('Y-m-d H:i:s');
        $row['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'vr_registrations', $row);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->log_vr_activity($insert_id, 'Registration created at step [' . $row['pipeline_step'] . ' — ' . $row['status'] . '].');
            $this->schedule_email_cadence($insert_id);
        }
        return $insert_id;
    }

    public function update_vr_registration($data, $id)
    {
        $allowed = [
            'company_id', 'lead_id', 'client_id', 'registration_type', 'pipeline_step',
            'status', 'priority', 'segment', 'decision_maker', 'decision_maker_role',
            'email', 'phone', 'expected_value', 'won_value', 'currency', 'lead_source',
            'documents_submitted',
            'compliance_iso', 'compliance_trade_license', 'compliance_vat_trn', 'compliance_cr',
            'registration_date', 'target_close_date', 'follow_up_date',
            'assigned_bdm', 'notes',
        ];
        $row = array_intersect_key($data, array_flip($allowed));
        foreach (['compliance_iso', 'compliance_trade_license', 'compliance_vat_trn', 'compliance_cr'] as $f) {
            if (array_key_exists($f, $row)) {
                $row[$f] = !empty($row[$f]) ? 1 : 0;
            }
        }
        $row['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'vr_registrations', $row);
        if ($this->db->affected_rows() > 0) {
            $this->log_vr_activity($id, 'Registration updated.');
            return true;
        }
        return false;
    }

    public function update_vr_status($id, $status)
    {
        $current = $this->db->select('status, pipeline_step, assigned_bdm, company_id')
                            ->where('id', $id)->get(db_prefix() . 'vr_registrations')->row();
        if (!$current || $current->status === $status) {
            return false;
        }
        $update = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];

        // Auto-bump pipeline step when status implies forward motion
        $map = [
            'New Referral' => 1, 'Contact Initiated' => 2, 'Information Requested' => 3,
            'Under Review' => 4, 'Qualified' => 5, 'Verification' => 6,
            'Final Approval' => 7, 'Registered' => 8, 'Rejected' => 9,
        ];
        if (isset($map[$status])) {
            $update['pipeline_step'] = max((int) $current->pipeline_step, $map[$status]);
        }

        $this->db->where('id', $id)->update(db_prefix() . 'vr_registrations', $update);
        $this->log_vr_activity($id, 'Status: [' . $current->status . '] → [' . $status . ']');

        $company = $this->get_master_companies($current->company_id);
        if ($current->assigned_bdm) {
            add_notification([
                'description' => 'BDM Status Update: ' . $status . ' for ' . ($company ? $company->company_name : 'N/A'),
                'tostaffid'   => $current->assigned_bdm,
                'fromcompany' => true,
                'link'        => 'bdm_operations/vr_registrations',
            ]);
        }
        return true;
    }

    public function update_vr_pipeline_step($id, $step)
    {
        $step    = max(1, min(10, (int) $step));
        $current = $this->db->where('id', $id)->get(db_prefix() . 'vr_registrations')->row();
        if (!$current) return false;

        $this->db->where('id', $id)->update(db_prefix() . 'vr_registrations', [
            'pipeline_step' => $step,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        $label = self::$pipeline_steps[$step] ?? 'Step ' . $step;
        $this->log_vr_activity($id, 'Pipeline → Step ' . $step . ' (' . $label . ')');
        return true;
    }

    public function delete_vr_registration($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'vr_registrations');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('registration_id', $id)->delete(db_prefix() . 'vr_activity_logs');
            $this->db->where('registration_id', $id)->delete(db_prefix() . 'bdm_email_cadence');
            log_activity('BDM Registration Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function check_duplicate_company($company_name)
    {
        $this->db->select(db_prefix() . 'vr_registrations.id, ' . db_prefix() . 'vr_master_companies.company_name');
        $this->db->join(
            db_prefix() . 'vr_master_companies',
            db_prefix() . 'vr_master_companies.id = ' . db_prefix() . 'vr_registrations.company_id'
        );
        $this->db->where(db_prefix() . 'vr_master_companies.company_name', $company_name);
        return $this->db->get(db_prefix() . 'vr_registrations')->row();
    }

    /* ===================================================================== */
    /* 4. MASTER COMPANIES                                                    */
    /* ===================================================================== */

    public function get_master_companies($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'vr_master_companies')->row();
        }
        $this->db->order_by('company_name', 'asc');
        return $this->db->get(db_prefix() . 'vr_master_companies')->result_array();
    }

    public function add_master_company($data)
    {
        $allowed = ['company_name', 'registration_number', 'industry', 'emirate', 'country_id', 'website', 'phone'];
        $row = array_intersect_key($data, array_flip($allowed));
        $row['created_at'] = date('Y-m-d H:i:s');

        // Idempotent
        $this->db->where('company_name', $row['company_name']);
        $existing = $this->db->get(db_prefix() . 'vr_master_companies')->row();
        if ($existing) return $existing->id;

        $this->db->insert(db_prefix() . 'vr_master_companies', $row);
        return $this->db->insert_id();
    }

    public function update_master_company($data, $id)
    {
        $allowed = ['company_name', 'registration_number', 'industry', 'emirate', 'country_id', 'website', 'phone'];
        $row = array_intersect_key($data, array_flip($allowed));
        $this->db->where('id', $id)->update(db_prefix() . 'vr_master_companies', $row);
        return $this->db->affected_rows() > 0;
    }

    public function delete_master_company($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'vr_master_companies');
        return $this->db->affected_rows() > 0;
    }

    /* ===================================================================== */
    /* 5. BDM ACTIVITIES                                                      */
    /* ===================================================================== */

    public function get_activities($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'bdm_activities')->row();
        }
        $this->apply_visibility_logic('staff_id');
        $this->db->order_by('date_created', 'desc');
        return $this->db->get(db_prefix() . 'bdm_activities')->result_array();
    }

    public function add_activity($data)
    {
        $allowed = ['type', 'channel', 'customer_name', 'contact_person', 'phone', 'email',
                    'outcome', 'description', 'next_action', 'follow_up_date',
                    'registration_id', 'partner_id', 'lead_id'];
        $row = array_intersect_key($data, array_flip($allowed));
        $row['staff_id']     = get_staff_user_id();
        $row['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'bdm_activities', $row);
        $aid = $this->db->insert_id();

        // If linked to a registration, mirror in VR activity log too
        if ($aid && !empty($row['registration_id'])) {
            $this->log_vr_activity(
                (int) $row['registration_id'],
                ($row['type'] ?? 'Activity') . ' — ' . ($row['description'] ?? '')
            );
        }
        return $aid;
    }

    public function delete_activity($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'bdm_activities');
        return $this->db->affected_rows() > 0;
    }

    /* ===================================================================== */
    /* 6. PARTNERS + COMMISSIONS                                              */
    /* ===================================================================== */

    public function get_partners($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'bdm_partners')->row();
        }
        $this->apply_visibility_logic('assigned_bdm');
        $this->db->order_by('created_at', 'desc');
        return $this->db->get(db_prefix() . 'bdm_partners')->result_array();
    }

    public function add_partner($data)
    {
        $allowed = ['company_name', 'partner_type', 'model', 'status', 'contact_person',
                    'email', 'phone', 'whatsapp', 'emirate', 'territory',
                    'agreement_date', 'agreement_expiry', 'commission_pct', 'margin_pct',
                    'assigned_bdm', 'notes'];
        $row = array_intersect_key($data, array_flip($allowed));
        if (empty($row['assigned_bdm'])) $row['assigned_bdm'] = get_staff_user_id();
        if (empty($row['status']))       $row['status']       = 'Pending';
        $row['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'bdm_partners', $row);
        $pid = $this->db->insert_id();
        if ($pid) log_activity('BDM Partner Added [ID: ' . $pid . ']');
        return $pid;
    }

    public function update_partner($data, $id)
    {
        $allowed = ['company_name', 'partner_type', 'model', 'status', 'contact_person',
                    'email', 'phone', 'whatsapp', 'emirate', 'territory',
                    'agreement_date', 'agreement_expiry', 'commission_pct', 'margin_pct',
                    'assigned_bdm', 'notes'];
        $row = array_intersect_key($data, array_flip($allowed));
        $this->db->where('id', $id)->update(db_prefix() . 'bdm_partners', $row);
        return $this->db->affected_rows() > 0;
    }

    public function delete_partner($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'bdm_partners');
        return $this->db->affected_rows() > 0;
    }

    /* ----- Commissions ledger ----- */

    public function get_commissions($id = '', $partner_id = null)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'bdm_partner_commissions')->row();
        }
        if ($partner_id) {
            $this->db->where('partner_id', $partner_id);
        }
        $this->db->order_by('created_at', 'desc');
        return $this->db->get(db_prefix() . 'bdm_partner_commissions')->result_array();
    }

    public function add_commission($data)
    {
        $allowed = ['partner_id', 'invoice_id', 'registration_id', 'deal_reference',
                    'deal_value', 'commission_pct', 'currency', 'status', 'notes'];
        $row = array_intersect_key($data, array_flip($allowed));
        $row['deal_value']        = (float) ($row['deal_value'] ?? 0);
        $row['commission_pct']    = (float) ($row['commission_pct'] ?? 0);
        $row['commission_amount'] = round($row['deal_value'] * ($row['commission_pct'] / 100), 2);
        $row['currency']          = $row['currency'] ?? 'AED';
        $row['status']            = $row['status'] ?? 'Accrued';
        $row['accrued_at']        = date('Y-m-d H:i:s');
        $row['created_by']        = get_staff_user_id();
        $row['created_at']        = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'bdm_partner_commissions', $row);
        $cid = $this->db->insert_id();
        if ($cid && !empty($row['partner_id'])) {
            $this->recalculate_partner_totals((int) $row['partner_id']);
        }
        return $cid;
    }

    public function mark_commission_paid($id)
    {
        $this->db->where('id', $id)->update(db_prefix() . 'bdm_partner_commissions', [
            'status'  => 'Paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);
        if ($this->db->affected_rows() > 0) {
            $row = $this->get_commissions($id);
            if ($row && !empty($row->partner_id)) {
                $this->recalculate_partner_totals((int) $row->partner_id);
            }
            return true;
        }
        return false;
    }

    public function delete_commission($id)
    {
        $row = $this->get_commissions($id);
        $this->db->where('id', $id)->delete(db_prefix() . 'bdm_partner_commissions');
        if ($this->db->affected_rows() > 0 && $row) {
            $this->recalculate_partner_totals((int) $row->partner_id);
            return true;
        }
        return false;
    }

    public function recalculate_partner_totals($partner_id)
    {
        $this->db->select_sum('deal_value', 'rev')
                 ->select_sum('commission_amount', 'comm')
                 ->where('partner_id', $partner_id);
        $row = $this->db->get(db_prefix() . 'bdm_partner_commissions')->row();
        $this->db->where('partner_id', $partner_id);
        $count = $this->db->count_all_results(db_prefix() . 'bdm_partner_commissions');

        $this->db->where('id', $partner_id)->update(db_prefix() . 'bdm_partners', [
            'total_deals'      => (int) $count,
            'total_revenue'    => (float) ($row->rev ?? 0),
            'total_commission' => (float) ($row->comm ?? 0),
        ]);
    }

    /* ===================================================================== */
    /* 7. TENDERS                                                             */
    /* ===================================================================== */

    public function get_tenders($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'bdm_tenders')->row();
        }
        $this->apply_visibility_logic('assigned_bdm');
        $this->db->order_by('submission_deadline', 'asc');
        return $this->db->get(db_prefix() . 'bdm_tenders')->result_array();
    }

    public function add_tender($data)
    {
        $allowed = ['tender_ref', 'title', 'client_company', 'segment', 'emirate', 'value',
                    'currency', 'submission_deadline', 'status', 'assigned_bdm',
                    'documents_required', 'outcome', 'notes'];
        $row = array_intersect_key($data, array_flip($allowed));
        if (empty($row['assigned_bdm'])) $row['assigned_bdm'] = get_staff_user_id();
        if (empty($row['status']))       $row['status']       = 'Identified';
        if (empty($row['currency']))     $row['currency']     = 'AED';
        $row['created_at'] = date('Y-m-d H:i:s');
        $row['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'bdm_tenders', $row);
        return $this->db->insert_id();
    }

    public function update_tender($data, $id)
    {
        $allowed = ['tender_ref', 'title', 'client_company', 'segment', 'emirate', 'value',
                    'currency', 'submission_deadline', 'status', 'assigned_bdm',
                    'documents_required', 'outcome', 'notes'];
        $row = array_intersect_key($data, array_flip($allowed));
        $row['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id)->update(db_prefix() . 'bdm_tenders', $row);
        return $this->db->affected_rows() > 0;
    }

    public function delete_tender($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'bdm_tenders');
        return $this->db->affected_rows() > 0;
    }

    /* ===================================================================== */
    /* 8. MARKETING CAMPAIGNS                                                 */
    /* ===================================================================== */

    public function get_campaigns($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'bdm_campaigns')->row();
        }
        $this->db->order_by('start_date', 'desc');
        return $this->db->get(db_prefix() . 'bdm_campaigns')->result_array();
    }

    public function add_campaign($data)
    {
        $allowed = ['name', 'channel', 'status', 'start_date', 'end_date', 'budget', 'cost_actual',
                    'target_audience', 'recipients', 'delivered', 'opens', 'clicks', 'replies',
                    'leads_generated', 'meetings_booked', 'deals_closed', 'revenue_generated',
                    'notes', 'owner'];
        $row = array_intersect_key($data, array_flip($allowed));
        if (empty($row['status'])) $row['status'] = 'Planned';
        if (empty($row['owner']))  $row['owner']  = get_staff_user_id();
        $row['created_by'] = get_staff_user_id();
        $row['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'bdm_campaigns', $row);
        return $this->db->insert_id();
    }

    public function update_campaign($data, $id)
    {
        $allowed = ['name', 'channel', 'status', 'start_date', 'end_date', 'budget', 'cost_actual',
                    'target_audience', 'recipients', 'delivered', 'opens', 'clicks', 'replies',
                    'leads_generated', 'meetings_booked', 'deals_closed', 'revenue_generated',
                    'notes', 'owner'];
        $row = array_intersect_key($data, array_flip($allowed));
        $this->db->where('id', $id)->update(db_prefix() . 'bdm_campaigns', $row);
        return $this->db->affected_rows() > 0;
    }

    public function delete_campaign($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'bdm_campaigns');
        return $this->db->affected_rows() > 0;
    }

    public function get_campaign_channel_summary()
    {
        $this->db->select('channel,
            COUNT(*) AS campaigns,
            SUM(recipients) AS recipients,
            SUM(opens) AS opens,
            SUM(clicks) AS clicks,
            SUM(leads_generated) AS leads,
            SUM(deals_closed) AS deals,
            SUM(revenue_generated) AS revenue,
            SUM(budget) AS budget,
            SUM(cost_actual) AS cost');
        $this->db->group_by('channel');
        return $this->db->get(db_prefix() . 'bdm_campaigns')->result_array();
    }

    /* ===================================================================== */
    /* 9. KPI TARGETS                                                         */
    /* ===================================================================== */

    public function get_kpi_targets($year = null, $month = null)
    {
        $year  = $year  ?: (int) date('Y');
        $month = $month ?: (int) date('m');
        $this->db->where('period_year', $year);
        $this->db->where('period_month', $month);
        return $this->db->get(db_prefix() . 'bdm_kpi_targets')->result_array();
    }

    public function set_kpi_target($metric_key, $target_value, $year = null, $month = null, $staff_id = 0)
    {
        $year  = $year  ?: (int) date('Y');
        $month = $month ?: (int) date('m');
        $this->db->where('period_year', $year)
                 ->where('period_month', $month)
                 ->where('metric_key', $metric_key)
                 ->where('staff_id', $staff_id);
        $existing = $this->db->get(db_prefix() . 'bdm_kpi_targets')->row();

        if ($existing) {
            $this->db->where('id', $existing->id)->update(db_prefix() . 'bdm_kpi_targets', [
                'target_value' => $target_value,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            return $existing->id;
        }
        $this->db->insert(db_prefix() . 'bdm_kpi_targets', [
            'period_year'  => $year,
            'period_month' => $month,
            'staff_id'     => $staff_id,
            'metric_key'   => $metric_key,
            'target_value' => $target_value,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    /* ===================================================================== */
    /* 10. DASHBOARD / KPI / FUNNEL / FORECAST                                 */
    /* ===================================================================== */

    /**
     * Comprehensive dashboard stats for the BDM Plan KPIs.
     */
    public function get_dashboard_stats()
    {
        $month_start = date('Y-m-01 00:00:00');
        $stats = [];

        $stats['total_brands']        = $this->db->count_all(db_prefix() . 'brands');
        $stats['total_distributors']  = $this->db->count_all(db_prefix() . 'distributors');
        $stats['total_registrations'] = $this->db->count_all(db_prefix() . 'vr_registrations');

        // KPIs (current month)
        $stats['kpi_leads']          = $this->_count_table(db_prefix() . 'leads',         ['dateadded >=' => $month_start]);
        $stats['kpi_visits']         = $this->_count_table(db_prefix() . 'bdm_activities', ['type' => 'Visit', 'date_created >=' => $month_start]);
        $stats['kpi_calls']          = $this->_count_table(db_prefix() . 'bdm_activities', ['type' => 'Call',  'date_created >=' => $month_start]);
        $stats['kpi_proposals']      = $this->_count_table(db_prefix() . 'estimates',     ['date >=' => date('Y-m-01')]);

        $this->db->where('date >=', date('Y-m-01'));
        $stats['kpi_deals'] = $this->db->count_all_results(db_prefix() . 'invoices');

        $this->db->select_sum('total');
        $this->db->where('date >=', date('Y-m-01'));
        $rev = $this->db->get(db_prefix() . 'invoices')->row();
        $stats['kpi_revenue']        = $rev ? (float) $rev->total : 0;
        $stats['kpi_revenue_fmt']    = number_format($stats['kpi_revenue'], 2);

        $this->db->where('created_at >=', $month_start);
        $stats['kpi_partners']       = $this->db->count_all_results(db_prefix() . 'bdm_partners');

        // Email open rate (current month)
        $this->db->select_sum('opens', 'opens')->select_sum('delivered', 'delivered');
        $this->db->where('start_date >=', date('Y-m-01'));
        $em = $this->db->get(db_prefix() . 'bdm_campaigns')->row();
        $stats['kpi_email_open_rate'] = ($em && $em->delivered > 0)
            ? round(($em->opens / $em->delivered) * 100, 1) : 0;

        // LinkedIn growth — pulled from social campaigns
        $this->db->select_sum('leads_generated', 'leads')
                 ->where('channel', 'Social')
                 ->where('start_date >=', date('Y-m-01'));
        $li = $this->db->get(db_prefix() . 'bdm_campaigns')->row();
        $stats['kpi_linkedin_growth'] = $li ? (int) $li->leads : 0;

        // Rollups
        $this->db->where('status', 'New Referral');
        $stats['pending_referrals'] = $this->db->count_all_results(db_prefix() . 'vr_registrations');

        $this->db->where('status', 'Registered');
        $stats['approved_registrations'] = $this->db->count_all_results(db_prefix() . 'vr_registrations');

        $stats['conversion_rate'] = $stats['total_registrations'] > 0
            ? round(($stats['approved_registrations'] / $stats['total_registrations']) * 100, 2) : 0;

        $stats['national_distributors'] = $this->_count_table(db_prefix() . 'distributors', ['classification' => 'National']);
        $stats['intl_distributors']     = $this->_count_table(db_prefix() . 'distributors', ['classification' => 'International']);
        $stats['unique_countries']      = $this->db->select('country_id')->distinct()->where('country_id IS NOT NULL', null, false)
                                                   ->get(db_prefix() . 'brands')->num_rows();

        $this->db->where('date_created >=', date('Y-m-d H:i:s', strtotime('-30 days')));
        $stats['recent_activity'] = $this->db->count_all_results(db_prefix() . 'vr_activity_logs');

        $stats['active_bdms'] = $this->db->select('assigned_bdm')->distinct()
                                         ->where('assigned_bdm IS NOT NULL', null, false)
                                         ->get(db_prefix() . 'vr_registrations')->num_rows();

        // Tenders
        $stats['total_tenders']  = $this->db->count_all(db_prefix() . 'bdm_tenders');
        $this->db->where('status', 'Won');
        $stats['tenders_won']    = $this->db->count_all_results(db_prefix() . 'bdm_tenders');

        // Pipeline value (open registrations expected_value)
        $this->db->select_sum('expected_value');
        $this->db->where_not_in('status', ['Registered', 'Rejected']);
        $rowEv = $this->db->get(db_prefix() . 'vr_registrations')->row();
        $stats['pipeline_value']     = $rowEv ? (float) $rowEv->expected_value : 0;
        $stats['pipeline_value_fmt'] = number_format($stats['pipeline_value'], 2);

        return $stats;
    }

    /**
     * Map dashboard stats vs. monthly targets.
     */
    public function get_kpi_progress()
    {
        $stats   = $this->get_dashboard_stats();
        $targets = [];
        foreach ($this->get_kpi_targets() as $t) {
            $targets[$t['metric_key']] = (float) $t['target_value'];
        }
        $map = [
            'leads'           => $stats['kpi_leads'],
            'visits'          => $stats['kpi_visits'],
            'calls'           => $stats['kpi_calls'],
            'vendor_regs'     => $stats['total_distributors'],
            'client_approvals'=> $stats['approved_registrations'],
            'proposals'       => $stats['kpi_proposals'],
            'deals'           => $stats['kpi_deals'],
            'revenue_aed'     => $stats['kpi_revenue'],
            'partner_regs'    => $stats['kpi_partners'],
            'email_open_rate' => $stats['kpi_email_open_rate'],
            'linkedin_growth' => $stats['kpi_linkedin_growth'],
            'conversion_rate' => $stats['conversion_rate'],
        ];
        $progress = [];
        foreach ($map as $key => $actual) {
            $target = $targets[$key] ?? 0;
            $pct    = $target > 0 ? min(100, round(($actual / $target) * 100, 1)) : 0;
            $progress[$key] = [
                'actual'  => $actual,
                'target'  => $target,
                'percent' => $pct,
            ];
        }
        return $progress;
    }

    public function get_pipeline_distribution()
    {
        $this->db->select('status, count(*) as total');
        $this->db->group_by('status');
        return $this->db->get(db_prefix() . 'vr_registrations')->result_array();
    }

    public function get_pipeline_step_distribution()
    {
        $this->db->select('pipeline_step, count(*) as total, SUM(expected_value) as value_total');
        $this->db->group_by('pipeline_step');
        $this->db->order_by('pipeline_step', 'asc');
        return $this->db->get(db_prefix() . 'vr_registrations')->result_array();
    }

    public function get_segment_distribution()
    {
        $this->db->select('segment, count(*) as total, SUM(expected_value) as value_total');
        $this->db->where('segment IS NOT NULL', null, false);
        $this->db->where('segment !=', '');
        $this->db->group_by('segment');
        return $this->db->get(db_prefix() . 'vr_registrations')->result_array();
    }

    public function get_revenue_trend($months = 6)
    {
        $rows = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-$i months"));
            $start = $m . '-01';
            $end   = date('Y-m-t', strtotime($start));

            $this->db->select_sum('total');
            $this->db->where('date >=', $start);
            $this->db->where('date <=', $end);
            $r = $this->db->get(db_prefix() . 'invoices')->row();
            $rows[] = ['period' => $m, 'revenue' => (float) ($r->total ?? 0)];
        }
        return $rows;
    }

    public function get_bdm_performance()
    {
        $sql = '
            SELECT s.staffid, s.firstname, s.lastname, s.email,
                (SELECT COUNT(*) FROM ' . db_prefix() . 'vr_registrations WHERE assigned_bdm = s.staffid) AS registrations,
                (SELECT COUNT(*) FROM ' . db_prefix() . 'vr_registrations WHERE assigned_bdm = s.staffid AND status = "Registered") AS approved,
                (SELECT COUNT(*) FROM ' . db_prefix() . 'distributors WHERE assigned_bdm = s.staffid) AS distributors,
                (SELECT COUNT(*) FROM ' . db_prefix() . 'bdm_partners WHERE assigned_bdm = s.staffid) AS partners,
                (SELECT COUNT(*) FROM ' . db_prefix() . 'bdm_activities WHERE staff_id = s.staffid AND type = "Visit") AS visits,
                (SELECT COUNT(*) FROM ' . db_prefix() . 'bdm_activities WHERE staff_id = s.staffid AND type = "Call") AS calls,
                (SELECT COALESCE(SUM(won_value),0) FROM ' . db_prefix() . 'vr_registrations WHERE assigned_bdm = s.staffid) AS won_value
            FROM ' . db_prefix() . 'staff s
            WHERE s.active = 1
              AND (
                EXISTS (SELECT 1 FROM ' . db_prefix() . 'vr_registrations WHERE assigned_bdm = s.staffid)
                OR EXISTS (SELECT 1 FROM ' . db_prefix() . 'distributors WHERE assigned_bdm = s.staffid)
                OR EXISTS (SELECT 1 FROM ' . db_prefix() . 'bdm_partners WHERE assigned_bdm = s.staffid)
                OR EXISTS (SELECT 1 FROM ' . db_prefix() . 'bdm_activities WHERE staff_id = s.staffid)
              )
            ORDER BY won_value DESC, registrations DESC
        ';
        return $this->db->query($sql)->result_array();
    }

    /* ===================================================================== */
    /* 11. ACTIVITY LOG / FOLLOW-UPS / EMAIL CADENCE / REFERRAL                */
    /* ===================================================================== */

    public function log_vr_activity($registration_id, $description, $metadata = null)
    {
        $this->db->insert(db_prefix() . 'vr_activity_logs', [
            'registration_id'    => $registration_id,
            'staff_id'           => get_staff_user_id() ?: 0,
            'action_description' => $description,
            'metadata'           => $metadata ? json_encode($metadata) : null,
            'date_created'       => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id', $registration_id)
                 ->update(db_prefix() . 'vr_registrations', ['updated_at' => date('Y-m-d H:i:s')]);
        return $this->db->insert_id();
    }

    public function get_vr_activity_logs($registration_id)
    {
        $this->db->where('registration_id', $registration_id);
        $this->db->order_by('date_created', 'desc');
        return $this->db->get(db_prefix() . 'vr_activity_logs')->result_array();
    }

    /**
     * Schedule the standard 4-touch email cadence (Day 1, 4, 10, 21).
     */
    public function schedule_email_cadence($registration_id)
    {
        $offsets = [1 => 0, 2 => 4, 3 => 10, 4 => 21];
        foreach ($offsets as $step => $days) {
            $this->db->insert(db_prefix() . 'bdm_email_cadence', [
                'registration_id' => $registration_id,
                'step'            => $step,
                'scheduled_at'    => date('Y-m-d H:i:s', strtotime('+' . $days . ' days')),
                'subject'         => 'BDM Touchpoint Step ' . $step,
                'status'          => 'Scheduled',
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function get_due_email_touchpoints()
    {
        $this->db->where('status', 'Scheduled');
        $this->db->where('scheduled_at <=', date('Y-m-d H:i:s'));
        return $this->db->get(db_prefix() . 'bdm_email_cadence')->result_array();
    }

    public function mark_cadence_sent($id)
    {
        $this->db->where('id', $id)->update(db_prefix() . 'bdm_email_cadence', [
            'status'  => 'Sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Refer Lead → BDM (creates master_company + vr_registration + activity log).
     */
    public function refer_lead_to_bdm($lead_id, $data)
    {
        $company_name = trim($data['company'] ?? '');
        if ($company_name === '') return false;

        // Master company (idempotent)
        $this->db->where('company_name', $company_name);
        $existing = $this->db->get(db_prefix() . 'vr_master_companies')->row();
        if ($existing) {
            $company_id = $existing->id;
        } else {
            $this->db->insert(db_prefix() . 'vr_master_companies', [
                'company_name' => $company_name,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            $company_id = $this->db->insert_id();
        }

        $registration_id = $this->add_vr_registration([
            'company_id'        => $company_id,
            'lead_id'           => $lead_id,
            'registration_type' => $data['registration_type'] ?? 'Supplier Onboarding',
            'segment'           => $data['segment'] ?? null,
            'status'            => 'New Referral',
            'pipeline_step'     => 1,
            'priority'          => $data['priority'] ?? 'Medium',
            'expected_value'    => isset($data['expected_value']) ? (float) $data['expected_value'] : 0,
            'currency'          => 'AED',
            'lead_source'       => 'Perfex Lead Referral',
            'assigned_bdm'      => $data['assigned_bdm'] ?? get_staff_user_id(),
            'notes'             => $data['notes'] ?? '',
        ]);

        if ($registration_id) {
            $this->log_vr_activity($registration_id, 'Lead [#' . $lead_id . '] referred to BDM.');
            add_notification([
                'description' => 'New BDM Lead Referral: ' . $company_name,
                'tostaffid'   => $data['assigned_bdm'] ?? get_staff_user_id(),
                'fromcompany' => true,
                'link'        => 'bdm_operations/vr_registrations',
            ]);
            log_activity('Lead Referred to BDM [Lead: ' . $lead_id . ', Reg: ' . $registration_id . ']');
        }
        return $registration_id;
    }

    public function get_overdue_followups_list()
    {
        $today = date('Y-m-d');

        // Distributors with overdue follow-ups
        $this->db->where('follow_up_date <', $today);
        $this->db->where('follow_up_date IS NOT NULL', null, false);
        $dists = $this->db->get(db_prefix() . 'distributors')->result_array();
        foreach ($dists as &$d) {
            $d['source'] = 'distributor';
        }

        // VR registrations with overdue follow-ups
        $this->db->select(db_prefix() . 'vr_registrations.*, ' . db_prefix() . 'vr_master_companies.company_name AS name');
        $this->db->join(
            db_prefix() . 'vr_master_companies',
            db_prefix() . 'vr_master_companies.id = ' . db_prefix() . 'vr_registrations.company_id',
            'left'
        );
        $this->db->where(db_prefix() . 'vr_registrations.follow_up_date <', $today);
        $this->db->where(db_prefix() . 'vr_registrations.follow_up_date IS NOT NULL', null, false);
        $this->db->where_not_in(db_prefix() . 'vr_registrations.status', ['Registered', 'Rejected']);
        $vrs = $this->db->get(db_prefix() . 'vr_registrations')->result_array();
        foreach ($vrs as &$v) {
            $v['source'] = 'registration';
        }

        return array_merge($dists, $vrs);
    }

    public function get_inactive_registrations($days = 3)
    {
        $threshold = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
        $this->db->select(db_prefix() . 'vr_registrations.*, ' . db_prefix() . 'vr_master_companies.company_name');
        $this->db->join(
            db_prefix() . 'vr_master_companies',
            db_prefix() . 'vr_master_companies.id = ' . db_prefix() . 'vr_registrations.company_id',
            'left'
        );
        $this->db->where(db_prefix() . 'vr_registrations.updated_at <', $threshold);
        $this->db->where_not_in(db_prefix() . 'vr_registrations.status', ['Registered', 'Rejected']);
        return $this->db->get(db_prefix() . 'vr_registrations')->result_array();
    }

    /* --------- Internal helpers --------- */

    private function _count_table($table, $where = [])
    {
        if (!empty($where)) $this->db->where($where);
        return $this->db->count_all_results($table);
    }
}
