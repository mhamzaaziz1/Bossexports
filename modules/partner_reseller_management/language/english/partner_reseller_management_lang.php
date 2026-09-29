<?php
defined('BASEPATH') or exit('No direct script access allowed');

$lang['prm_module_name']            = 'Partner & Reseller Management';
$lang['prm_dashboard']              = 'Dashboard';
$lang['prm_dashboard_subtitle']     = 'Realtime overview of your partner program.';
$lang['prm_partners']               = 'Partners';
$lang['prm_partner']                = 'Partner';
$lang['prm_hierarchy']              = 'Hierarchy';
$lang['prm_deals']                  = 'Deals';
$lang['prm_deal']                   = 'Deal';
$lang['prm_deal_number']            = 'Deal number';
$lang['prm_register_deal']          = 'Register a deal';
$lang['prm_commissions']            = 'Commissions';
$lang['prm_commission']             = 'Commission';
$lang['prm_payouts']                = 'Payouts';
$lang['prm_mdf']                    = 'Marketing Funds (MDF)';
$lang['prm_mdf_subtitle']           = 'Allocate co-marketing funds and approve campaigns.';
$lang['prm_training']               = 'Training';
$lang['prm_courses']                = 'Courses';
$lang['prm_new_course']             = 'New course';
$lang['prm_gamification']           = 'Gamification';
$lang['prm_badges']                 = 'Badges';
$lang['prm_leaderboard']            = 'Leaderboard';
$lang['prm_reports']                = 'Reports';
$lang['prm_audit_log']              = 'Audit log';
$lang['prm_audit_subtitle']         = 'Immutable record of all important actions.';
$lang['prm_settings']               = 'Settings';

/* Partners */
$lang['prm_new_partner']            = 'New partner';
$lang['prm_edit_partner']           = 'Edit partner';
$lang['prm_partner_created']        = 'Partner created.';
$lang['prm_partner_updated']        = 'Partner updated.';
$lang['prm_partner_deleted']        = 'Partner deleted.';
$lang['prm_company']                = 'Company';
$lang['prm_company_info']           = 'Company information';
$lang['prm_partner_type']           = 'Partner type';
$lang['prm_email']                  = 'Email';
$lang['prm_phone']                  = 'Phone';
$lang['prm_website']                = 'Website';
$lang['prm_country']                = 'Country';
$lang['prm_address']                = 'Address';
$lang['prm_status']                 = 'Status';
$lang['prm_tier']                   = 'Tier';
$lang['prm_auto_tier']              = 'Auto-determined';
$lang['prm_parent_partner']         = 'Parent partner';
$lang['prm_none']                   = 'None';
$lang['prm_currency']               = 'Currency';
$lang['prm_username']               = 'Username';
$lang['prm_password']               = 'Password';
$lang['prm_leave_blank_keep']       = 'Leave blank to keep current';
$lang['prm_enable_portal_access']   = 'Enable portal access';
$lang['prm_enable_2fa']             = 'Require 2FA';
$lang['prm_settings_panel']         = 'Settings';
$lang['prm_code']                   = 'Code';
$lang['prm_type']                   = 'Type';
$lang['prm_revenue']                = 'Revenue';

/* Commission */
$lang['prm_commission_type']        = 'Commission type';
$lang['prm_commission_value']       = 'Commission value';
$lang['prm_override_rate']          = 'Override rate';
$lang['prm_commission_config_json'] = 'Commission config (JSON)';
$lang['prm_commission_config_help'] = 'For tiered: [{"min":0,"max":10000,"rate":5}]. For hybrid: {"flat":500,"pct":3}.';
$lang['prm_kind']                   = 'Kind';
$lang['prm_deal_value']             = 'Deal value';
$lang['prm_rate']                   = 'Rate';
$lang['prm_clawback']               = 'Clawback';
$lang['prm_commission_approved']    = 'Commission approved.';
$lang['prm_commission_clawed_back'] = 'Commission clawed back.';

/* Deals */
$lang['prm_client']                 = 'Client';
$lang['prm_value']                  = 'Value';
$lang['prm_stage']                  = 'Stage';
$lang['prm_score']                  = 'Score';
$lang['prm_submitted']              = 'Submitted';
$lang['prm_approval']               = 'Approval';
$lang['prm_admin_notes']            = 'Internal notes';
$lang['prm_approve_deal']           = 'Approve';
$lang['prm_reject_deal']            = 'Reject';
$lang['prm_reason']                 = 'Reason';
$lang['prm_close_won']              = 'Close as won';
$lang['prm_actual_value']           = 'Actual value';
$lang['prm_estimated_value']        = 'Estimated value';
$lang['prm_mark_won_calc_commission']= 'Mark won and calculate commission';
$lang['prm_history']                = 'History';
$lang['prm_duplicate']              = 'Duplicate';
$lang['prm_duplicate_of']           = 'Duplicate of';
$lang['prm_lock_until']             = 'Locked until';
$lang['prm_lead_score']             = 'Lead score';
$lang['prm_win_prob']               = 'win probability';
$lang['prm_description']            = 'Description';
$lang['prm_service_type']           = 'Service / Product';
$lang['prm_deal_approved']          = 'Deal approved.';
$lang['prm_deal_rejected']          = 'Deal rejected.';
$lang['prm_deal_marked_won']        = 'Deal marked as won.';
$lang['prm_deal_submitted']         = 'Your deal has been submitted.';
$lang['prm_recent_deals']           = 'Recent deals';

/* Payouts */
$lang['prm_amount']                 = 'Amount';
$lang['prm_method']                 = 'Method';
$lang['prm_requested_at']           = 'Requested at';
$lang['prm_payout_approved']        = 'Payout approved.';
$lang['prm_payout_paid']            = 'Payout marked as paid.';
$lang['prm_mark_paid']              = 'Mark as paid';
$lang['prm_reference']              = 'Reference';
$lang['prm_confirm']                = 'Confirm';
$lang['prm_payout_requested']       = 'Payout requested.';
$lang['prm_payout_below_min']       = 'Selected commissions are below the minimum payout amount.';

/* MDF */
$lang['prm_allocate_funds']         = 'Allocate funds';
$lang['prm_allocate']               = 'Allocate';
$lang['prm_fiscal_year']            = 'Fiscal year';
$lang['prm_expires']                = 'Expires';
$lang['prm_allocations']            = 'Allocations';
$lang['prm_allocated']              = 'Allocated';
$lang['prm_used']                   = 'Used';
$lang['prm_remaining']              = 'Remaining';
$lang['prm_campaigns']              = 'Campaigns';
$lang['prm_campaign']               = 'Campaign';
$lang['prm_budget']                 = 'Budget';
$lang['prm_mdf_allocated']          = 'Funds allocated.';
$lang['prm_campaign_approved']      = 'Campaign approved.';
$lang['prm_insufficient_fund']      = 'Insufficient MDF balance.';

/* Training */
$lang['prm_title']                  = 'Title';
$lang['prm_difficulty']             = 'Difficulty';
$lang['prm_duration_min']           = 'Duration (min)';
$lang['prm_pass_score']             = 'Pass score';
$lang['prm_video_url']              = 'Video URL';
$lang['prm_mandatory']              = 'Mandatory';
$lang['prm_course_created']         = 'Course created.';
$lang['prm_pts']                    = 'pts';

/* Reports */
$lang['prm_revenue_by_month']       = 'Revenue by month';
$lang['prm_revenue_by_partner']     = 'Revenue by partner';
$lang['prm_month']                  = 'Month';
$lang['prm_deals_won']              = 'Deals won';

/* Audit */
$lang['prm_when']                   = 'When';
$lang['prm_actor']                  = 'Actor';
$lang['prm_action']                 = 'Action';
$lang['prm_entity']                 = 'Entity';
$lang['prm_severity']               = 'Severity';
$lang['prm_ip']                     = 'IP';

/* Settings */
$lang['prm_settings_saved']         = 'Settings saved.';

/* Stats / cards */
$lang['prm_top_partners']           = 'Top partners';
$lang['prm_pipeline']               = 'Pipeline';

/* Permissions */
$lang['prm_perm_approve_deals']     = 'Approve deals';
$lang['prm_perm_manage_commission'] = 'Manage commissions';
$lang['prm_perm_process_payouts']   = 'Process payouts';
$lang['prm_perm_manage_mdf']        = 'Manage MDF';
$lang['prm_perm_view_audit']        = 'View audit log';

/* Portal */
$lang['prm_partner_login']          = 'Partner Login';
$lang['prm_partner_register']       = 'Register as a Partner';
$lang['prm_username_or_email']      = 'Username or email';
$lang['prm_login']                  = 'Sign in';
$lang['prm_register']               = 'Register';
$lang['prm_submit']                 = 'Submit';
$lang['prm_login_failed_not_found'] = 'No account found.';
$lang['prm_login_failed_inactive']  = 'Your account is not active yet.';
$lang['prm_login_failed_no_portal'] = 'Portal access is disabled.';
$lang['prm_login_failed_bad_password']= 'Incorrect password.';
$lang['prm_login_failed_locked']    = 'Account locked. Please try again later.';
$lang['prm_register_active']        = 'Welcome! You can sign in now.';
$lang['prm_register_pending']       = 'Thanks! Your application is pending approval.';
$lang['prm_wallet']                 = 'Wallet';
$lang['prm_profile']                = 'Profile';
$lang['prm_profile_updated']        = 'Profile updated.';
