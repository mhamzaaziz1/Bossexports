<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test_fifo_v3 extends App_Controller
{
    public function index()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        echo "<pre>";
        echo "<h1>FIFO Verification V3</h1>";
        
        try {
            $this->load->model('reports_model');
            
            // Call Logic
            $result = $this->reports_model->get_avg_purchase_aging('both', '', 'extended');
            
            if (empty($result)) {
                echo "No data returned (Empty Result).\n";
            } else {
                echo "Data returned: " . count($result) . " items.\n";
                // Show top 5 items
                foreach (array_slice($result, 0, 5) as $item) {
                    echo "<h3>Item: " . $item['description'] . "</h3>";
                    echo "Avg Age: " . $item['avg_age'] . "<br>";
                    echo "Risk: " . $item['risk_level'] . "<br>";
                    echo "Total Qty (Stock): " . $item['total_quantity'] . "<br>";
                    echo "Buckets: <br>";
                    print_r($item['aging_buckets']);
                    echo "<hr>";
                }
            }
        
        } catch (Throwable $e) {
            echo "<h1>Error</h1>";
            echo $e->getMessage() . "\n" . $e->getTraceAsString();
        }

        echo "</pre>";
        die();
    }
}
