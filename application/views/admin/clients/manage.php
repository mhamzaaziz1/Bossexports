<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            
            <div class="_filters _hidden_inputs hidden">
               <?php
                  echo form_hidden('my_customers');
                  echo form_hidden('requires_registration_confirmation');
                  foreach($groups as $group){ echo form_hidden('customer_group_'.$group['id']); }
                  foreach($contract_types as $type){ echo form_hidden('contract_type_'.$type['id']); }
                  foreach($invoice_statuses as $status){ echo form_hidden('invoices_'.$status); }
                  foreach($estimate_statuses as $status){ echo form_hidden('estimates_'.$status); }
                  foreach($project_statuses as $status){ echo form_hidden('projects_'.$status['id']); }
                  foreach($proposal_statuses as $status){ echo form_hidden('proposals_'.$status); }
                  foreach($customer_admins as $cadmin){ echo form_hidden('responsible_admin_'.$cadmin['staff_id']); }
                  foreach($countries as $country){ echo form_hidden('country_'.$country['country_id']); }
                  ?>
            </div>

            <?php if(has_permission('customers','','view') || have_assigned_customers()) { 
                // --- 1. DATA COLLECTION ---
                $where_summary = '';
                if(!has_permission('customers','','view')){
                    $where_summary = ' AND userid IN (SELECT customer_id FROM '.db_prefix().'customer_admins WHERE staff_id='.get_staff_user_id().')';
                }
                $base_currency = get_base_currency();

                // A. Customer Counts
                $total_clients = total_rows(db_prefix().'clients',($where_summary != '' ? substr($where_summary,5) : ''));
                $active_clients = total_rows(db_prefix().'clients','active=1'.$where_summary);
                $inactive_clients = total_rows(db_prefix().'clients','active=0'.$where_summary);
                $contacts_online = total_rows(db_prefix().'contacts','last_login LIKE "'.date('Y-m-d').'%"'.$where_summary);
                $new_clients_month = total_rows(db_prefix().'clients', 'datecreated LIKE "'.date('Y-m').'%"'.$where_summary);

                // B. Ratios
                $retention_rate = ($total_clients > 0) ? round(($active_clients / $total_clients) * 100, 1) : 0;
                $churn_rate = ($total_clients > 0) ? round(($inactive_clients / $total_clients) * 100, 1) : 0;

                // C. Financials
                // 1. Outstanding Balance (Invoices not Paid or Cancelled)
                $q_balance = $this->db->query('SELECT SUM(total) as total, (SELECT IFNULL(SUM(amount),0) FROM '.db_prefix().'invoicepaymentrecords WHERE invoiceid IN (SELECT id FROM '.db_prefix().'invoices WHERE status != 5)) as paid FROM '.db_prefix().'invoices WHERE status != 5 AND status != 2');
                $balance_row = $q_balance->row();
                $total_balance_due = ($balance_row->total - $balance_row->paid);

                // 2. Business This Month
                $this->db->select('SUM(total) as amount, COUNT(id) as count');
                $this->db->where('date LIKE', date('Y-m').'%');
                $this->db->where('status !=', 5); 
                $month_stats = $this->db->get(db_prefix().'invoices')->row();
                $month_amount = $month_stats->amount ?? 0;
                $month_count = $month_stats->count ?? 0;

                // D. Chart Data
                $this->db->select("DATE_FORMAT(datecreated, '%Y-%m') as m, count(userid) as total");
                $this->db->from(db_prefix().'clients');
                $this->db->group_by('m');
                $this->db->order_by('datecreated', 'DESC');
                $this->db->limit(12);
                $chart_data_raw = array_reverse($this->db->get()->result_array());
                $chart_labels = []; $chart_values = [];
                foreach($chart_data_raw as $d){
                    $chart_labels[] = date('M Y', strtotime($d['m'] . '-01'));
                    $chart_values[] = $d['total'];
                }

                // E. Groups Data
                $this->db->select('g.name, COUNT(c.customer_id) as count');
                $this->db->from(db_prefix().'customers_groups g');
                $this->db->join(db_prefix().'customer_groups c', 'c.groupid = g.id', 'left');
                $this->db->group_by('g.id');
                $this->db->order_by('count', 'DESC');
                $this->db->limit(5);
                $group_stats = $this->db->get()->result_array();
            ?>
            
            <style>
                .client-stat-card {
                    background: #fff; border-radius: 8px; padding: 20px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #eef2f4;
                    display: flex; flex-direction: column; justify-content: space-between;
                    height: 100%; min-height: 120px; position: relative; overflow: hidden;
                    transition: all 0.3s ease;
                }
                .client-stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
                
                .stat-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; z-index: 2; position: relative; }
                .stat-value { font-size: 24px; font-weight: 700; color: #333; line-height: 1.2; margin-bottom: 2px; }
                .stat-label { font-size: 12px; color: #9aa0ac; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
                .stat-sub { font-size: 13px; font-weight: 500; }
                
                .stat-icon { 
                    width: 45px; height: 45px; border-radius: 12px; 
                    display: flex; align-items: center; justify-content: center; font-size: 20px; 
                    opacity: 0.8;
                }
                
                /* Progress Bars */
                .mini-progress { height: 4px; background: #f0f0f0; border-radius: 2px; margin-top: 15px; overflow: hidden; }
                .mini-progress-bar { height: 100%; border-radius: 2px; }

                /* Colors & Themes */
                .theme-blue { color: #03a9f4; background: rgba(3, 169, 244, 0.1); }
                .theme-green { color: #84c529; background: rgba(132, 197, 41, 0.1); }
                .theme-red { color: #fc2d42; background: rgba(252, 45, 66, 0.1); }
                .theme-orange { color: #ff6f00; background: rgba(255, 111, 0, 0.1); }
                .theme-purple { color: #7c4dff; background: rgba(124, 77, 255, 0.1); }
                .theme-teal { color: #009688; background: rgba(0, 150, 136, 0.1); }

                .text-blue { color: #03a9f4; }
                .text-green { color: #84c529; }
                .text-red { color: #fc2d42; }
                
                .bg-fill-blue { background-color: #03a9f4; }
                .bg-fill-green { background-color: #84c529; }
                .bg-fill-red { background-color: #fc2d42; }
                .bg-fill-orange { background-color: #ff6f00; }

                /* Dashboard Panels */
                .dashboard-panel { background: #fff; border-radius: 8px; padding: 20px; border: 1px solid #eef2f4; min-height: 380px; }
                .panel-title-custom { font-size: 16px; font-weight: 700; color: #323a45; margin-bottom: 25px; display: block; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; }
                
                .group-item { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f9f9f9; }
                .group-name { font-weight: 500; font-size: 14px; color: #444; }
                .group-count { background: #f0f2f5; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; color: #666; }
            </style>

            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-default pull-right" style="margin-bottom: 15px;" type="button" data-toggle="collapse" data-target="#customer-stats" aria-expanded="true">
                        <i class="fa fa-bar-chart"></i> Toggle Dashboard
                    </button>
                </div>
            </div>

            <div class="collapse in" id="customer-stats">
                
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Total Customers</div>
                                    <div class="stat-value"><?php echo $total_clients; ?></div>
                                </div>
                                <div class="stat-icon theme-blue"><i class="fa fa-users"></i></div>
                            </div>
                            <div class="mini-progress"><div class="mini-progress-bar bg-fill-blue" style="width: 100%"></div></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Active Customers</div>
                                    <div class="stat-value"><?php echo $active_clients; ?></div>
                                </div>
                                <div class="stat-icon theme-green"><i class="fa fa-check"></i></div>
                            </div>
                            <div class="mini-progress"><div class="mini-progress-bar bg-fill-green" style="width: <?php echo ($total_clients>0 ? ($active_clients/$total_clients)*100 : 0); ?>%"></div></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Inactive</div>
                                    <div class="stat-value"><?php echo $inactive_clients; ?></div>
                                </div>
                                <div class="stat-icon theme-red"><i class="fa fa-times"></i></div>
                            </div>
                            <div class="mini-progress"><div class="mini-progress-bar bg-fill-red" style="width: <?php echo ($total_clients>0 ? ($inactive_clients/$total_clients)*100 : 0); ?>%"></div></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Contacts Online</div>
                                    <div class="stat-value"><?php echo $contacts_online; ?></div>
                                </div>
                                <div class="stat-icon theme-orange"><i class="fa fa-user-circle-o"></i></div>
                            </div>
                            <div class="mini-progress"><div class="mini-progress-bar bg-fill-orange" style="width: 100%"></div></div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 25px;">
                    <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Total Balance Due</div>
                                    <div class="stat-value"><?php echo app_format_money($total_balance_due, $base_currency); ?></div>
                                    <div class="stat-sub text-red">Unpaid Invoices</div>
                                </div>
                                <div class="stat-icon theme-red"><i class="fa fa-balance-scale"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Business (<?php echo date('M'); ?>)</div>
                                    <div class="stat-value"><?php echo app_format_money($month_amount, $base_currency); ?></div>
                                    <div class="stat-sub text-green"><?php echo $month_count; ?> Invoices Generated</div>
                                </div>
                                <div class="stat-icon theme-green"><i class="fa fa-line-chart"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Growth (<?php echo date('M'); ?>)</div>
                                    <div class="stat-value"><?php echo $new_clients_month; ?></div>
                                    <div class="stat-sub text-blue">New Signups</div>
                                </div>
                                <div class="stat-icon theme-teal"><i class="fa fa-user-plus"></i></div>
                            </div>
                        </div>
                    </div>
                     <div class="col-md-3">
                        <div class="client-stat-card">
                            <div class="stat-top">
                                <div>
                                    <div class="stat-label">Retention Rate</div>
                                    <div class="stat-value"><?php echo $retention_rate; ?>%</div>
                                    <div class="stat-sub text-muted">Based on Active Status</div>
                                </div>
                                <div class="stat-icon theme-purple"><i class="fa fa-refresh"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-bottom: 25px;">
                    <div class="col-md-8">
                        <div class="dashboard-panel">
                            <span class="panel-title-custom">Client Acquisition Trend (Last 12 Months)</span>
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="clientGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="dashboard-panel" style="min-height: 380px;">
                            <span class="panel-title-custom">Top 5 Customer Groups</span>
                            <?php if(empty($group_stats)){ echo '<p class="text-center text-muted mtop30">No customer groups found.</p>'; } ?>
                            
                            <div style="margin-top: 15px;">
                                <?php foreach($group_stats as $group) { ?>
                                    <div class="group-item">
                                        <span class="group-name"><?php echo $group['name']; ?></span>
                                        <span class="group-count"><?php echo $group['count']; ?> Customers</span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <?php if (has_permission('customers','','create')) { ?>
                     <a href="<?php echo admin_url('clients/client'); ?>" class="btn btn-info mright5 test pull-left display-block">
                     <?php echo _l('new_client'); ?></a>
                     <a href="<?php echo admin_url('clients/import'); ?>" class="btn btn-info pull-left display-block mright5 hidden-xs">
                     <?php echo _l('import_customers'); ?></a>
                     <?php } ?>
                     <a href="<?php echo admin_url('clients/all_contacts'); ?>" class="btn btn-info pull-left display-block mright5">
                     <?php echo _l('customer_contacts'); ?></a>
                     <div class="visible-xs">
                        <div class="clearfix"></div>
                     </div>
                     <a href="#" class="btn btn-default pull-right mright5" id="show-all-balances">Show All Balances</a>
                     <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" style="width:300px;">
                           <li class="active"><a href="#" data-cview="all" onclick="dt_custom_view('','.table-clients',''); return false;"><?php echo _l('customers_sort_all'); ?></a>
                           </li>
                           <?php if(get_option('customer_requires_registration_confirmation') == '1' || total_rows(db_prefix().'clients','registration_confirmed=0') > 0) { ?>
                           <li class="divider"></li>
                           <li>
                              <a href="#" data-cview="requires_registration_confirmation" onclick="dt_custom_view('requires_registration_confirmation','.table-clients','requires_registration_confirmation'); return false;">
                              <?php echo _l('customer_requires_registration_confirmation'); ?>
                              </a>
                           </li>
                           <?php } ?>
                           <li class="divider"></li>
                           <li>
                              <a href="#" data-cview="my_customers" onclick="dt_custom_view('my_customers','.table-clients','my_customers'); return false;">
                              <?php echo _l('customers_assigned_to_me'); ?>
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php if(count($groups) > 0){ ?>
                           <li class="dropdown-submenu pull-left groups">
                              <a href="#" tabindex="-1"><?php echo _l('customer_groups'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($groups as $group){ ?>
                                 <li><a href="#" data-cview="customer_group_<?php echo $group['id']; ?>" onclick="dt_custom_view('customer_group_<?php echo $group['id']; ?>','.table-clients','customer_group_<?php echo $group['id']; ?>'); return false;"><?php echo $group['name']; ?></a></li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <?php } ?>
                           <?php if(count($countries) > 1){ ?>
                           <li class="dropdown-submenu pull-left countries">
                              <a href="#" tabindex="-1"><?php echo _l('clients_country'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($countries as $country){ ?>
                                 <li><a href="#" data-cview="country_<?php echo $country['country_id']; ?>" onclick="dt_custom_view('country_<?php echo $country['country_id']; ?>','.table-clients','country_<?php echo $country['country_id']; ?>'); return false;"><?php echo $country['short_name']; ?></a></li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <?php } ?>
                           <li class="dropdown-submenu pull-left invoice">
                              <a href="#" tabindex="-1"><?php echo _l('invoices'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($invoice_statuses as $status){ ?>
                                 <li>
                                    <a href="#" data-cview="invoices_<?php echo $status; ?>" onclick="dt_custom_view('invoices_<?php echo $status; ?>','.table-clients','invoices_<?php echo $status; ?>'); return false;"><?php echo _l('customer_have_invoices_by',format_invoice_status($status,'',false)); ?></a>
                                 </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left estimate">
                              <a href="#" tabindex="-1"><?php echo _l('estimates'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($estimate_statuses as $status){ ?>
                                 <li>
                                    <a href="#" data-cview="estimates_<?php echo $status; ?>" onclick="dt_custom_view('estimates_<?php echo $status; ?>','.table-clients','estimates_<?php echo $status; ?>'); return false;">
                                    <?php echo _l('customer_have_estimates_by',format_estimate_status($status,'',false)); ?>
                                    </a>
                                 </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left project">
                              <a href="#" tabindex="-1"><?php echo _l('projects'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($project_statuses as $status){ ?>
                                 <li>
                                    <a href="#" data-cview="projects_<?php echo $status['id']; ?>" onclick="dt_custom_view('projects_<?php echo $status['id']; ?>','.table-clients','projects_<?php echo $status['id']; ?>'); return false;">
                                    <?php echo _l('customer_have_projects_by',$status['name']); ?>
                                    </a>
                                 </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left proposal">
                              <a href="#" tabindex="-1"><?php echo _l('proposals'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($proposal_statuses as $status){ ?>
                                 <li>
                                    <a href="#" data-cview="proposals_<?php echo $status; ?>" onclick="dt_custom_view('proposals_<?php echo $status; ?>','.table-clients','proposals_<?php echo $status; ?>'); return false;">
                                    <?php echo _l('customer_have_proposals_by',format_proposal_status($status,'',false)); ?>
                                    </a>
                                 </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <div class="clearfix"></div>
                           <?php if(count($contract_types) > 0) { ?>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left contract_types">
                              <a href="#" tabindex="-1"><?php echo _l('contract_types'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($contract_types as $type){ ?>
                                 <li>
                                    <a href="#" data-cview="contract_type_<?php echo $type['id']; ?>" onclick="dt_custom_view('contract_type_<?php echo $type['id']; ?>','.table-clients','contract_type_<?php echo $type['id']; ?>'); return false;">
                                    <?php echo _l('customer_have_contracts_by_type',$type['name']); ?>
                                    </a>
                                 </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <?php } ?>
                           <?php if(count($customer_admins) > 0 && (has_permission('customers','','create') || has_permission('customers','','edit'))){ ?>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left responsible_admin">
                              <a href="#" tabindex="-1"><?php echo _l('responsible_admin'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($customer_admins as $cadmin){ ?>
                                 <li>
                                    <a href="#" data-cview="responsible_admin_<?php echo $cadmin['staff_id']; ?>" onclick="dt_custom_view('responsible_admin_<?php echo $cadmin['staff_id']; ?>','.table-clients','responsible_admin_<?php echo $cadmin['staff_id']; ?>'); return false;">
                                    <?php echo get_staff_full_name($cadmin['staff_id']); ?>
                                    </a>
                                 </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <?php } ?>
                        </ul>
                     </div>
                  </div>
                  <div class="clearfix"></div>
                  
                  <hr class="hr-panel-heading" />
                  <a href="#" data-toggle="modal" data-target="#customers_bulk_action" class="bulk-actions-btn table-btn hide" data-table=".table-clients"><?php echo _l('bulk_actions'); ?></a>
                  <div class="modal fade bulk_actions" id="customers_bulk_action" tabindex="-1" role="dialog">
                     <div class="modal-dialog" role="document">
                        <div class="modal-content">
                           <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                           </div>
                           <div class="modal-body">
                              <?php if(has_permission('customers','','delete')){ ?>
                              <div class="checkbox checkbox-danger">
                                 <input type="checkbox" name="mass_delete" id="mass_delete">
                                 <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                              </div>
                              <hr class="mass_delete_separator" />
                              <?php } ?>
                              <div id="bulk_change">
                                 <?php echo render_select('move_to_groups_customers_bulk[]',$groups,array('id','name'),'customer_groups','', array('multiple'=>true),array(),'','',false); ?>
                                 <p class="text-danger"><?php echo _l('bulk_action_customers_groups_warning'); ?></p>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                              <a href="#" class="btn btn-info" onclick="customers_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="checkbox">
                     <input type="checkbox" checked id="exclude_inactive" name="exclude_inactive">
                     <label for="exclude_inactive"><?php echo _l('exclude_inactive'); ?> <?php echo _l('clients'); ?></label>
                  </div>
                  <div class="clearfix mtop20"></div>
                  <?php
                     $table_data = array();
                     $_table_data = array(
                      '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="clients"><label></label></div>',
                       array('name'=>_l('the_number_sign'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-number')),
                       array('name'=>_l('clients_list_company'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-company')),
                       array('name'=>_l('contact_primary'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-primary-contact')),
                       array('name'=>_l('company_primary_email'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-primary-contact-email')),
                       array('name'=>_l('clients_list_phone'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-phone')),
                       array('name'=>_l('customer_active'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-active')),
                       array('name'=>_l('customer_groups'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-groups')),
                       array('name'=>_l('Balance'), 'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-date-created')),
                      );
                     foreach($_table_data as $_t){ array_push($table_data,$_t); }
                     $custom_fields = get_custom_fields('customers',array('show_on_table'=>1));
                     foreach($custom_fields as $field){ array_push($table_data,$field['name']); }
                     $table_data = hooks()->apply_filters('customers_table_columns', $table_data);
                     render_datatable($table_data,'clients',[],[
                           'data-last-order-identifier' => 'customers',
                           'data-default-order'         => get_table_last_order('customers'),
                     ]);
                     ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   $(function(){
       // --- CHART LOGIC ---
       <?php if(isset($chart_values)) { ?>
        var ctx = document.getElementById('clientGrowthChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'New Customers',
                    data: <?php echo json_encode($chart_values); ?>,
                    borderColor: '#84c529',
                    backgroundColor: 'rgba(132, 197, 41, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
       <?php } ?>

       var CustomersServerParams = {};
       $.each($('._hidden_inputs._filters input'),function(){
          CustomersServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
      });
       CustomersServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';
   
       var tAPI = initDataTable('.table-clients', admin_url+'clients/table', [0], [0], CustomersServerParams,<?php echo hooks()->apply_filters('customers_table_default_order', json_encode(array(2,'asc'))); ?>);
       $('input[name="exclude_inactive"]').on('change',function(){
           tAPI.ajax.reload();
       });

       $('body').on('click', '.client-balance-check', function() {
           var clientID = $(this).data('id');
           var $btn = $(this);
           $btn.html('<i class="fa fa-refresh fa-spin"></i>');
           $.get(admin_url + 'clients/get_customer_balance/' + clientID, function(response) {
               $btn.parent().html(response);
           });
       });

       $('#show-all-balances').on('click', function(e) {
           e.preventDefault();
           var items = $('.client-balance-check').toArray();
           processItems(items);
       });

       function processItems(items) {
           if (items.length === 0) return;

           var item = items.shift();
           var $item = $(item);
           var clientID = $item.data('id');
           
           $item.html('<i class="fa fa-refresh fa-spin"></i>');
           $.get(admin_url + 'clients/get_customer_balance/' + clientID, function(response) {
               $item.parent().html(response);
               processItems(items);
           });
       }
   });
   function customers_bulk_action(event) {
       var r = confirm(app.lang.confirm_action_prompt);
       if (r == false) { return false; } else {
           var mass_delete = $('#mass_delete').prop('checked');
           var ids = [];
           var data = {};
           if(mass_delete == false || typeof(mass_delete) == 'undefined'){
               data.groups = $('select[name="move_to_groups_customers_bulk[]"]').selectpicker('val');
               if (data.groups.length == 0) { data.groups = 'remove_all'; }
           } else { data.mass_delete = true; }
           var rows = $('.table-clients').find('tbody tr');
           $.each(rows, function() {
               var checkbox = $($(this).find('td').eq(0)).find('input');
               if (checkbox.prop('checked') == true) { ids.push(checkbox.val()); }
           });
           data.ids = ids;
           $(event).addClass('disabled');
           setTimeout(function(){
             $.post(admin_url + 'clients/bulk_action', data).done(function() { window.location.reload(); });
         },50);
       }
   }
</script>
</body>
</html>