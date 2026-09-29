<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Item_analytics_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ABC Analysis (Pareto Principle)
     * Class A: Top 80% Revenue
     * Class B: Next 15% Revenue
     * Class C: Bottom 5% Revenue
     */
    public function get_abc_analysis()
    {
        // 1. Get raw revenue per item (from Invoices only)
        // tblitems vs tblitemable. In Perfex, actual line items are in tblitemable.
        // We link description/long_description to tblitems or use item_id if available (Perfex sometimes treats custom items as ID 0)
        
        // We will try to group by description or item_id if > 0
        $sql = "SELECT description, SUM(qty * rate) as total_revenue, count(id) as freq 
                FROM ".db_prefix()."itemable 
                WHERE rel_type = 'invoice' 
                GROUP BY description 
                HAVING total_revenue > 0
                ORDER BY total_revenue DESC";
        
        $items = $this->db->query($sql)->result_array();
        
        $total_revenue = array_sum(array_column($items, 'total_revenue'));
        $running_sum = 0;
        
        $classes = ['A' => 0, 'B' => 0, 'C' => 0];
        $details = ['A' => [], 'B' => [], 'C' => []]; // Store top 5 of each for preview
        
        foreach($items as $i){
            $running_sum += $i['total_revenue'];
            $pct = ($running_sum / $total_revenue) * 100;
            
            $class = 'C';
            if($pct <= 80) $class = 'A';
            elseif($pct <= 95) $class = 'B';
            
            $classes[$class]++;
            
            // Keep top 5 descriptions
            if(count($details[$class]) < 5){
                $details[$class][] = $i['description'];
            }
        }
        
        return [
            'classes' => $classes,
            'details' => $details,
            'total_items' => count($items)
        ];
    }
    
    /**
     * Item Velocity
     * Top items by frequency of appearance on unique invoices
     */
    public function get_item_velocity()
    {
        $sql = "SELECT description, COUNT(DISTINCT rel_id) as invoice_count 
                FROM ".db_prefix()."itemable 
                WHERE rel_type = 'invoice' 
                GROUP BY description 
                ORDER BY invoice_count DESC 
                LIMIT 10";
        
        return $this->db->query($sql)->result_array();
    }
    
    /**
     * Product Affinity (Market Basket)
     * "Frequently Bought Together"
     */
    public function get_product_affinity()
    {
        // Find pairs of items appearing on the same invoice
        // Filter out self-joins (A-A) and ensure order (A-B is same as B-A) to duplicate count
        // Using MySQL Self Join
        
        // This can be heavy on large DBs. Limit to top 20 recent Invoices?? No, let's try full but optimized.
        // We filter where Item A < Item B by description string to ensure unique unordered pairs.
        
        $sql = "
            SELECT t1.description as item_a, t2.description as item_b, COUNT(*) as frequency
            FROM ".db_prefix()."itemable t1
            JOIN ".db_prefix()."itemable t2 ON t1.rel_id = t2.rel_id AND t1.rel_type = 'invoice' AND t2.rel_type = 'invoice'
            WHERE t1.description < t2.description
            GROUP BY t1.description, t2.description
            ORDER BY frequency DESC
            LIMIT 10
        ";
        
        return $this->db->query($sql)->result_array();
    }
    
    /**
     * Seasonality Heatmap
     * Top 10 items sales by month
     */
    public function get_seasonality_heatmap()
    {
        // 1. Identify Top 10 Items by total revenue first to filter the heatmap
        $top_sql = "SELECT description FROM ".db_prefix()."itemable 
                    WHERE rel_type='invoice' 
                    GROUP BY description 
                    ORDER BY SUM(qty * rate) DESC LIMIT 10";
        
        $top_items = array_column($this->db->query($top_sql)->result_array(), 'description');
        
        if(empty($top_items)) return [];
        
        // 2. Fetch monthly data for these items
        // Need to join invoices to get the Date
        $escaped_items = [];
        foreach($top_items as $t) $escaped_items[] = "'".$this->db->escape_str($t)."'";
        $in_clause = implode(',', $escaped_items);
        
        $sql = "
            SELECT i.description, MONTH(inv.date) as month, COUNT(i.id) as sales_count
            FROM ".db_prefix()."itemable i
            JOIN ".db_prefix()."invoices inv ON i.rel_id = inv.id
            WHERE i.rel_type = 'invoice'
            AND i.description IN ($in_clause)
            AND inv.status != 6
            GROUP BY i.description, MONTH(inv.date)
        ";
        
        $raw = $this->db->query($sql)->result_array();
        
        // Transform for chart/table
        // Structure: ['Item Name'] => [1=>0, 2=>5, ... 12=>0]
        $heatmap = [];
        foreach($top_items as $name) {
            $heatmap[$name] = array_fill(1, 12, 0); // Init 1-12 with 0
        }
        
        foreach($raw as $r){
            $heatmap[$r['description']][$r['month']] = $r['sales_count'];
        }
        
        return $heatmap;
    }
}
