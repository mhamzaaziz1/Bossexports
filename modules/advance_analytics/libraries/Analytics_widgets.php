<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Analytics_widgets
{
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('advance_analytics_model');
    }

    public function get_available_widgets()
    {
        return [
            // --- SECTION: DECISION SUPPORT SYSTEM (DSS) ---
            'dss_header' => [
                'id' => 'dss_header',
                'title' => 'Decision Support System', 
                'view' => 'widgets/section_header', 
                'category' => 'dss',
                'col' => 12,
                'order' => 1
            ],
            'pipeline_funnel' => [
                'id' => 'pipeline_funnel',
                'title' => 'Proposal Conversion Funnel', 
                'view' => 'widgets/pipeline_funnel_chart', 
                'category' => 'sales',
                'model_method' => 'get_proposal_conversion_funnel', 
                'col' => 6,
                'chart_id' => 'pipeline_funnel',
                'order' => 2
            ],
            'churn_chart' => [
                'id' => 'churn_chart',
                'title' => 'Churn Risk Analysis', 
                'view' => 'widgets/churn_chart', 
                'category' => 'customers',
                'model_method_external' => ['customer_analytics_model', 'get_churn_risk_analysis'], 
                'col' => 6,
                'chart_id' => 'churn_risk_chart',
                'order' => 3
            ],
            'sales_leaderboard' => [
                'id' => 'sales_leaderboard',
                'title' => 'Top Sales Agents',
                'view' => 'widgets/sales_leaderboard_chart',
                'category' => 'sales',
                'model_method' => 'get_top_sales_staff',
                'col' => 6,
                'chart_id' => 'sales_leaderboard',
                'requires_year' => true,
                'order' => 4
            ],
            'items_leaderboard' => [
                'id' => 'items_leaderboard',
                'title' => 'Top Selling Items',
                'view' => 'widgets/items_leaderboard_chart',
                'category' => 'items',
                'model_method' => 'get_top_performing_items',
                'col' => 6,
                'chart_id' => 'items_leaderboard',
                'order' => 5
            ],

            // --- SECTION: DEEP SALES ANALYTICS ---
             'sales_header' => [
                'id' => 'sales_header',
                'title' => 'Deep Sales Analytics', 
                'view' => 'widgets/section_header', 
                'category' => 'sales',
                'col' => 12,
                'order' => 10
            ],
            'sales_velocity' => [
                'id' => 'sales_velocity',
                'title' => 'Sales Velocity', 
                'view' => 'widgets/sales_velocity_card', 
                'category' => 'sales',
                'model_method' => 'get_sales_velocity', 
                'col' => 3,
                'drill_down_url' => 'proposals?status=3', 
                'requires_year' => true,
                'order' => 11
            ],
            'win_rate' => [
                'id' => 'win_rate',
                'title' => 'Win Rate', 
                'view' => 'widgets/win_rate_card', 
                'category' => 'sales',
                'model_method' => 'get_win_rate_analysis', 
                'col' => 3,
                'is_chart' => false,
                'drill_down_url' => 'proposals',
                'order' => 12
            ],
            'arpa' => [
                'id' => 'arpa',
                'title' => 'Average Revenue Per Account', 
                'view' => 'widgets/arpa_card', 
                'category' => 'sales',
                'model_method' => 'get_arpa', 
                'col' => 3,
                'drill_down_url' => 'clients',
                'order' => 13
            ],
            'sales_cycle' => [
                'id' => 'sales_cycle',
                'title' => 'Sales Cycle Length', 
                'view' => 'widgets/sales_cycle_card', 
                'category' => 'sales',
                'model_method' => 'get_sales_cycle_analysis', 
                'col' => 3,
                'order' => 14
            ],
             'win_rate_trend' => [
                'id' => 'win_rate_trend',
                'title' => 'Win Rate Trend (Monthly)', 
                'view' => 'widgets/win_rate_trend_chart', 
                'category' => 'sales',
                'model_method' => 'get_win_rate_trend', 
                'col' => 6,
                'chart_id' => 'win_rate_trend',
                'requires_year' => true,
                'order' => 15
            ],
             'leads_chart' => [
                'id' => 'leads_chart',
                'title' => 'Leads Monthly Conversions', 
                'view' => 'widgets/core_leads_chart', 
                'category' => 'core',
                'model_method_external' => ['reports_model', 'leads_monthly_report'], 
                'col' => 6,
                'chart_id' => 'leads_monthly',
                'requires_month' => true,
                'order' => 16
            ],
             'revenue_concentration' => [
                'id' => 'revenue_concentration',
                'title' => 'Revenue Concentration', 
                'view' => 'widgets/concentration_chart', 
                'category' => 'sales',
                'model_method' => 'get_revenue_concentration', 
                'col' => 6,
                'chart_id' => 'concentration_chart',
                'requires_year' => true,
                'order' => 17
            ],
            
            // --- SECTION: FINANCE ANALYTICS ---
             'finance_header' => [
                'id' => 'finance_header',
                'title' => 'Finance Analytics', 
                'view' => 'widgets/section_header', 
                'category' => 'finance',
                'col' => 12,
                'order' => 30
            ],
            'profit_trend' => [
                'id' => 'profit_trend',
                'title' => 'Cost vs Revenue vs Profit', 
                'view' => 'widgets/profit_trend_chart', 
                'category' => 'sales',
                'model_method' => 'get_cost_vs_revenue_trend', 
                'col' => 6,
                'chart_id' => 'profit_trend',
                'requires_year' => true,
                'order' => 31
            ],
            'income_chart' => [
                'id' => 'income_chart',
                'title' => 'Total Income', 
                'view' => 'widgets/core_income_chart', 
                'category' => 'core',
                'model_method_external' => ['reports_model', 'total_income_report'], 
                'col' => 6,
                'chart_id' => 'total_income',
                'requires_year' => true,
                'order' => 32
            ],
             'expense_income_chart' => [
                'id' => 'expense_income_chart',
                'title' => 'Expenses vs Income', 
                'view' => 'widgets/core_expense_income_chart', 
                'category' => 'core',
                'model_method_external' => ['reports_model', 'get_expenses_vs_income_report'], 
                'col' => 12,
                'chart_id' => 'expense_income',
                 'requires_year' => true,
                 'order' => 33
            ],
             'payment_collection' => [
                'id' => 'payment_collection',
                'title' => 'Payment Collection Trend', 
                'view' => 'widgets/payment_collection_chart', 
                'category' => 'finance',
                'model_method' => 'get_payment_collection_trend', 
                'col' => 6,
                'chart_id' => 'payment_collection',
                'requires_year' => true,
                'order' => 34
            ],

             // --- SECTION: CUSTOMER ANALYTICS ---
              'customer_header' => [
                'id' => 'customer_header',
                'title' => 'Deep Customer Analytics', 
                'view' => 'widgets/section_header', 
                'category' => 'customers',
                'col' => 12,
                'order' => 50
            ],
             'customer_kpis' => [
                'id' => 'customer_kpis',
                'title' => 'Customer KPIs', 
                'view' => 'widgets/customer_kpis_cards', 
                'category' => 'customers',
                'model_method' => 'get_customer_overview_data', 
                'col' => 12,
                'order' => 51
            ],
            'rfm_segmentation' => [
                'id' => 'rfm_segmentation',
                'title' => 'RFM Segmentation', 
                'view' => 'widgets/rfm_chart', 
                'category' => 'customers',
                'model_method_external' => ['customer_analytics_model', 'get_rfm_segmentation'], 
                'col' => 6,
                'chart_id' => 'rfm_chart',
                'order' => 52
            ],
             'cohort_analysis' => [
                'id' => 'cohort_analysis',
                'title' => 'Cohort Analysis', 
                'view' => 'widgets/cohort_table', 
                'category' => 'customers',
                'model_method_external' => ['customer_analytics_model', 'get_cohort_analysis'], 
                'col' => 6,
                'order' => 53
            ],
             'loyalty_metrics' => [
                'id' => 'loyalty_metrics',
                'title' => 'Customer Loyalty Metrics', 
                'view' => 'widgets/loyalty_metrics_chart', 
                'category' => 'customers',
                'model_method' => 'get_customer_loyalty_metrics', 
                'col' => 6,
                'chart_id' => 'loyalty_metrics',
                'requires_year' => true,
                'order' => 54
            ],

            // --- SECTION: PROJECT ANALYTICS ---
             'project_header' => [
                'id' => 'project_header',
                'title' => 'Project Analytics', 
                'view' => 'widgets/section_header', 
                'category' => 'projects',
                'col' => 12,
                'order' => 70
            ],
            'project_status' => [
                'id' => 'project_status',
                'title' => 'Project Status Breakdown',
                'view' => 'widgets/project_status_chart',
                'category' => 'projects',
                'model_method' => 'get_project_status_stats',
                'col' => 6,
                'chart_id' => 'project_status',
                'order' => 71
            ],
            'staff_workload' => [
                'id' => 'staff_workload',
                'title' => 'Top Staff by Logged Hours (All Time)',
                'view' => 'widgets/staff_workload_chart',
                'category' => 'projects',
                'model_method' => 'get_msg_logged_hours_by_staff',
                'col' => 6,
                'chart_id' => 'staff_workload',
                'order' => 72
            ],
            
             // --- SECTION: ITEM ANALYTICS ---
             'item_header' => [
                'id' => 'item_header',
                'title' => 'Deep Items Analytics', 
                'view' => 'widgets/section_header', 
                'category' => 'items',
                'col' => 12,
                'order' => 80
            ],
            'abc_analysis' => [
                'id' => 'abc_analysis',
                'title' => 'ABC Analysis (Inventory/Sales)', 
                'view' => 'widgets/abc_chart', 
                'category' => 'items',
                'model_method_external' => ['item_analytics_model', 'get_abc_analysis'], 
                'col' => 6,
                'chart_id' => 'abc_chart',
                'order' => 81
            ],
            'seasonality' => [
                'id' => 'seasonality',
                'title' => 'Seasonality Trends', 
                'view' => 'widgets/seasonality_chart', 
                'category' => 'items',
                'model_method_external' => ['item_analytics_model', 'get_seasonality_heatmap'], 
                'col' => 6,
                'chart_id' => 'seasonality_chart',
                'order' => 82
            ]

        ];
    }
}
