<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Advance_analytics extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('advance_analytics_model');
        $this->load->model('dss_model');
        $this->load->model('customer_analytics_model');
        $this->load->model('item_analytics_model');
        
        if (!has_permission('reports', '', 'view')) {
            access_denied('Advance Analytics');
        }
    }

    public function index()
    {
        $data['title'] = _l('advance_analytics_dashboard');
        $this->load->view('dashboard', $data);
    }

    public function sales()
    {
        $data['title'] = _l('analytics_sales');
        
        $period = $this->input->get('stats_period');
        if(!$period) $period = 'all_time';
        $data['selected_period'] = $period;

        $data['proposal_analytics'] = $this->advance_analytics_model->get_proposal_analytics($period);
        $data['estimate_analytics'] = $this->advance_analytics_model->get_estimate_analytics($period);
        $data['invoice_analytics'] = $this->advance_analytics_model->get_invoice_analytics($period);
        
        $data['invoice_stats'] = json_encode($this->advance_analytics_model->get_invoice_status_stats());
        $data['proposal_stats'] = json_encode($this->advance_analytics_model->get_proposal_status_stats());
        $data['estimate_stats'] = json_encode($this->advance_analytics_model->get_estimate_status_stats());
        
        // Advanced Analytics
        $year = date('Y');
        $data['revenue_trend'] = json_encode($this->advance_analytics_model->get_revenue_trend($year));
        $data['top_items'] = json_encode($this->advance_analytics_model->get_top_performing_items(10));
        $data['top_staff'] = json_encode($this->advance_analytics_model->get_top_sales_staff($year, 10));
        $data['proposal_funnel'] = json_encode($this->advance_analytics_model->get_proposal_conversion_funnel());
        $data['estimate_funnel'] = json_encode($this->advance_analytics_model->get_estimate_conversion_funnel());
        $data['invoice_funnel'] = json_encode($this->advance_analytics_model->get_invoice_payment_funnel());
        
        $data['invoice_value_stats'] = json_encode($this->advance_analytics_model->get_invoice_value_stats());
        $data['proposal_value_stats'] = json_encode($this->advance_analytics_model->get_proposal_value_stats());
        $data['estimate_value_stats'] = json_encode($this->advance_analytics_model->get_estimate_value_stats());

        $data['proposal_trend'] = json_encode($this->advance_analytics_model->get_proposal_trend($year));
        $data['estimate_trend'] = json_encode($this->advance_analytics_model->get_estimate_trend($year));
        
        $data['activity_volume_trend'] = json_encode($this->advance_analytics_model->get_activity_volume_trend($year));
        $data['avg_deal_size_trend'] = json_encode($this->advance_analytics_model->get_average_deal_size_trend($year));
        
        // Level 12 Analytics
        $data['win_rate_trend'] = json_encode($this->advance_analytics_model->get_win_rate_trend($year));
        $data['sales_cycle_trend'] = json_encode($this->advance_analytics_model->get_avg_sales_cycle_length($year));
        
        // Broadened Analytics
        $data['collection_trend'] = json_encode($this->advance_analytics_model->get_payment_collection_trend($year));
        $data['profit_trend'] = json_encode($this->advance_analytics_model->get_cost_vs_revenue_trend($year));
        
        // Strategic Analytics
        $data['concentration_struct'] = json_encode($this->advance_analytics_model->get_revenue_concentration($year));
        $data['loyalty_trend'] = json_encode($this->advance_analytics_model->get_customer_loyalty_metrics($year));

        $data['sales_targets'] = $this->advance_analytics_model->get_sales_targets();

        $this->load->view('sales', $data);
    }

    public function finance()
    {
        $data['title'] = _l('analytics_finance');
        $data['income_vs_expenses'] = json_encode($this->advance_analytics_model->get_finance_overview());
        
        $year = date('Y');
        $data['payment_mode_stats'] = json_encode($this->advance_analytics_model->get_payment_mode_stats($year));
        $data['payment_receipts_trend'] = json_encode($this->advance_analytics_model->get_payment_receipts_trend($year));
        $data['top_customers'] = json_encode($this->advance_analytics_model->get_top_paying_customers(10));
        
        // New Financial Data
        $data['financial_health'] = $this->advance_analytics_model->get_financial_health_stats($year);
        $data['expenses_by_category'] = json_encode($this->advance_analytics_model->get_expenses_by_category($year));

        $this->load->view('finance', $data);
    }

    public function customers()
    {
        $data['title'] = _l('analytics_customers');
        
        $year = date('Y');
        $data['lead_source_stats'] = json_encode($this->advance_analytics_model->get_lead_source_stats());
        $data['customer_group_stats'] = json_encode($this->advance_analytics_model->get_customer_group_stats());
        
        // New Customer Analytics
        $data['new_vs_returning'] = json_encode($this->advance_analytics_model->get_new_vs_returning_customers($year));
        $data['geography'] = json_encode($this->advance_analytics_model->get_customer_geography());
        $data['retention_kpis'] = $this->advance_analytics_model->get_customer_retention_kpis();

        $this->load->view('customers', $data);
    }

    public function projects()
    {
        $data['title'] = _l('analytics_projects');
        $data['project_status_stats'] = json_encode($this->advance_analytics_model->get_project_status_stats());
        $data['logged_hours_stats'] = json_encode($this->advance_analytics_model->get_msg_logged_hours_by_staff());
        $this->load->view('projects', $data);
    }

    public function client($client_id)
    {
        // Check permission
        if (!has_permission('advance_analytics', '', 'view')) {
            access_denied('Advance Analytics');
        }

        // Get data from model
        $data = $this->advance_analytics_model->get_client_analytics_data($client_id);
        
        if (!$data) {
            set_alert('warning', _l('client_not_found'));
            redirect(admin_url('clients'));
        }

        $this->load->view('advance_analytics/client_advanced_analytics', $data);
    }

    public function dss()
    {
        $data['title'] = 'Decision Support System';
        
        $this->load->model('dss_model');
        
        // 1. Get Data
        $revenue_history = $this->dss_model->get_monthly_revenue_history('all'); // Fetch all history
        $series = array_values($revenue_history); // simple array for math
        
        // 2. Run Algos
        // A. Holt-Winters (Seasonal)
        $pred_hw = $this->dss_model->predict_holt_winters($series, 12, 0.2, 0.1, 0.1, 6);
        
        // B. Linear Regression (Trend)
        $pred_lr = $this->dss_model->predict_linear_regression($series, 6);
        
        // C. Moving Average (Baseline)
        $pred_ma = $this->dss_model->predict_moving_average($series, 3, 6);
        
        // 3. Narratives
        $narratives = [
            'holt_winters' => $this->dss_model->generate_narrative($pred_hw),
            'linear' => $this->dss_model->generate_narrative($pred_lr),
            'moving_avg' => $this->dss_model->generate_narrative($pred_ma)
        ];
        
        $data['dss_data'] = json_encode([
            'labels' => array_keys($revenue_history),
            'history' => $series,
            'forecasts' => [
                'holt_winters' => $pred_hw['forecast'],
                'linear' => $pred_lr['forecast'],
                'moving_avg' => $pred_ma['forecast']
            ],
            'narratives' => $narratives
        ]);
        
        $this->load->view('dss', $data);
    }

    public function customers_deep_dive()
    {
        $data['title'] = 'Deep Customer Analytics';
        
        $year = date('Y');
        
        // --- Legacy Standard Analytics ---
        $data['retention_kpis'] = $this->advance_analytics_model->get_customer_retention_kpis();
        $data['new_vs_returning'] = json_encode($this->advance_analytics_model->get_new_vs_returning_customers($year));
        $data['geography'] = json_encode($this->advance_analytics_model->get_customer_geography());
        $data['lead_source_stats'] = json_encode($this->advance_analytics_model->get_lead_source_stats());
        $data['customer_group_stats'] = json_encode($this->advance_analytics_model->get_customer_group_stats());
        
        // --- Deep Analytics ---
        // 1. RFM
        $rfm = $this->customer_analytics_model->get_rfm_segmentation();
        $data['rfm_data'] = json_encode($rfm['segments']);
        
        // 2. Churn
        $churn = $this->customer_analytics_model->get_churn_risk_analysis();
        $data['churn_data'] = json_encode($churn);
        
        // 3. Cohort
        $data['cohort_data'] = $this->customer_analytics_model->get_cohort_analysis(); // Pass array for HTML table
        
        // 4. CLV
        $clv = $this->customer_analytics_model->get_clv_distribution();
        $data['clv_buckets'] = json_encode($clv['buckets']);
        $data['clv_stats'] = $clv;
        
        $this->load->view('customers_deep_dive', $data);
    }

    public function items_deep_dive()
    {
        $data['title'] = 'Deep Items Analytics';
        
        // 1. ABC
        $data['abc'] = $this->item_analytics_model->get_abc_analysis();
        
        // 2. Velocity
        $data['velocity'] = $this->item_analytics_model->get_item_velocity();
        
        // 3. Affinity
        $data['affinity'] = $this->item_analytics_model->get_product_affinity();
        
        // 4. Seasonality
        $data['seasonality'] = $this->item_analytics_model->get_seasonality_heatmap();
        
        $this->load->view('items_deep_dive', $data);
    }
    public function full_report()
    {
        $data['title'] = 'Full Report Analytics (Modular)';
        
        // Handle Date Filter
        $year = date('Y');
        if($this->input->post('report_year')){
             $year = $this->input->post('report_year');
        }
        $data['selected_year'] = $year;
        
        $this->load->library('analytics_widgets');
        $all_widgets = $this->analytics_widgets->get_available_widgets();
        
        // Get Settings
        $settings_json = get_option('advance_analytics_report_config');
        $saved_settings = $settings_json ? json_decode($settings_json, true) : [];
        
        $final_widgets = [];
        
        if(!empty($saved_settings)){
            // Filter and Sort
            foreach($all_widgets as $id => $widget){
                if(isset($saved_settings[$id])){
                    if(isset($saved_settings[$id]['enabled']) && $saved_settings[$id]['enabled'] == 1){
                         $widget['order'] = isset($saved_settings[$id]['order']) ? $saved_settings[$id]['order'] : 99;
                         $final_widgets[] = $widget;
                    }
                } else {
                    // New widget not in settings yet? Default to enabled or disabled? 
                    // Let's default to enabled but at the end
                    $widget['order'] = 999;
                    $final_widgets[] = $widget;
                }
            }
            
            // Sort
            usort($final_widgets, function($a, $b){
                return $a['order'] - $b['order'];
            });
            
        } else {
            // Default: All enabled
            $final_widgets = $all_widgets;
        }
        
        
        // Prepare widget data
        foreach($final_widgets as $key => $widget){
             $final_widgets[$key]['data'] = isset($widget['data']) ? $widget['data'] : null; // Default to null
             
             // Fetch data based on definition
             if(isset($widget['model_method'])){
                 // Internal model method (Advance_analytics_model)
                 if(isset($widget['requires_year']) && $widget['requires_year']){
                     $final_widgets[$key]['data'] = $this->advance_analytics_model->{$widget['model_method']}($year);
                 } else {
                     $final_widgets[$key]['data'] = $this->advance_analytics_model->{$widget['model_method']}();
                 }
             } elseif(isset($widget['model_method_external'])){
                 // External model method
                 $model = $widget['model_method_external'][0];
                 $method = $widget['model_method_external'][1];
                 $this->load->model($model);
                 
                 // Some external methods might need params, implementing simple handling for now
                 if($method == 'get_new_vs_returning_customers') {
                      $final_widgets[$key]['data'] = $this->{$model}->{$method}($year);
                 } elseif($method == 'leads_monthly_report') {
                      $final_widgets[$key]['data'] = $this->{$model}->{$method}(date('m')); // Just current month for now, or improve
                 } else {
                      $final_widgets[$key]['data'] = $this->{$model}->{$method}($year);
                 }
             }
        }
        
        $data['widgets'] = $final_widgets;
        $this->load->view('dynamic_report', $data);
    }
    public function settings()
    {
        if (!has_permission('reports', '', 'view')) {
            access_denied('reports');
        }
        
        $data['title'] = 'Report Settings';
        
        $this->load->library('analytics_widgets');
        $data['available_widgets'] = $this->analytics_widgets->get_available_widgets();
        
        // Get saved settings
        $settings_json = get_option('advance_analytics_report_config');
        $data['saved_settings'] = $settings_json ? json_decode($settings_json, true) : [];
        
        $this->load->view('report_settings', $data);
    }

    public function save_settings()
    {
        if (!has_permission('reports', '', 'edit')) {
            access_denied('reports');
        }
        
        if ($this->input->post()) {
            $config = $this->input->post('widgets');
            // Save as JSON
            update_option('advance_analytics_report_config', json_encode($config));
            set_alert('success', _l('updated_successfully', _l('settings')));
        }
        redirect(admin_url('advance_analytics/settings'));
    }
}
