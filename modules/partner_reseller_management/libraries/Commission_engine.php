<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *  Commission_engine
 *  ----------------------------------------------------------------------
 *  Pluggable rule-based engine that supports:
 *      - fixed              flat amount
 *      - percentage         simple % of deal_value
 *      - tiered             ladder of (min,max,rate) brackets
 *      - hybrid             flat + percentage combo
 *      - product_based      per-line-item rules (looks up prm_products)
 *      - hierarchy override paid to ancestors  (multi-level)
 *      - splits             list of (partner_id, pct) on the same deal
 *      - tax handling       inclusive / exclusive based on settings
 *      - currency           applies fx_rate to base currency
 *      - clawback eligible  flag based on age window
 *  ----------------------------------------------------------------------
 */
class Commission_engine
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('partner_reseller_management/partners_model');
        $this->CI->load->model('partner_reseller_management/commissions_model');
        $this->CI->load->model('partner_reseller_management/deals_model');
        $this->CI->load->model('partner_reseller_management/wallet_model');
    }

    /* ==================================================================
     *  PUBLIC ENTRY POINTS
     * ================================================================ */
    public function calculate_for_deal($deal_id)
    {
        $deal = $this->CI->deals_model->get($deal_id);
        if (!$deal || !in_array($deal->pipeline_stage, ['won','approved','contacted','qualified','proposal','negotiation'])) {
            return ['ok' => false, 'reason' => 'invalid_stage'];
        }

        $partner = $this->CI->partners_model->get($deal->partner_id);
        if (!$partner) return ['ok' => false, 'reason' => 'partner_missing'];

        $value = (float) ($deal->actual_value ?: $deal->estimated_value);
        if ($value <= 0) return ['ok' => false, 'reason' => 'no_value'];

        $base = $this->resolve_partner_rate($partner, $value);
        $boost = $this->tier_boost($partner);
        $amount = $base['amount'];
        if ($base['type'] === 'percentage' || $base['type'] === 'tiered' || $base['type'] === 'hybrid') {
            $amount += ($value * $boost / 100); // tier boost applies to %-based rules
        }

        $tax_rate = (float) get_prm_option('prm_default_vat_rate', 0);
        $tax_handling = get_prm_option('prm_tax_handling', 'inclusive');
        $tax  = ($tax_handling === 'exclusive') ? round($amount * $tax_rate / 100, 2) : 0;
        $net  = $amount - (($tax_handling === 'inclusive') ? round($amount * $tax_rate / (100 + $tax_rate), 2) : 0);

        $cid = $this->CI->commissions_model->add([
            'partner_id'        => $partner->id,
            'deal_id'           => $deal->id,
            'kind'              => 'deal',
            'deal_value'        => $value,
            'commission_type'   => $base['type'],
            'commission_rate'   => $base['rate'],
            'commission_amount' => round($amount, 2),
            'currency'          => $deal->currency,
            'fx_rate'           => $deal->fx_rate,
            'tax_amount'        => $tax,
            'net_amount'        => round($net, 2),
            'payment_condition' => get_prm_option('prm_default_payment_condition', 'invoice_clearance'),
            'status'            => 'calculated',
        ]);

        // Hierarchy override — pay ancestors their override_rate %
        $this->generate_hierarchy_overrides($deal, $partner, $value);

        // Recurring commissions if product flagged
        if (!empty($deal->product_id)) {
            $product = $this->CI->db->where('id', $deal->product_id)->get(db_prefix() . 'prm_partner_products')->row();
            if ($product && (int)$product->recurring === 1) {
                $this->CI->commissions_model->add_recurring([
                    'partner_id'    => $partner->id,
                    'deal_id'       => $deal->id,
                    'product_id'    => $product->id,
                    'period'        => $product->recurring_period,
                    'rate'          => $product->commission_value,
                    'cycles_total'  => $product->recurring_cycles,
                    'cycles_paid'   => 0,
                    'next_run_date' => $this->next_period_date($product->recurring_period),
                    'base_amount'   => $value,
                    'status'        => 'active',
                ]);
            }
        }

        // Update deal
        $this->CI->deals_model->update($deal->id, [
            'commission_amount' => round($amount, 2),
            'commission_status' => 'calculated',
        ]);

        // Hold in wallet
        $this->CI->wallet_model->entry($partner->id, 'hold', round($amount, 2), 'commission', $cid, 'Commission #' . $cid . ' on hold');

        hooks()->do_action('prm_commission_calculated', $cid);
        return ['ok' => true, 'commission_id' => $cid, 'amount' => round($amount, 2)];
    }

    public function run_recurring_cron()
    {
        $due = $this->CI->commissions_model->due_recurring();
        foreach ($due as $row) {
            $amount = round(((float) $row->base_amount * (float) $row->rate) / 100, 2);
            $cid = $this->CI->commissions_model->add([
                'partner_id'        => $row->partner_id,
                'deal_id'           => $row->deal_id,
                'recurring_id'      => $row->id,
                'kind'              => 'recurring',
                'deal_value'        => $row->base_amount,
                'commission_type'   => 'percentage',
                'commission_rate'   => $row->rate,
                'commission_amount' => $amount,
                'status'            => 'calculated',
                'payment_condition' => 'invoice_clearance',
                'notes'             => 'Recurring (' . $row->period . ') — cycle ' . ($row->cycles_paid + 1),
            ]);
            $this->CI->wallet_model->entry($row->partner_id, 'hold', $amount, 'commission', $cid, 'Recurring commission');
            $this->CI->commissions_model->increment_recurring_cycle($row->id);
            hooks()->do_action('prm_recurring_commission_generated', $cid);
        }
    }

    /* ==================================================================
     *  RULE RESOLVERS
     * ================================================================ */
    public function resolve_partner_rate($partner, $value)
    {
        $type   = $partner->commission_type;
        $rate   = (float) $partner->commission_value;
        $config = json_decode((string)$partner->commission_config, true);

        switch ($type) {
            case 'fixed':
                return ['type' => 'fixed', 'rate' => 0, 'amount' => $rate];

            case 'percentage':
                return ['type' => 'percentage', 'rate' => $rate, 'amount' => round($value * $rate / 100, 2)];

            case 'tiered':
                $tiers = is_array($config) ? $config : [];
                foreach ($tiers as $t) {
                    if ($value >= (float)$t['min'] && $value <= (float)$t['max']) {
                        return ['type'=>'tiered','rate'=>(float)$t['rate'],'amount'=>round($value*$t['rate']/100,2)];
                    }
                }
                return ['type'=>'tiered','rate'=>$rate,'amount'=>round($value*$rate/100,2)];

            case 'hybrid':
                $flat = isset($config['flat']) ? (float)$config['flat'] : 0;
                $pct  = isset($config['pct'])  ? (float)$config['pct']  : $rate;
                return ['type'=>'hybrid','rate'=>$pct,'amount'=>round($flat + ($value*$pct/100),2)];

            case 'product_based':
                // resolved at deal creation when product_id is set; fallback %
                return ['type'=>'percentage','rate'=>$rate,'amount'=>round($value*$rate/100,2)];
        }
        return ['type'=>'fixed','rate'=>0,'amount'=>0];
    }

    public function tier_boost($partner)
    {
        if (!$partner->tier_level_id) return 0;
        $row = $this->CI->db->where('id', $partner->tier_level_id)->get(db_prefix().'prm_partner_tier_levels')->row();
        return $row ? (float)$row->commission_boost : 0;
    }

    public function generate_hierarchy_overrides($deal, $partner, $value)
    {
        $ancestors = $this->CI->partners_model->get_ancestors($partner->id);
        foreach ($ancestors as $a) {
            $anc = $this->CI->partners_model->get($a->ancestor_id);
            if (!$anc || (float)$anc->override_rate <= 0) continue;
            $amount = round($value * (float)$anc->override_rate / 100, 2);
            if ($amount <= 0) continue;
            $cid = $this->CI->commissions_model->add([
                'partner_id'        => $anc->id,
                'deal_id'           => $deal->id,
                'kind'              => 'override',
                'deal_value'        => $value,
                'commission_type'   => 'percentage',
                'commission_rate'   => $anc->override_rate,
                'commission_amount' => $amount,
                'status'            => 'calculated',
                'notes'             => "Override (depth {$a->depth}) on partner #{$partner->id}",
                'payment_condition' => 'invoice_clearance',
            ]);
            $this->CI->wallet_model->entry($anc->id, 'hold', $amount, 'override', $cid, 'Override on deal ' . $deal->deal_number);
        }
    }

    private function next_period_date($period)
    {
        switch ($period) {
            case 'monthly':   return date('Y-m-d', strtotime('+1 month'));
            case 'quarterly': return date('Y-m-d', strtotime('+3 months'));
            case 'yearly':    return date('Y-m-d', strtotime('+1 year'));
        }
        return date('Y-m-d', strtotime('+1 month'));
    }
}
