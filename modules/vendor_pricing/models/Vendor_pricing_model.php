<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_pricing_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Submit vendor pricing for a PO
     * @param  int $po_id
     * @param  array $data (item_code => vendor_price)
     * @return boolean
     */
    public function submit_vendor_pricing($po_id, $data)
    {
        $affectedRows = 0;
        
        // Remove existing pending submissions for this PO
        $this->db->where('pur_order_id', $po_id);
        $this->db->where('status', 'pending');
        $this->db->delete(db_prefix() . 'vendor_pricing_po_details');

        foreach ($data as $item_code => $price) {
            $insert_data = [
                'pur_order_id' => $po_id,
                'item_code'    => $item_code,
                'vendor_price' => $price,
                'status'       => 'pending',
                'date_submitted' => date('Y-m-d H:i:s')
            ];

            $this->db->insert(db_prefix() . 'vendor_pricing_po_details', $insert_data);
            $affectedRows++;
        }

        return $affectedRows > 0;
    }

    /**
     * Get submitted vendor pricing for a specific PO
     */
    public function get_vendor_pricing($po_id)
    {
        $this->db->where('pur_order_id', $po_id);
        return $this->db->get(db_prefix() . 'vendor_pricing_po_details')->result_array();
    }

    /**
     * Get all POs that have vendor pricing submissions
     */
    public function get_pos_with_pricing()
    {
        $this->db->select('v.pur_order_id, v.status, MAX(v.date_submitted) as date_submitted, p.pur_order_number, p.pur_order_name, v2.company as vendor_name');
        $this->db->from(db_prefix() . 'vendor_pricing_po_details v');
        $this->db->join(db_prefix() . 'pur_orders p', 'p.id = v.pur_order_id', 'left');
        $this->db->join(db_prefix() . 'pur_vendor v2', 'v2.userid = p.vendor', 'left');
        $this->db->group_by('v.pur_order_id, v.status, p.pur_order_number, p.pur_order_name, v2.company');
        $this->db->order_by('date_submitted', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Accept vendor pricing
     */
    public function accept_vendor_pricing($po_id)
    {
        $vendor_prices = $this->get_vendor_pricing($po_id);
        if(empty($vendor_prices)) return false;

        $this->load->model('purchase/purchase_model');

        foreach ($vendor_prices as $vp) {
            $this->db->where('pur_order', $po_id);
            $this->db->where('item_code', $vp['item_code']);
            $detail = $this->db->get(db_prefix() . 'pur_order_detail')->row();
            
            if ($detail) {
                $qty = $detail->quantity;
                $new_price = $vp['vendor_price'];
                $into_money = $qty * $new_price;
                
                $total = $into_money; // Assuming basic setup where no tax logic strictly applies to the core row unless recalced natively
                
                $this->db->where('pur_order', $po_id);
                $this->db->where('item_code', $vp['item_code']);
                $this->db->update(db_prefix() . 'pur_order_detail', [
                    'unit_price' => $new_price,
                    'into_money' => $into_money,
                    'total'      => $total,
                    'total_money'=> $total
                ]);
            }
        }

        // Recalculate PO totals
        $this->db->select_sum('total');
        $this->db->select_sum('into_money');
        $this->db->where('pur_order', $po_id);
        $res = $this->db->get(db_prefix() . 'pur_order_detail')->row();

        if ($res) {
            $this->db->where('id', $po_id);
            $this->db->update(db_prefix() . 'pur_orders', [
                'subtotal' => $res->into_money,
                'total'    => $res->total
            ]);
        }

        $this->db->where('pur_order_id', $po_id);
        $this->db->update(db_prefix() . 'vendor_pricing_po_details', ['status' => 'accepted']);

        return true;
    }

    /**
     * Reject vendor pricing
     */
    public function reject_vendor_pricing($po_id)
    {
        $this->db->where('pur_order_id', $po_id);
        $this->db->update(db_prefix() . 'vendor_pricing_po_details', ['status' => 'rejected']);
        return true;
    }

    /**
     * Generate HTML for Vendor PDF view
     */
    public function get_vendor_pricing_pdf_html($pur_order_id, $vendor_prices)
    {
        $this->load->model('purchase/purchase_model');
        $pur_order = $this->purchase_model->get_pur_order($pur_order_id);
        $pur_order_detail = $this->purchase_model->get_pur_order_detail($pur_order_id);
        
        $organization_info = '<div style="color:#424242; font-size:14px" border="1px">';
        $organization_info .= format_organization_info();
        $organization_info .= '</div>';

        $info_right_column ='<span style="font-weight:bold;font-size:24px;" align="right">'.mb_strtoupper(_l('purchase_order')).'</span><br/>';
        $invoice_number = html_entity_decode($pur_order->pur_order_number.' - '.$pur_order->pur_order_name);

        $invoice_info1 = '<div style="font-size:14px" border="1px" align="right">Invoice <b style="color:#4e4e4e;">' . $invoice_number.'</b>';
        $invoice_info1 .= '<br />' . _l('invoice_data_date') . ' ' . _d($pur_order->order_date) . '<br>';
        $invoice_info1 .= '<br> <br> </div>';

        $html = '<table >
            <tbody border="1px">
              <tr >
                <td style="width:50%">'.pdf_logo_url().'</td>
                <td style="width:50%">'.$info_right_column.'</td>
              </tr>
              <tr>
                <td >'. $organization_info .'</td>
                <td align="right">'.$invoice_info1.'</td>
              </tr>
            </tbody>
          </table>
          <table class="table" style="font-size:14px">
            <tbody>
              <tr>
                <td style="width:50%"><b>Vendor:</b><div border="1px">'. get_vendor_company_name($pur_order->vendor).'</div></td>
                <td style="width:50%"><b>Ship From:</b><div border="1px">'. get_vendor_company_name($pur_order->vendor).'</div></td>
              </tr>
            </tbody>
          </table><br><br>';

        $html .= '<table class="table purorder-item" border="1px" style="font-size:14px">
        <thead>
          <tr bgcolor="#f9f9f9">
            <th class="thead-dark" width="3%" align="center">#</th>
            <th class="thead-dark" width="22%">'._l('item').'</th>
            <th class="thead-dark" align="right" width="6%">'._l('quantity').'</th>
            <th class="thead-dark" align="right" width="9%">Unit Price</th>
            <th class="thead-dark" align="right" width="10%">Into money</th>
            <th class="thead-dark" align="right" width="10%">Tax</th>
            <th class="thead-dark" align="right" width="10%"><b>Tax Amount</b></th>
            <th class="thead-dark" align="right" width="10%">Sub total</th>
            <th class="thead-dark" align="right" width="8%">Discount</th>
            <th class="thead-dark" align="right" width="8%">Discount (money)</th>
            <th class="thead-dark" align="right" width="10%">'._l('total').'</th>
          </tr>
        </thead>
        <tbody>';
        
        $i = 1;
        $subtotal = 0;
        $taxes_totals = [];

        foreach($pur_order_detail as $row){
            $item_name = $row['description'] ? $row['description'] : $row['item_name'];
            $long_desc = isset($row['long_description']) ? $row['long_description'] : '';
            if($long_desc != ''){
                $item_name .= '<br><span style="color:#777;">'.$long_desc.'</span>';
            }
            $qty = $row['quantity'];
            $v_price = isset($vendor_prices[$row['item_code']]) ? (float)$vendor_prices[$row['item_code']] : 0;
            
            // Into Money
            $into_money = $qty * $v_price;
            
            // Tax Calculation
            $tax_names = [];
            if($row['tax'] != '' && $row['tax'] != null) {
                $tax_arr = explode('|', $row['tax']);
                $tax_rate_arr = explode('|', $row['tax_rate']);
                foreach($tax_arr as $k => $tn) {
                    $rate = isset($tax_rate_arr[$k]) ? $tax_rate_arr[$k] : 0;
                    $tax_names[] = ['name' => $tn . ' (' . $rate . '%)', 'rate' => $rate];
                }
            } else {
                if(isset($row['tax_name']) && $row['tax_name'] != '') {
                    $tax_names[] = ['name' => $row['tax_name'] . ' (' . $row['tax_rate'] . '%)', 'rate' => $row['tax_rate']];
                }
            }
            
            $row_tax_amount = 0;
            foreach($tax_names as $tn) {
                $t_rate = $tn['rate'];
                $t_amt = ($into_money * ($t_rate / 100));
                $row_tax_amount += $t_amt;
                
                $t_name = $tn['name'];
                if(!isset($taxes_totals[$t_name])) {
                    $taxes_totals[$t_name] = 0;
                }
                $taxes_totals[$t_name] += $t_amt;
            }

            // Tax Amount column acts as Sub_total
            $tax_amount_col = $into_money + $row_tax_amount;
            
            $discount_percent = isset($row['discount_%']) ? $row['discount_%'] : 0;
            $discount_money = $tax_amount_col * ($discount_percent / 100);
            $line_total = $tax_amount_col - $discount_money;

            $subtotal += $into_money;

            $html .= '<tr nobr="true" class="sortable">
                <td align="center">'.$i.'</td>
                <td><b>'.$item_name.'</b></td>
                <td align="right">'.number_format($qty, 2).'</td>
                <td align="right">'.app_format_money($v_price,'').'</td>
                <td align="right">'.app_format_money($into_money,'').'</td>
                <td align="right">'.app_format_money($row_tax_amount,'').'</td>
                <td align="right">'.app_format_money($tax_amount_col,'').'</td>
                <td align="right"></td>
                <td align="right">'.number_format($discount_percent, 2).'%</td>
                <td align="right">'.app_format_money($discount_money,'').'</td>
                <td align="right">'.app_format_money($line_total,'').'</td>
              </tr>';
            $i++;
        }
        
        $html .=  '</tbody>
        </table><br><br>';

        $total_discount = 0;
        if($pur_order->discount_percent > 0) {
            $total_discount = $subtotal * ($pur_order->discount_percent / 100);
        }
        
        $global_tax_total = 0;
        foreach($taxes_totals as $t_amt) {
            $global_tax_total += $t_amt;
        }

        $grand_total = ($subtotal - $total_discount) + $global_tax_total;

        $html .= '<table class="table text-right" style="font-size:14px"><tbody>';
        $html .= '<tr id="subtotal">
                    <td width="33%"></td>
                     <td><b>'._l('subtotal').'</b></td>
                     <td class="subtotal">
                        '.app_format_money($subtotal,'').'
                     </td>
                  </tr>';
        
        if($pur_order->discount_percent > 0) {
            $html .= '<tr id="discount">
                        <td width="33%"></td>
                         <td><b>Discount ('.app_format_money($pur_order->discount_percent, '').'%)</b></td>
                         <td class="subtotal">
                            -'.app_format_money($total_discount,'').'
                         </td>
                      </tr>';
        }

        foreach($taxes_totals as $taxName => $taxAmt) {
            $html .= '<tr id="taxtotal">
                        <td width="33%"></td>
                         <td><b>'.$taxName.'</b></td>
                         <td class="subtotal">
                            '.app_format_money($taxAmt,'').'
                         </td>
                      </tr>';
        }
                  
        $html .= '<tr id="grandtotal">
                 <td width="33%"></td>
                 <td><b>'. _l('total').'</b></td>
                 <td class="subtotal">
                    '. app_format_money($grand_total, '').'
                 </td>
              </tr>';
        $html .= ' </tbody></table>';
        
        $html .= '<br><br>';
        $html .= '<link href="' . module_dir_url('purchase', 'assets/css/pur_order_pdf.css') . '"  rel="stylesheet" type="text/css" />';
        return $html;
    }
}
