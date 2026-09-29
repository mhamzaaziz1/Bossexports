<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fix_inventory_model extends App_Model
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get reconciliation data
     */
    public function get_reconciliation($item_id = '', $warehouse_id = '') {
        $sql = "SELECT 
                    i.id as item_id,
                    i.description as item_name,
                    i.sku_code,
                    w.warehouse_id,
                    w.warehouse_name,
                    IFNULL(curr.actual_qty, 0) as actual_qty
                FROM " . db_prefix() . "items i
                CROSS JOIN " . db_prefix() . "warehouse w
                LEFT JOIN (
                    SELECT commodity_id, warehouse_id, SUM(inventory_number) as actual_qty
                    FROM " . db_prefix() . "inventory_manage
                    GROUP BY commodity_id, warehouse_id
                ) curr ON curr.commodity_id = i.id AND curr.warehouse_id = w.warehouse_id";

        $append_where = ' WHERE 1=1';
        if ($item_id != '') { $append_where .= " AND i.id = " . $this->db->escape($item_id); }
        if ($warehouse_id != '') { $append_where .= " AND w.warehouse_id = " . $this->db->escape($warehouse_id); }
        $sql .= $append_where;

        $base_data = $this->db->query($sql)->result_array();

        // Single pass over ALL transactions to build accurate ledger history
        $this->db->select('id, commodity_id, status, old_quantity, quantity, warehouse_id, from_stock_name, to_stock_name, goods_receipt_id');
        if ($item_id != '') $this->db->where('commodity_id', $item_id);
        $this->db->order_by('commodity_id ASC, date_add ASC, id ASC');
        $transactions = $this->db->get(db_prefix() . 'goods_transaction_detail')->result_array();

        $ledger = [];
        foreach ($transactions as $t) {
            $c_id = $t['commodity_id'];
            $qty = (float)$t['quantity'];
            $status = (int)$t['status'];
            
            $w_ids = array_filter(explode(',', $t['warehouse_id']));
            $from_w_ids = array_filter(explode(',', $t['from_stock_name']));
            $to_w_ids = array_filter(explode(',', $t['to_stock_name']));
            
            $involved = array_unique(array_merge($w_ids, $from_w_ids, $to_w_ids));

            foreach ($involved as $w_id) {
                if ($warehouse_id != '' && $w_id != $warehouse_id) continue;
                $key = $c_id . '_' . $w_id;
                if (!isset($ledger[$key])) {
                    $ledger[$key] = [
                        'running_total' => 0,
                        'opening_qty' => 0,
                        'receipt_qty' => 0,
                        'delivery_qty' => 0,
                        'adjustment_qty' => 0,
                        'transfer_in_qty' => 0,
                        'transfer_out_qty' => 0
                    ];
                }

                $is_from = in_array($w_id, $from_w_ids);
                $is_to = in_array($w_id, $to_w_ids);

                if ($status == 1) { 
                    $ledger[$key]['running_total'] += $qty;
                    if (empty($t['goods_receipt_id']) || $t['goods_receipt_id'] == 0) {
                        $ledger[$key]['opening_qty'] += $qty;
                    } else {
                        $ledger[$key]['receipt_qty'] += $qty;
                    }
                } elseif ($status == 2) { 
                    $ledger[$key]['running_total'] -= $qty;
                    $ledger[$key]['delivery_qty'] += $qty;
                } elseif ($status == 3) { 
                    $diff = $qty - $ledger[$key]['running_total'];
                    $ledger[$key]['running_total'] = $qty; 
                    $ledger[$key]['adjustment_qty'] += $diff;
                } elseif ($status == 4) { 
                    if ($is_from && !$is_to) {
                        $ledger[$key]['running_total'] -= $qty;
                        $ledger[$key]['transfer_out_qty'] += $qty;
                    } elseif ($is_to && !$is_from) {
                        $ledger[$key]['running_total'] += $qty;
                        $ledger[$key]['transfer_in_qty'] += $qty;
                    }
                } else {
                    $ledger[$key]['running_total'] += $qty;
                    $ledger[$key]['opening_qty'] += $qty;
                }
            }
        }

        $results = [];
        foreach ($base_data as $row) {
            $key = $row['item_id'] . '_' . $row['warehouse_id'];
            $l = isset($ledger[$key]) ? $ledger[$key] : [
                'running_total' => 0,
                'opening_qty' => 0,
                'receipt_qty' => 0,
                'delivery_qty' => 0,
                'adjustment_qty' => 0,
                'transfer_in_qty' => 0,
                'transfer_out_qty' => 0
            ];

            $row['expected_qty'] = $l['running_total'];
            $row['opening_qty'] = $l['opening_qty'];
            $row['receipt_qty'] = $l['receipt_qty'];
            $row['delivery_qty'] = $l['delivery_qty'];
            $row['adjustment_qty'] = $l['adjustment_qty'];
            $row['transfer_in_qty'] = $l['transfer_in_qty'];
            $row['transfer_out_qty'] = $l['transfer_out_qty'];

            if (abs((float)$row['actual_qty'] - (float)$row['expected_qty']) > 0.001) {
                $results[] = $row;
            }
        }

        return $results;
    }

    /**
     * Fool-proof recalculation of the entire transaction history for an item & warehouse.
     * Sequences through all transactions, calculates old vs new quantities,
     * and guarantees the ledger history logically maps to the current total.
     */
    public function sync_item_ledger($item_id, $warehouse_id) {
        $sql = "SELECT id, status, old_quantity, quantity, warehouse_id, from_stock_name, to_stock_name 
                FROM " . db_prefix() . "goods_transaction_detail 
                WHERE commodity_id = " . $this->db->escape($item_id) . " 
                AND (FIND_IN_SET(" . $this->db->escape($warehouse_id) . ", warehouse_id) 
                     OR FIND_IN_SET(" . $this->db->escape($warehouse_id) . ", from_stock_name) 
                     OR FIND_IN_SET(" . $this->db->escape($warehouse_id) . ", to_stock_name))
                ORDER BY date_add ASC, id ASC";

        $transactions = $this->db->query($sql)->result_array();

        // Calculate chronological running total starting from zero
        $running_total = 0;

        foreach ($transactions as $t) {
            $qty = (float)$t['quantity'];
            $status = (int)$t['status'];
            
            $is_from = false;
            if ($t['from_stock_name'] != '' && $t['from_stock_name'] != '0') {
                $is_from = (strpos((string)$t['from_stock_name'], (string)$warehouse_id) !== false);
            }
            $is_to = false;
            if ($t['to_stock_name'] != '' && $t['to_stock_name'] != '0') {
                $is_to = (strpos((string)$t['to_stock_name'], (string)$warehouse_id) !== false);
            }

            $current_balance = $running_total;

            if ($status == 1) { 
                // Stock Import / Receipt
                $running_total += $qty;
                $t_old_qty = $current_balance;
            } elseif ($status == 2) { 
                // Stock Export / Delivery
                $running_total -= $qty;
                // Perfex explicitly tracks 'old_quantity' as the remaining balance AFTER delivery for status=2.
                $t_old_qty = $running_total;
            } elseif ($status == 3) { 
                // Loss Adjustment
                $t_old_qty = $current_balance;
                $running_total = $qty; // Quantity represents new absolute value
            } elseif ($status == 4) { 
                // Internal Transfer
                if ($is_from && !$is_to) {
                    $running_total -= $qty;
                    $t_old_qty = $current_balance; // Perfex expects 'before quantity' for transfers
                } elseif ($is_to && !$is_from) {
                    $t_old_qty = $current_balance;
                    $running_total += $qty;
                } else {
                    $t_old_qty = $current_balance;
                }
            } else {
                $running_total += $qty;
                $t_old_qty = $current_balance;
            }

            // Sync the transaction row with exactly matched old/new quantities
            if ((float)$t['old_quantity'] !== (float)$t_old_qty) {
                $this->db->where('id', $t['id']);
                $this->db->update(db_prefix() . 'goods_transaction_detail', ['old_quantity' => $t_old_qty]);
            }
        }

        return $running_total;
    }

    /**
     * Fix a single item/warehouse gap
     */
    public function fix_item($item_id, $warehouse_id = '') {
        if ($warehouse_id == '') {
            $warehouses = $this->db->get(db_prefix() . 'warehouse')->result_array();
            foreach ($warehouses as $w) {
                $this->fix_item($item_id, $w['warehouse_id']);
            }
            return true;
        }

        // 1. Calculate what the ledger currently sums up to (this inherently repairs old_quantity line gaps)
        $current_ledger_total = $this->sync_item_ledger($item_id, $warehouse_id);

        // 2. Update the actual inventory table to match the absolute voucher truth
        $this->db->where(['commodity_id' => $item_id, 'warehouse_id' => $warehouse_id]);
        $this->db->order_by('id', 'DESC');
        $inventory = $this->db->get(db_prefix() . 'inventory_manage')->row();

        if ($inventory) {
            $this->db->where('id', $inventory->id)->update(db_prefix() . 'inventory_manage', ['inventory_number' => $current_ledger_total]);
        } else if ($current_ledger_total != 0) {
            $this->db->where('id', $item_id);
            $item = $this->db->get(db_prefix().'items')->row();
            $this->db->insert(db_prefix() . 'inventory_manage', [
                'commodity_id' => $item_id,
                'warehouse_id' => $warehouse_id,
                'inventory_number' => $current_ledger_total,
                'purchase_price' => (isset($item->purchase_price) ? $item->purchase_price : 0),
                'lot_number' => 'FIXED_MODULE_RECONCILE',
            ]);
        }

        return true;
    }

    /**
     * Fix all gaps
     */
    public function fix_all() {
        $reconciliations = $this->get_reconciliation();
        foreach ($reconciliations as $data) {
            $this->fix_item($data['item_id'], $data['warehouse_id']);
        }
        return true;
    }

    /**
     * Get audit history for a specific item
     */
    public function get_audit_history($item_id, $warehouse_id = '') {
        $sql = "SELECT id, status, old_quantity, quantity, warehouse_id, from_stock_name, to_stock_name, date_add, note 
                FROM " . db_prefix() . "goods_transaction_detail 
                WHERE commodity_id = " . $this->db->escape($item_id);
        
        if ($warehouse_id != '') {
            $sql .= " AND (FIND_IN_SET(" . $this->db->escape($warehouse_id) . ", warehouse_id) 
                      OR FIND_IN_SET(" . $this->db->escape($warehouse_id) . ", from_stock_name) 
                      OR FIND_IN_SET(" . $this->db->escape($warehouse_id) . ", to_stock_name))";
        }
        
        $sql .= " ORDER BY date_add ASC, id ASC";

        $transactions = $this->db->query($sql)->result_array();

        // Calculate chronological running total starting from zero
        $running_total = 0;
        $history = [];

        foreach ($transactions as $t) {
            $qty = (float)$t['quantity'];
            $status = (int)$t['status'];
            
            // To figure out if it affected our selected warehouse
            $is_from = false;
            $is_to = false;
            if ($warehouse_id != '') {
                if ($t['from_stock_name'] != '' && $t['from_stock_name'] != '0') {
                    $is_from = (strpos((string)$t['from_stock_name'], (string)$warehouse_id) !== false);
                }
                if ($t['to_stock_name'] != '' && $t['to_stock_name'] != '0') {
                    $is_to = (strpos((string)$t['to_stock_name'], (string)$warehouse_id) !== false);
                }
            }

            $current_balance = $running_total;
            $type = 'Unknown';
            $change = 0;

            if ($status == 1) { 
                $type = 'Receipt / Import';
                $change = $qty;
                $running_total += $qty;
            } elseif ($status == 2) { 
                $type = 'Delivery / Export';
                $change = -$qty;
                $running_total -= $qty;
            } elseif ($status == 3) { 
                $type = 'Loss Adjustment';
                $change = $qty - $current_balance;
                $running_total = $qty; 
            } elseif ($status == 4) { 
                $type = 'Internal Transfer';
                if ($warehouse_id != '') {
                    if ($is_from && !$is_to) {
                        $change = -$qty;
                        $running_total -= $qty;
                    } elseif ($is_to && !$is_from) {
                        $change = $qty;
                        $running_total += $qty;
                    }
                } else {
                    $change = 0;
                }
            } else {
                $type = 'Other (Status ' . $status . ')';
                $change = $qty;
                $running_total += $qty;
            }

            $t['calculated_change'] = $change;
            $t['theoretical_balance'] = $running_total;
            $t['transaction_type_name'] = $type;
            
            $history[] = $t;
        }

        return $history;
    }
}
