<?php
defined('BASEPATH') or exit('No direct script access allowed');

/* =====================================================
 *  BDM Operations — English language file (UAE Edition)
 * ===================================================== */

$lang['bdm_operations']        = 'BDM Operations';
$lang['brands']                = 'Brands';
$lang['distributors']          = 'Vendors / Distributors';
$lang['vr_registrations']      = 'Vendor Registrations';
$lang['master_companies']      = 'Master Companies';
$lang['bdm_pipeline']          = 'Pipeline (Kanban)';
$lang['bdm_partnerships']      = 'Partnerships';
$lang['bdm_commissions']       = 'Partner Commissions';
$lang['bdm_tenders']           = 'Tenders';
$lang['bdm_campaigns']         = 'Marketing Campaigns';
$lang['bdm_kpi_targets']       = 'KPI Targets';
$lang['bdm_dashboard']         = 'BDM Dashboard';

// Permissions
$lang['permission_view_own']   = 'View (Own)';
$lang['permission_view']       = 'View';
$lang['permission_create']     = 'Create';
$lang['permission_edit']       = 'Edit';
$lang['permission_delete']     = 'Delete';
$lang['permission_global']     = 'Global';

// Brand
$lang['brand']                 = 'Brand';
$lang['new_brand']             = 'New Brand';
$lang['edit_brand']            = 'Edit Brand';
$lang['brand_name']            = 'Brand Name';
$lang['brand_description']     = 'Description';
$lang['brand_category']        = 'Category';
$lang['brand_website']         = 'Website';

// Distributor / Vendor
$lang['distributor']           = 'Vendor';
$lang['new_distributor']       = 'New Vendor';
$lang['edit_distributor']      = 'Edit Vendor';
$lang['distributor_name']      = 'Vendor Name';
$lang['classification']        = 'Classification';
$lang['contact_name']          = 'Contact Name';
$lang['assigned_bdm']          = 'Assigned BDM';
$lang['vendor_role']           = 'Vendor Role';
$lang['vendor_role_supplier']  = 'Supplier';
$lang['vendor_role_service']   = 'Service Provider';
$lang['vendor_role_subcon']    = 'Subcontractor';
$lang['products_services']     = 'Products / Services';
$lang['price_range']           = 'Price Range';
$lang['emirate']               = 'Emirate';
$lang['trade_license_no']      = 'Trade License No.';
$lang['vat_trn']               = 'VAT TRN';
$lang['cr_number']             = 'Commercial Registration (CR)';
$lang['iso_certified']         = 'ISO Certified';
$lang['follow_up_date']        = 'Follow-up Date';
$lang['whatsapp']              = 'WhatsApp';

// Lead referral
$lang['refer_to_bdm']          = 'Refer to BDM';
$lang['bdm_referral']          = 'BDM Referral';
$lang['company_name']          = 'Company Name';
$lang['bdm_notes']             = 'BDM Notes';
$lang['duplicate_warning']     = 'Duplicate Warning';
$lang['company_already_exists']= 'Company already exists in BDM pipeline';
$lang['proceed_anyway']        = 'Proceed Anyway?';

// Pipeline (legacy 9-stage)
$lang['stage_new_referral']        = 'New Referral';
$lang['stage_contact_initiated']   = 'Contact Initiated';
$lang['stage_info_requested']      = 'Information Requested';
$lang['stage_under_review']        = 'Under Review';
$lang['stage_qualified']           = 'Qualified';
$lang['stage_verification']        = 'Verification';
$lang['stage_final_approval']      = 'Final Approval';
$lang['stage_registered']          = 'Registered';
$lang['stage_rejected']            = 'Rejected';

// Pipeline (10-step)
$lang['bdm_step_1_lead_id']        = 'Step 1 — Lead Identified';
$lang['bdm_step_2_classify']       = 'Step 2 — Classified';
$lang['bdm_step_3_register']       = 'Step 3 — Registered/Onboarded';
$lang['bdm_step_4_relationship']   = 'Step 4 — Relationship Building';
$lang['bdm_step_5_requirement']    = 'Step 5 — Requirement Captured';
$lang['bdm_step_6_quote']          = 'Step 6 — Quote Sent';
$lang['bdm_step_7_deal']           = 'Step 7 — Deal Closed';
$lang['bdm_step_8_execution']      = 'Step 8 — Execution';
$lang['bdm_step_9_payment']        = 'Step 9 — Payment & Commission';
$lang['bdm_step_10_long_term']     = 'Step 10 — Long-Term Growth';

// Reports
$lang['bdm_reports']                  = 'BDM Reports';
$lang['bdm_report_engine']            = 'BDM Operations Reporting Engine';
$lang['bdm_report_brands_desc']       = 'All brands with country mapping.';
$lang['bdm_report_distributors_desc'] = 'Complete vendor directory with UAE compliance fields.';
$lang['bdm_report_pipeline_desc']     = 'Full status report of all registrations.';
$lang['bdm_report_overdue_desc']      = 'List of missed follow-up dates.';
$lang['bdm_report_registered_desc']   = 'Vendors that have completed registration.';
$lang['bdm_report_rejected_desc']     = 'History of unsuccessful registrations.';
$lang['bdm_report_national_desc']     = 'Filter by National classification.';
$lang['bdm_report_intl_desc']         = 'Filter by International classification.';
$lang['bdm_report_activity_desc']     = 'Audit trail of every status change.';
$lang['bdm_report_master_desc']       = 'List of all system-wide companies.';
$lang['bdm_report_partners_desc']     = 'Inbound + outbound partner directory with revenue.';
$lang['bdm_report_commissions_desc']  = 'Partner commission ledger (Accrued + Paid).';
$lang['bdm_report_tenders_desc']      = 'Tender pipeline with deadlines and outcomes.';
$lang['bdm_report_campaigns_desc']    = 'Multi-channel marketing campaign performance.';
$lang['download_csv']                 = 'Export CSV';

// Activity log
$lang['registration_activity']        = 'Registration Activity';
$lang['view_history']                 = 'History';
$lang['registration_date']            = 'Registration Date';
$lang['last_updated']                 = 'Last Updated';
$lang['activity_log']                 = 'Activity Log';
$lang['action']                       = 'Action';
$lang['date']                         = 'Date';
$lang['last_activity_days_threshold'] = 'No activity for 3 days';

// Activities
$lang['bdm_activities']               = 'BDM Activities';
$lang['bdm_new_activity']             = 'Log Activity';
$lang['bdm_activity_type']            = 'Activity Type';
$lang['bdm_visit']                    = 'Field Visit';
$lang['bdm_call']                     = 'Cold Call';
$lang['bdm_email']                    = 'Email';
$lang['bdm_whatsapp']                 = 'WhatsApp';
$lang['bdm_linkedin']                 = 'LinkedIn';
$lang['bdm_meeting']                  = 'Meeting';
$lang['bdm_customer_name']            = 'Customer / Prospect';
$lang['bdm_outcome']                  = 'Outcome';
$lang['bdm_next_action']              = 'Next Action';

// Outcomes
$lang['outcome_meeting_booked']       = 'Meeting Booked';
$lang['outcome_no_answer']            = 'No Answer';
$lang['outcome_not_interested']       = 'Not Interested';
$lang['outcome_qualified']            = 'Qualified';
$lang['outcome_proposal_requested']   = 'Proposal Requested';
$lang['outcome_follow_up']            = 'Follow Up Required';

// Partnerships
$lang['bdm_new_partner']              = 'New Partner';
$lang['edit_partner']                 = 'Edit Partner';
$lang['bdm_partner_type']             = 'Partner Type';
$lang['bdm_inbound_partner']          = 'Inbound (Channel Sales)';
$lang['bdm_outbound_partner']         = 'Outbound (Reseller / Distributor)';
$lang['bdm_commission']               = 'Commission %';
$lang['bdm_margin']                   = 'Margin %';
$lang['bdm_agreement_date']           = 'Agreement Date';
$lang['bdm_agreement_expiry']         = 'Agreement Expiry';
$lang['bdm_territory']                = 'Territory';

// Registration types
$lang['bdm_registration_type']        = 'Registration Type';
$lang['bdm_supplier_onboarding']      = 'Supplier Onboarding';
$lang['bdm_client_approval']          = 'Client Vendor Approval';

// KPIs
$lang['bdm_kpi_leads']                = 'Leads Generated';
$lang['bdm_kpi_visits']               = 'Field Visits';
$lang['bdm_kpi_calls']                = 'Cold Calls';
$lang['bdm_kpi_proposals']            = 'Proposals Sent';
$lang['bdm_kpi_deals']                = 'Deals Closed';
$lang['bdm_kpi_revenue']              = 'Revenue (AED)';
$lang['bdm_kpi_partners']             = 'Partner Registrations';
$lang['bdm_kpi_vendor_regs']          = 'Vendor Registrations';
$lang['bdm_kpi_client_approvals']     = 'Client Approvals';
$lang['bdm_kpi_email_open']           = 'Email Open Rate %';
$lang['bdm_kpi_linkedin_growth']      = 'LinkedIn Growth';
$lang['bdm_kpi_conversion']           = 'Conversion Rate %';
$lang['bdm_kpi_pipeline_value']       = 'Pipeline Value (AED)';
$lang['bdm_kpi_tenders_won']          = 'Tenders Won';

// Dashboard widgets
$lang['bdm_command_center']           = 'BDM Command Center — UAE';
$lang['bdm_total_brands']             = 'Total Brands';
$lang['bdm_active_registrations']     = 'Active Registrations';
$lang['bdm_overdue_followups']        = 'Overdue Follow-ups';
$lang['bdm_pending_referrals']        = 'Pending Referrals';
$lang['bdm_conversion_rate']          = 'Conversion Rate';
$lang['bdm_national_dists']           = 'National Vendors';
$lang['bdm_intl_dists']               = 'International Vendors';
$lang['bdm_unique_countries']         = 'Countries';
$lang['bdm_30d_activity']             = '30-Day Activity';
$lang['bdm_active_bdms']              = 'Active BDMs';
$lang['bdm_approved_vrs']             = 'Approved Vendors';
$lang['bdm_pipeline_status']          = 'Pipeline Status';
$lang['bdm_pipeline_step_dist']       = '10-Step Pipeline Distribution';
$lang['bdm_segment_dist']             = 'Segment / ICP Distribution';
$lang['bdm_revenue_trend']            = 'Revenue Trend (Last 6 Months, AED)';
$lang['bdm_channel_performance']      = 'Marketing Channel Performance';
$lang['bdm_performance_leaderboard']  = 'BDM Performance Leaderboard';
$lang['bdm_agent']                    = 'BDM Agent';
$lang['bdm_performance']              = 'Performance';
$lang['bdm_workspace']                = 'BDM Operational Workspace';
$lang['bdm_my_brand_research']        = 'Brand Research';
$lang['bdm_my_active_vrs']            = 'Active Vendor Registrations';
$lang['bdm_target_vs_actual']         = 'Target vs. Actual (Current Month)';
$lang['bdm_pushed_to_purchase']       = 'Vendor pushed to the Purchase module successfully.';
$lang['bdm_push_to_purchase_failed']  = 'Could not push vendor — Purchase module may be unavailable.';

// Tenders
$lang['bdm_tender_ref']               = 'Tender Ref';
$lang['bdm_tender_title']             = 'Title';
$lang['bdm_tender_value']             = 'Value (AED)';
$lang['bdm_tender_deadline']          = 'Submission Deadline';
$lang['bdm_tender_outcome']           = 'Outcome';
$lang['bdm_tender_documents']         = 'Documents Required';
$lang['bdm_tender_client']            = 'Client';
$lang['new_tender']                   = 'New Tender';
$lang['edit_tender']                  = 'Edit Tender';

// Campaigns
$lang['bdm_campaign_name']            = 'Campaign Name';
$lang['bdm_campaign_channel']         = 'Channel';
$lang['bdm_campaign_status']          = 'Status';
$lang['bdm_campaign_recipients']      = 'Recipients';
$lang['bdm_campaign_opens']           = 'Opens';
$lang['bdm_campaign_clicks']          = 'Clicks';
$lang['bdm_campaign_leads']           = 'Leads';
$lang['bdm_campaign_meetings']        = 'Meetings';
$lang['bdm_campaign_deals']           = 'Deals';
$lang['bdm_campaign_revenue']         = 'Revenue';
$lang['bdm_campaign_budget']          = 'Budget';
$lang['bdm_campaign_cost']            = 'Cost Actual';
$lang['bdm_campaign_audience']        = 'Target Audience';
$lang['new_campaign']                 = 'New Campaign';
$lang['edit_campaign']                = 'Edit Campaign';

// Channels
$lang['channel_direct']      = 'Direct (Field Sales)';
$lang['channel_telecalling'] = 'Telecalling';
$lang['channel_email']       = 'Email';
$lang['channel_social']      = 'Social Media';
$lang['channel_seo']         = 'SEO / Web';

// Segments (ICP)
$lang['segment_construction']     = 'Construction';
$lang['segment_fm']               = 'Facility Management';
$lang['segment_government']       = 'Government Projects';
$lang['segment_it_companies']     = 'IT Companies';
$lang['segment_telecom']          = 'Telecom Providers';
$lang['segment_hospitality']      = 'Hospitality';
$lang['segment_other']            = 'Other';

// Priority
$lang['priority_high']   = 'High';
$lang['priority_medium'] = 'Medium';
$lang['priority_low']    = 'Low';

// Commissions
$lang['bdm_commission_recorded'] = 'Commission recorded successfully.';
$lang['bdm_commission_paid']     = 'Commission marked as paid.';
$lang['bdm_deal_value']          = 'Deal Value';
$lang['bdm_deal_reference']      = 'Deal Reference';
$lang['bdm_commission_amount']   = 'Commission Amount';
$lang['bdm_commission_status']   = 'Status';
$lang['bdm_commission_accrued']  = 'Accrued';
$lang['bdm_commission_paid_lbl'] = 'Paid';

// Targets
$lang['bdm_targets_saved'] = 'KPI Targets saved successfully.';
$lang['bdm_target']        = 'Target';
$lang['bdm_actual']        = 'Actual';
$lang['bdm_period']        = 'Period';
$lang['bdm_year']          = 'Year';
$lang['bdm_month']         = 'Month';

// Misc
$lang['bdm_priority']         = 'Priority';
$lang['bdm_lead_source']      = 'Lead Source';
$lang['bdm_segment']          = 'Segment';
$lang['bdm_decision_maker']   = 'Decision Maker';
$lang['bdm_decision_role']    = 'Decision Maker Role';
$lang['bdm_expected_value']   = 'Expected Value (AED)';
$lang['bdm_won_value']        = 'Won Value (AED)';
$lang['bdm_target_close_date']= 'Target Close Date';
$lang['bdm_documents']        = 'Documents Submitted';
$lang['bdm_compliance']       = 'UAE Compliance';
$lang['bdm_compliance_iso']   = 'ISO Certificate';
$lang['bdm_compliance_tl']    = 'Trade License';
$lang['bdm_compliance_vat']   = 'VAT TRN';
$lang['bdm_compliance_cr']    = 'CR Number';
