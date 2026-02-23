<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customer_analytics_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * RFM Analysis
     * Scores customers 1-5 on Recency, Frequency, Monetary.
     */
    public function get_rfm_segmentation()
    {
        // 1. Fetch raw data
        $this->db->select('clientid, COUNT(id) as frequency, SUM(total) as monetary, MAX(date) as last_purchase');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('status !=', 6); // Exclude drafts
        $this->db->where('status !=', 5); // Exclude cancelled
        $this->db->group_by('clientid');
        $customers = $this->db->get()->result_array();

        if (empty($customers)) return [];

        // 2. Calculate Recency (Days since last purchase)
        $today = time();
        $recency_days = [];
        $frequencies = [];
        $monetaries = [];

        foreach ($customers as &$c) {
            $last_date = strtotime($c['last_purchase']);
            $days = round(($today - $last_date) / (60 * 60 * 24));
            $c['recency_days'] = $days;
            
            $recency_days[] = $days;
            $frequencies[] = $c['frequency'];
            $monetaries[] = $c['monetary'];
        }
        unset($c);

        // 3. Determine Quintiles (1-5 Scoring)
        // Helper to get score based on percentile
        // Recency: Low days = High Score (5), High days = Low Score (1)
        // Frequency/Monetary: High value = High Score (5)
        
        $r_quartiles = $this->get_quintiles($recency_days, true); // true = lower is better
        $f_quartiles = $this->get_quintiles($frequencies, false);
        $m_quartiles = $this->get_quintiles($monetaries, false);

        $segments = [
            'Champions' => 0,
            'Loyal Customers' => 0,
            'Potential Loyalist' => 0,
            'Recent Customers' => 0,
            'Promising' => 0,
            'At Risk' => 0,
            'Cant Lose Them' => 0,
            'Hibernating' => 0,
            'Lost' => 0
        ];
        
        $customer_details = [];

        foreach ($customers as $c) {
            $r_score = $this->score_value($c['recency_days'], $r_quartiles, true);
            $f_score = $this->score_value($c['frequency'], $f_quartiles);
            $m_score = $this->score_value($c['monetary'], $m_quartiles);
            
            $rfm_score = $r_score . $f_score . $m_score; // String "555"
            $avg_score = ($r_score + $f_score) / 2; // Focusing on R and F for segmentation Logic

            // Segmentation Logic (Simplified RB-Growth)
            $segment = 'Average';
            
            if ($r_score >= 4 && $f_score >= 4) {
                $segment = 'Champions';
            } elseif ($f_score >= 3 && $r_score >= 3) {
                $segment = 'Loyal Customers';
            } elseif ($r_score >= 4 && $f_score <= 2) {
                $segment = 'Recent Customers'; // High Recency, Low Freq
            } elseif ($r_score >= 3 && $f_score <= 3) {
                $segment = 'Potential Loyalist';
            } elseif ($r_score <= 2 && $f_score >= 4) {
                $segment = 'Cant Lose Them'; // Used to buy a lot, but not recently
            } elseif ($r_score <= 2 && $f_score >= 2) {
                $segment = 'At Risk';
            } elseif ($r_score <= 2 && $f_score <= 2) {
                $segment = 'Hibernating'; 
            } elseif ($r_score == 1 && $f_score == 1) {
                $segment = 'Lost'; 
            } else {
                $segment = 'Promising';
            }
            
            $segments[$segment]++;
            
            // Only keep top details to save memory if list is huge
            // For now, let's keep basic counts
        }

        return [
            'segments' => $segments,
            'total_customers' => count($customers)
        ];
    }
    
    // Helper for quintiles
    private function get_quintiles($array, $reverse = false) {
        sort($array);
        $count = count($array);
        if($count == 0) return [0,0,0,0,0];
        
        return [
            $array[round($count * 0.2) - 1] ?? 0,
            $array[round($count * 0.4) - 1] ?? 0,
            $array[round($count * 0.6) - 1] ?? 0,
            $array[round($count * 0.8) - 1] ?? 0,
            end($array)
        ];
    }
    
    private function score_value($val, $quintiles, $reverse = false) {
         if ($reverse) {
             // Low value = High Score for Recency
             if ($val <= $quintiles[0]) return 5;
             if ($val <= $quintiles[1]) return 4;
             if ($val <= $quintiles[2]) return 3;
             if ($val <= $quintiles[3]) return 2;
             return 1;
         } else {
             if ($val <= $quintiles[0]) return 1;
             if ($val <= $quintiles[1]) return 2;
             if ($val <= $quintiles[2]) return 3;
             if ($val <= $quintiles[3]) return 4;
             return 5;
         }
    }
    
    /**
     * Churn Risk Prediction
     * Identifies customers deviating from their personal average inter-purchase time.
     */
    public function get_churn_risk_analysis()
    {
        // Require customers with at least 3 purchases to calculate a valid interval
        $sql = "SELECT clientid, date FROM " . db_prefix() . "invoices WHERE status != 6 ORDER BY clientid, date ASC";
        $rows = $this->db->query($sql)->result_array();
        
        $client_dates = [];
        foreach($rows as $r){
            $client_dates[$r['clientid']][] = strtotime($r['date']);
        }
        
        $high_risk = 0;
        $medium_risk = 0;
        $safe = 0;
        
        foreach($client_dates as $id => $dates){
            $count = count($dates);
            if($count < 3) continue; // Skip new/low data clients
            
            // Calculate Average Inter-Purchase Time (AIPT)
            $intervals = [];
            for($i = 1; $i < $count; $i++){
                $intervals[] = ($dates[$i] - $dates[$i-1]) / (60*60*24);
            }
            $aipt = array_sum($intervals) / count($intervals);
            
            // Time since last purchase
            $last_purchase = end($dates);
            $days_since = (time() - $last_purchase) / (60*60*24);
            
            // Risk Logic
            // If they are > 3x their normal cycle late, High Risk
            // If they are > 1.5x their normal cycle late, Medium Risk
            
            if ($days_since > ($aipt * 3)) {
                $high_risk++;
            } elseif ($days_since > ($aipt * 1.5)) {
                $medium_risk++;
            } else {
                $safe++;
            }
        }
        
        return [
            'high_risk' => $high_risk,
            'medium_risk' => $medium_risk,
            'safe' => $safe
        ];
    }
    
    /**
     * Cohort Analysis (Retention Heatmap)
     * Groups customers by acquisition month and tracks retention over 12 months.
     */
    public function get_cohort_analysis()
    {
        // 1. Get First Purchase Date for every client
        $sql = "SELECT clientid, MIN(date) as first_purchase_date FROM ".db_prefix()."invoices WHERE status != 6 GROUP BY clientid";
        $clients_start = $this->db->query($sql)->result_array();
        
        $cohorts = []; // Key: 'YYYY-MM', Value: [client_ids...]
        $client_cohort_map = [];
        
        foreach($clients_start as $c){
            $cohort_month = date('Y-m', strtotime($c['first_purchase_date']));
            $cohorts[$cohort_month][] = $c['clientid'];
            $client_cohort_map[$c['clientid']] = $cohort_month;
        }
        
        // Limit to last 12 months for display
        krsort($cohorts);
        $recent_cohorts = array_slice($cohorts, 0, 12); 
        
        // 2. Calculate Retention
        $heatmap = [];
        
        foreach($recent_cohorts as $month => $client_ids){
            $cohort_size = count($client_ids);
            if($cohort_size == 0) continue;
            
            $retention_row = [];
            $retention_row['month'] = $month;
            $retention_row['size'] = $cohort_size;
            $retention_row['data'] = []; // Index 0 = Month 0 (100%), Index 1 = Month 1...
            
            // Check retention for next 11 months
            for($i = 0; $i < 12; $i++){
                // Target Month to check activity
                $target_date = date('Y-m', strtotime("$month +$i months"));
                if($target_date > date('Y-m')) break; // Future
                
                if($i == 0) {
                    $retention_row['data'][] = 100; // Month 0 is always 100%
                } else {
                    // Count how many of these specific clients made a purchase in Target Month
                    $this->db->where_in('clientid', $client_ids);
                    $this->db->where('DATE_FORMAT(date, "%Y-%m")', $target_date);
                    $this->db->where('status !=', 6);
                    $active_count = $this->db->count_all_results(db_prefix().'invoices');
                    
                    // Distinct client count would be more accurate if one client buys multiple times, 
                    // count_all_results counts invoices. 
                    // Let's optimize:
                    $sql_ret = "SELECT COUNT(DISTINCT clientid) as retained FROM ".db_prefix()."invoices 
                                WHERE DATE_FORMAT(date, '%Y-%m') = '$target_date' 
                                AND status != 6 
                                AND clientid IN (".implode(',', $client_ids).")";
                    $res = $this->db->query($sql_ret)->row();
                    $active = $res->retained;
                    
                    $pct = round(($active / $cohort_size) * 100);
                    $retention_row['data'][] = $pct;
                }
            }
            $heatmap[] = $retention_row;
        }
        
        return $heatmap;
    }
    
    /**
     * Customer Lifetime Value Distribution
     */
    public function get_clv_distribution()
    {
        // Calculate Total Spent per customer
        $sql = "SELECT clientid, SUM(total) as lifetime_value FROM ".db_prefix()."invoices WHERE status != 6 AND status != 5 GROUP BY clientid";
        $rows = $this->db->query($sql)->result_array();
        
        // Buckets
        $buckets = [
            '< $1,000' => 0,
            '$1k - $5k' => 0,
            '$5k - $10k' => 0,
            '$10k - $50k' => 0,
            '> $50k' => 0
        ];
        
        $total_clv = 0;
        $count = 0;
        
        foreach($rows as $r){
            $val = floatval($r['lifetime_value']);
            $total_clv += $val;
            $count++;
            
            if($val < 1000) $buckets['< $1,000']++;
            elseif($val < 5000) $buckets['$1k - $5k']++;
            elseif($val < 10000) $buckets['$5k - $10k']++;
            elseif($val < 50000) $buckets['$10k - $50k']++;
            else $buckets['> $50k']++;
        }
        
        return [
            'buckets' => $buckets,
            'avg_clv' => $count > 0 ? round($total_clv / $count) : 0,
            'top_clv' => $count > 0 ? max(array_column($rows, 'lifetime_value')) : 0
        ];
    }
}
