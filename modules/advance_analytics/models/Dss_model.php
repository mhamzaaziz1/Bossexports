<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dss_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get monthly revenue for the last N months to use as training data
     */
    public function get_monthly_revenue_history($months = null)
    {
        $this->db->select('DATE_FORMAT(date, "%Y-%m") as month, SUM(total) as revenue');
        $this->db->from(db_prefix() . 'invoices');
        $this->db->where('status !=', 5); // Exclude cancelled
        $this->db->where('date >', '1970-01-01'); // Exclude invalid dates
        $this->db->group_by('month');
        $this->db->order_by('month', 'ASC');
        
        if ($months !== null && $months !== 'all') {
            $this->db->limit($months);
        }
        
        $results = $this->db->get()->result_array();
        
        // Fill gaps with 0
        $data = [];
        if(count($results) > 0){
             $start = new DateTime($results[0]['month'] . '-01');
             $end = new DateTime($results[count($results)-1]['month'] . '-01');
             $interval = new DateInterval('P1M');
             $period = new DatePeriod($start, $interval, $end->modify('+1 month'));

             $mapped = [];
             foreach($results as $r){
                 $mapped[$r['month']] = (float)$r['revenue'];
             }

             foreach($period as $dt){
                 $m = $dt->format('Y-m');
                 $data[$m] = isset($mapped[$m]) ? $mapped[$m] : 0.0;
             }
        }
        return $data;
    }

    /**
     * Holt-Winters Triple Exponential Smoothing
     * 
     * @param array $series Time series data
     * @param int $slen Season length (e.g. 12 for monthly data)
     * @param float $alpha Smoothing factor for level
     * @param float $beta Smoothing factor for trend
     * @param float $gamma Smoothing factor for seasonality
     * @param int $n_preds Number of predictions to make
     * 
     * @return array ['forecast' => [], 'confidence' => []]
     */
    public function predict_holt_winters($series, $slen = 12, $alpha = 0.2, $beta = 0.1, $gamma = 0.1, $n_preds = 3)
    {
        // Check if we have enough data for Seasonality (need at least 2 full cycles)
        if (count($series) < $slen * 2) {
             // Fallback to Holt's Linear Trend (Double Exponential) if data is short (< 24 months)
             // This captures Level + Trend but ignores Seasonality, giving a different result than simple regression.
             return $this->predict_holts_linear($series, $alpha, $beta, $n_preds);
        }
        
        $season = [];
        $trend = [];
        
        // Initial Seasonality
        for ($i = 0; $i < $slen; $i++) {
            $sum = 0;
            for ($j = 0; $j < $slen; $j++) {
                $sum += $series[$j];
            }
            $avg = $sum / $slen;
            $season[$i] = $series[$i] / ($avg == 0 ? 1 : $avg); 
        }

        // Initial Trend
        $trend = 0; // Simple init
        
        $smooth = $series[0];
        $result = [];
        
        // Training (Smoothing)
        for ($i = 0; $i < count($series); $i++) {
             $val = $series[$i];
             $last_smooth = $smooth;
             
             $season_idx = $i % $slen;
             
             // Guard against division by zero
             $season_val = $season[$season_idx] == 0 ? 1e-10 : $season[$season_idx];
             
             // Level
             $smooth = $alpha * ($val / $season_val) + (1 - $alpha) * ($smooth + $trend);
             
             // Guard against division by zero for seasonality update
             $smooth_val = $smooth == 0 ? 1e-10 : $smooth;

             // Trend
             $trend = $beta * ($smooth - $last_smooth) + (1 - $beta) * $trend;
             
             // Seasonality
             $season[$season_idx] = $gamma * ($val / $smooth_val) + (1 - $gamma) * $season[$season_idx];
             
             $result[] = ($smooth + $trend) * $season[$season_idx];
        }

        // Forecasting
        $forecast = [];
        $data_count = count($series);
        
        for ($i = 0; $i < $n_preds; $i++) {
             $season_idx = ($data_count + $i) % $slen;
             $pred = ($smooth + $i * $trend) * $season[$season_idx];
             // Simple confidence interval (dummy for now, real calc is complex)
             $forecast[] = $pred;
        }

        return [
            'method' => 'Holt-Winters',
            'historical' => $series,
            'forecast' => $forecast
        ];
    }

    /**
     * Simple Linear Regression
     */
    public function predict_linear_regression($series, $n_preds = 3)
    {
        $n = count($series);
        if($n < 2) return ['forecast' => [], 'method' => 'Insufficient Data'];
        
        $x = range(0, $n - 1);
        $y = array_values($series);
        
        $sum_x = array_sum($x);
        $sum_y = array_sum($y);
        
        $sum_xy = 0;
        $sum_xx = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sum_xy += ($x[$i] * $y[$i]);
            $sum_xx += ($x[$i] * $x[$i]);
        }
        
        $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_xx - $sum_x * $sum_x);
        $intercept = ($sum_y - $slope * $sum_x) / $n;
        
        $forecast = [];
        for($i = 0; $i < $n_preds; $i++){
             $forecast[] = $slope * ($n + $i) + $intercept;
        }

        return [
            'method' => 'Linear Regression',
            'historical' => $series,
            'forecast' => $forecast
        ];
    }
    
    /**
     * Generate "Plain English" Narrative
     */
    public function generate_narrative($forecast_data)
    {
        $predictions = $forecast_data['forecast'];
        if(empty($predictions)) return "Not enough data to generate a forecast.";
        
        $method = $forecast_data['method'];
        $next_month_val = $predictions[0];
        $last_hist = end($forecast_data['historical']);
        
        $trend_direction = $next_month_val > $last_hist ? "increase" : "decrease";
        $percent_change = $last_hist != 0 ? round((($next_month_val - $last_hist) / $last_hist) * 100, 1) : 100;
        
        $narrative = "Based on historical data using the {$method} algorithm, we project a <b>{$percent_change}% {$trend_direction}</b> in revenue for the upcoming month.";
        
        if($method === 'Holt-Winters'){
            $narrative .= " This model accounts for seasonal fluctuations, suggesting that past seasonal patterns are likely to repeat.";
        }
        
        return $narrative;
    }
    /**
     * Moving Average
     */
    public function predict_moving_average($series, $window = 3, $n_preds = 3)
    {
        $n = count($series);
        if ($n < $window) return ['forecast' => [], 'method' => 'Insufficient Data'];
        
        $forecast = [];
        // We will just project the average of the last $window months forward (flat line)
        // Or we can do a rolling average if we were predicting one step at a time with real data,
        // but for pure future forecast without new data, SMA tends to be flat or linear if trend-adjusted.
        // Let's do a simple iterative moving average where we use predictions as inputs.
        
        $current_series = $series;
        
        for ($i = 0; $i < $n_preds; $i++) {
            $subset = array_slice($current_series, -$window);
            $avg = array_sum($subset) / count($subset);
            $forecast[] = $avg;
            $current_series[] = $avg; // Use prediction for next step
        }
        
        return [
            'method' => 'Moving Average',
            'historical' => $series,
            'forecast' => $forecast
        ];
    }

    /**
     * Holt's Linear Trend (Double Exponential Smoothing)
     * Good for data with trend but no/insufficient seasonality.
     */
    public function predict_holts_linear($series, $alpha = 0.2, $beta = 0.1, $n_preds = 3)
    {
        $n = count($series);
        if ($n < 2) return $this->predict_linear_regression($series, $n_preds);

        // Init
        $level = $series[0];
        $trend = $series[1] - $series[0];
        
        // Smoothing
        for ($i = 0; $i < $n; $i++) {
            $val = $series[$i];
            $last_level = $level;
            
            // Level
            $level = $alpha * $val + (1 - $alpha) * ($level + $trend);
            
            // Trend
            $trend = $beta * ($level - $last_level) + (1 - $beta) * $trend;
        }
        
        // Forecast
        $forecast = [];
        for ($k = 1; $k <= $n_preds; $k++) {
            $forecast[] = $level + $k * $trend;
        }
        
        return [
            'method' => 'Holt\'s Linear (Double Exp)',
            'historical' => $series,
            'forecast' => $forecast
        ];
    }
}
