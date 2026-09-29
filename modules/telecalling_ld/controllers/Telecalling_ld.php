<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * Telecalling_ld.php — Main Controller
 * ─────────────────────────────────────────────────────────────────────
 * HOW TO ADD A NEW PAGE:
 *   1. Add public function below
 *   2. Create view in views/admin/ (or views/agent/)
 *   3. Load using the 3-part pattern:
 *        $this->_view('admin/YOUR_VIEW', $d);
 *
 * URL pattern: /admin/telecalling_ld/method_name
 * ─────────────────────────────────────────────────────────────────────
 */
class Telecalling_ld extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('telecalling_ld/Telecalling_ld_model','tc');
        require_once FCPATH.'modules/telecalling_ld/helpers/tc_distribution.php';
        require_once FCPATH.'modules/telecalling_ld/helpers/tc_email.php';
    }

    /* ─── HELPER: load views with Perfex layout ─────────────────── */
    private function _view($view, $data=[])
    {
        // Perfex AdminController wraps views automatically - just load the view
        $this->load->view($view, $data);
    }

    /* ═══════════════════════════════════════════════════════════════
       DASHBOARD — routes to correct role dashboard
    ═══════════════════════════════════════════════════════════════ */
    public function index()
    {
        if (is_admin())          $this->_admin_dashboard();
        elseif (tc_is_manager()) $this->_group_dashboard();
        else                     $this->_agent_dashboard();
    }

    private function _admin_dashboard()
    {
        $period = $this->input->get('period') ?: 'week';
        [$from,$to] = tc_period_range($period);
        $pfx = $this->db->dbprefix;
        $d = [
            'title'              => 'Telecalling Dashboard',
            'page_title'         => 'Admin Dashboard',
            'period'             => $period,
            'total_leads'        => $this->db->count_all('tblleads'),
            'unassigned_leads'   => $this->db->query("SELECT COUNT(*) AS c FROM tblleads l LEFT JOIN {$pfx}tc_assignments a ON a.lead_id=l.id WHERE a.id IS NULL")->row()->c,
            'total_calls'        => $this->db->where("DATE(called_at) BETWEEN '$from' AND '$to'")->count_all_results($pfx.'tc_call_log'),
            'total_appointments' => $this->db->where("DATE(called_at) BETWEEN '$from' AND '$to'")->where('call_status','appointment_scheduled')->count_all_results($pfx.'tc_call_log'),
            'total_conversions'  => $this->db->where("DATE(called_at) BETWEEN '$from' AND '$to'")->where('call_status','converted')->count_all_results($pfx.'tc_call_log'),
            'total_calls_today'  => $this->db->where("DATE(called_at)", date('Y-m-d'))->count_all_results($pfx.'tc_call_log'),
            'agent_stats'        => $this->tc->get_agent_stats($from,$to),
            'groups'             => $this->tc->get_groups(),
            'daily_chart'        => $this->tc->get_daily_data(7),
            'funnel'             => $this->tc->get_funnel($from,$to),
            'email_stats'        => $this->tc->get_email_stats($from,$to),
        ];
        $this->_view('admin/dashboard',$d);
    }

    private function _group_dashboard()
    {
        $period = $this->input->get('period') ?: 'week';
        [$from,$to] = tc_period_range($period);
        $staff_id = get_staff_user_id();
        $group = $this->tc->get_manager_group($staff_id);
        if (!$group) { redirect(admin_url('telecalling_ld')); return; }
        $agents = $this->tc->get_group_members($group->id);
        $agent_ids = implode(',', array_column((array)$agents,'staffid') ?: [0]);
        $pfx = $this->db->dbprefix;
        $d = [
            'title'   => 'Group Dashboard', 'page_title'=>'Group Dashboard',
            'period'  => $period, 'group'=>$group,
            'agents'  => $this->tc->get_agent_stats($from,$to),
            'grp_calls'=> $this->db->query("SELECT COUNT(*) AS c FROM {$pfx}tc_call_log WHERE agent_id IN($agent_ids) AND DATE(called_at) BETWEEN '$from' AND '$to'")->row()->c,
            'grp_appts'=> $this->db->query("SELECT COUNT(*) AS c FROM {$pfx}tc_call_log WHERE agent_id IN($agent_ids) AND call_status='appointment_scheduled' AND DATE(called_at) BETWEEN '$from' AND '$to'")->row()->c,
            'grp_convs'=> $this->db->query("SELECT COUNT(*) AS c FROM {$pfx}tc_call_log WHERE agent_id IN($agent_ids) AND call_status='converted' AND DATE(called_at) BETWEEN '$from' AND '$to'")->row()->c,
            'daily_chart'=> $this->tc->get_daily_data(7,null,$group->id),
        ];
        $this->_view('group_leader/dashboard',$d);
    }

    private function _agent_dashboard()
    {
        $aid   = get_staff_user_id();
        $today = date('Y-m-d');
        $ws    = date('Y-m-d',strtotime('monday this week'));
        $ms    = date('Y-m-01');
        $pfx   = $this->db->dbprefix;
        $d = [
            'title'      => 'My Dashboard', 'page_title'=>'Agent Dashboard',
            'staff'      => $this->db->get_where('tblstaff',['staffid'=>$aid])->row(),
            'calls_today'=> $this->db->where('agent_id',$aid)->where("DATE(called_at)='$today'")->count_all_results($pfx.'tc_call_log'),
            'calls_week' => $this->db->where('agent_id',$aid)->where("DATE(called_at) BETWEEN '$ws' AND '$today'")->count_all_results($pfx.'tc_call_log'),
            'calls_month'=> $this->db->where('agent_id',$aid)->where("DATE(called_at) BETWEEN '$ms' AND '$today'")->count_all_results($pfx.'tc_call_log'),
            'my_leads'   => $this->tc->get_agent_assignments($aid,0),
            'daily_chart'=> $this->tc->get_daily_data(7,$aid),
            'status_breakdown'=> $this->tc->get_status_breakdown($aid,$ms,$today),
            'target_calls'=> tc_target_status($aid,'calls','daily'),
            'target_appts'=> tc_target_status($aid,'appointments','daily'),
            'target_convs'=> tc_target_status($aid,'conversions','daily'),
            'pending_requests'=> $this->db->where(['agent_id'=>$aid,'status'=>'pending'])->count_all_results($pfx.'tc_lead_requests'),
        ];
        $this->_view('agent/dashboard',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       MY LEADS (agent)
    ═══════════════════════════════════════════════════════════════ */
    public function my_leads()
    {
        $aid = get_staff_user_id();
        $d   = [
            'title'     => 'My Leads', 'page_title'=>'My Leads',
            'my_leads'  => $this->tc->get_agent_assignments($aid,0),
            'done_leads'=> $this->tc->get_agent_assignments($aid,1),
            'campaigns' => $this->tc->get_campaigns(),
        ];
        $this->_view('agent/my_leads',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       LOG CALL (AJAX post from agent)
    ═══════════════════════════════════════════════════════════════ */
    public function log_call()
    {
        $lead_id = (int)$this->input->post('lead_id');
        $status  = $this->input->post('tc_call_status');
        $notes   = $this->input->post('tc_notes');
        $scores  = ['not_answered'=>0,'callback_later'=>5,'not_interested'=>10,'interested'=>25,'appointment_scheduled'=>60,'converted'=>80];
        $score   = $scores[$status] ?? 0;
        $aid     = get_staff_user_id();
        $pfx     = $this->db->dbprefix;
        $this->db->insert($pfx.'tc_call_log',['lead_id'=>$lead_id,'agent_id'=>$aid,'call_status'=>$status,'notes'=>$notes,'lead_score'=>$score,'called_at'=>date('Y-m-d H:i:s')]);
        if (in_array($status,['not_interested','appointment_scheduled','converted'])) {
            $this->db->where(['lead_id'=>$lead_id,'agent_id'=>$aid])->update($pfx.'tc_assignments',['completed'=>1,'status'=>$status,'completed_at'=>date('Y-m-d H:i:s')]);
            tc_auto_assign_next($aid);
        }
        tc_check_score_trigger($lead_id,$score);
        tc_check_target_achieved($aid);
        set_alert('success','Call logged successfully.');
        redirect(admin_url('telecalling_ld/my_leads'));
    }

    /* ═══════════════════════════════════════════════════════════════
       ASSIGN LEADS (admin)
    ═══════════════════════════════════════════════════════════════ */
    public function assign()
    {
        if (!is_admin() && !(tc_is_manager() && tc_setting('tc_leader_can_assign')=='1')) show_404();
        $pfx = $this->db->dbprefix;
        if ($this->input->post()) {
            $mode     = $this->input->post('mode');
            $agent_id = (int)$this->input->post('agent_id');
            $count    = (int)$this->input->post('count');
            $group_id = (int)$this->input->post('group_id') ?: null;
            $from_id  = (int)$this->input->post('from_id');
            $to_id    = (int)$this->input->post('to_id');
            if ($mode === 'rotation') {
                $leads = $this->tc->get_unassigned_leads($count);
                foreach ($leads as $l) $this->db->replace($pfx.'tc_rotation_queue',['lead_id'=>$l->id,'priority'=>0]);
                set_alert('success',count($leads).' leads added to rotation queue.');
            } elseif ($mode === 'range') {
                $n = tc_range_assign($agent_id,$from_id,$to_id,$group_id);
                set_alert('success',"$n leads assigned (range $from_id–$to_id).");
            } elseif ($mode === 'group') {
                $n = tc_group_distribute($group_id,$count);
                set_alert('success',"$n leads distributed across group.");
            } else {
                $n = tc_bulk_assign($agent_id,$count,$group_id);
                set_alert('success',"$n leads assigned.");
            }
            redirect(admin_url('telecalling_ld/assign'));
        }
        $d = [
            'title'          => 'Assign Leads','page_title'=>'Assign Leads to Agents',
            'staff'          => $this->db->where('admin',0)->where('active',1)->order_by('firstname')->get('tblstaff')->result(),
            'groups'         => $this->tc->get_groups(),
            'unassigned_count'=> $this->db->query("SELECT COUNT(*) AS c FROM tblleads l LEFT JOIN {$pfx}tc_assignments a ON a.lead_id=l.id WHERE a.id IS NULL")->row()->c,
            'queue_count'    => $this->db->count_all($pfx.'tc_rotation_queue'),
            'active_agents'  => $this->db->where('admin',0)->where('active',1)->count_all_results('tblstaff'),
        ];
        $this->_view('admin/assign',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       GROUPS
    ═══════════════════════════════════════════════════════════════ */
    public function groups($id=null)
    {
        if (!is_admin()) show_404();
        if ($this->input->post('action')==='save') {
            $data = ['name'=>$this->input->post('name'),'description'=>$this->input->post('description'),
                'manager_id'=>(int)$this->input->post('manager_id')?:null,'color'=>$this->input->post('color'),
                'weekly_call_target'=>(int)$this->input->post('weekly_call_target'),'active'=>1,
                'leader_can_assign'=>(int)$this->input->post('leader_can_assign')];
            $gid = $this->tc->save_group($data,$id?:null);
            set_alert('success',$id?'Group updated.':'Group created.');
            redirect(admin_url('telecalling_ld/groups/'.$gid));
        }
        if ($this->input->post('action')==='add_member')    { $this->tc->add_group_member($id,(int)$this->input->post('agent_id')); set_alert('success','Member added.'); redirect(admin_url('telecalling_ld/groups/'.$id)); }
        if ($this->input->post('action')==='remove_member') { $this->tc->remove_group_member($id,(int)$this->input->post('agent_id')); set_alert('success','Member removed.'); redirect(admin_url('telecalling_ld/groups/'.$id)); }
        $d = [
            'title'=>'Groups','page_title'=>'Manage Groups',
            'groups'   => $this->tc->get_groups(false),
            'group'    => $id ? $this->tc->get_group($id) : null,
            'members'  => $id ? $this->tc->get_group_members($id) : [],
            'available'=> $id ? $this->tc->get_available_agents($id) : [],
            'all_staff'=> $this->db->where('admin',0)->where('active',1)->order_by('firstname')->get('tblstaff')->result(),
        ];
        $this->_view('admin/groups',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       TARGETS
    ═══════════════════════════════════════════════════════════════ */
    public function targets()
    {
        if (!is_admin()) show_404();
        if ($this->input->post('action')==='save') {
            $data=['agent_id'=>(int)$this->input->post('agent_id')?:null,'group_id'=>(int)$this->input->post('group_id')?:null,
                'target_type'=>$this->input->post('target_type'),'period'=>$this->input->post('period'),
                'target_value'=>(int)$this->input->post('target_value'),'start_date'=>date('Y-m-d')];
            $this->tc->save_target($data);
            set_alert('success','Target saved.'); redirect(admin_url('telecalling_ld/targets'));
        }
        if ($this->input->post('action')==='delete') { $this->tc->delete_target((int)$this->input->post('target_id')); set_alert('success','Target deleted.'); redirect(admin_url('telecalling_ld/targets')); }
        $d = ['title'=>'Targets','page_title'=>'Performance Targets',
            'targets'=>$this->tc->get_targets(),'agents'=>$this->db->where('admin',0)->where('active',1)->order_by('firstname')->get('tblstaff')->result(),
            'groups'=>$this->tc->get_groups()];
        $this->_view('admin/targets',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       CAMPAIGNS & TEMPLATES
    ═══════════════════════════════════════════════════════════════ */
    public function campaigns($id=null)
    {
        if (!is_admin()) show_404();
        if ($this->input->post('action')==='save_campaign') {
            $data=['name'=>$this->input->post('name'),'description'=>$this->input->post('description'),
                'trigger_score'=>(int)$this->input->post('trigger_score'),'status'=>$this->input->post('status'),
                'created_by'=>get_staff_user_id()];
            $cid=$this->tc->save_campaign($data,$id?:null);
            set_alert('success','Campaign saved.'); redirect(admin_url('telecalling_ld/campaigns/'.$cid));
        }
        if ($this->input->post('action')==='delete_campaign') { $this->tc->delete_campaign($id); set_alert('success','Campaign deleted.'); redirect(admin_url('telecalling_ld/campaigns')); }
        if ($this->input->post('action')==='save_template') {
            $data=['campaign_id'=>$id,'subject'=>$this->input->post('subject'),'body'=>$this->input->post('body'),
                'delay_days'=>(int)$this->input->post('delay_days'),'sort_order'=>(int)$this->input->post('sort_order')];
            $tid=$this->input->post('template_id'); $this->tc->save_template($data,$tid?:null);
            set_alert('success','Template saved.'); redirect(admin_url('telecalling_ld/campaigns/'.$id));
        }
        if ($this->input->post('action')==='delete_template') { $this->tc->delete_template((int)$this->input->post('template_id')); set_alert('success','Template deleted.'); redirect(admin_url('telecalling_ld/campaigns/'.$id)); }
        $d=['title'=>'Campaigns','page_title'=>'Email Campaigns',
            'campaigns'=>$this->tc->get_campaigns(),
            'campaign'=>$id?$this->tc->get_campaign($id):null,
            'templates'=>$id?$this->tc->get_templates($id):[],
        ];
        $this->_view('admin/campaigns',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       REPORTS
    ═══════════════════════════════════════════════════════════════ */
    public function reports()
    {
        if (!is_admin() && !tc_is_manager()) show_404();
        $from     = $this->input->get('from') ?: date('Y-m-01');
        $to       = $this->input->get('to')   ?: date('Y-m-d');
        $agent_id = (int)$this->input->get('agent_id') ?: null;
        $group_id = (int)$this->input->get('group_id') ?: null;
        $status   = $this->input->get('status') ?: null;
        $d = ['title'=>'Reports','page_title'=>'Reports & Analytics','from'=>$from,'to'=>$to,
            'filters'=>compact('agent_id','group_id','status'),
            'logs'       => $this->tc->get_call_log(compact('agent_id','group_id','status','from','to')),
            'agent_stats'=> $this->tc->get_agent_stats($from,$to),
            'funnel'     => $this->tc->get_funnel($from,$to),
            'email_stats'=> $this->tc->get_email_stats($from,$to),
            'agents'     => $this->db->where('admin',0)->where('active',1)->order_by('firstname')->get('tblstaff')->result(),
            'groups'     => $this->tc->get_groups(),
            'statuses'   => ['not_answered','callback_later','not_interested','interested','appointment_scheduled','converted'],
        ];
        $this->_view('admin/reports',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       REQUESTS
    ═══════════════════════════════════════════════════════════════ */
    public function requests()
    {
        if (!is_admin()) show_404();
        $pfx = $this->db->dbprefix;
        if ($this->input->post('action')==='approve') {
            $req_id=(int)$this->input->post('request_id');
            $req=$this->db->get_where($pfx.'tc_lead_requests',['id'=>$req_id,'status'=>'pending'])->row();
            if ($req) { $n=tc_bulk_assign($req->agent_id,$req->requested); $this->tc->update_request($req_id,['status'=>'approved','actioned_at'=>date('Y-m-d H:i:s'),'actioned_by'=>get_staff_user_id()]); set_alert('success',"Approved. $n leads assigned."); }
            redirect(admin_url('telecalling_ld/requests'));
        }
        if ($this->input->post('action')==='reject') {
            $req_id=(int)$this->input->post('request_id');
            $this->tc->update_request($req_id,['status'=>'rejected','actioned_at'=>date('Y-m-d H:i:s'),'actioned_by'=>get_staff_user_id(),'admin_note'=>$this->input->post('admin_note')]);
            set_alert('warning','Request rejected.'); redirect(admin_url('telecalling_ld/requests'));
        }
        $d=['title'=>'Requests','page_title'=>'Lead Requests','requests'=>$this->tc->get_requests()];
        $this->_view('admin/requests',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       SETTINGS
    ═══════════════════════════════════════════════════════════════ */
    public function settings()
    {
        if (!is_admin()) show_404();
        if ($this->input->post()) {
            $keys=['tc_email_method','tc_smtp_host','tc_smtp_port','tc_smtp_user','tc_smtp_pass','tc_email_from',
                   'tc_ses_key','tc_ses_secret','tc_ses_region','tc_wa_token','tc_wa_phone_id','tc_wa_account_id',
                   'tc_ai_enabled','tc_ai_provider','tc_ai_api_key','tc_leader_can_assign','tc_default_leads'];
            foreach ($keys as $k) tc_save_setting($k,$this->input->post($k));
            set_alert('success','Settings saved.'); redirect(admin_url('telecalling_ld/settings'));
        }
        $d=['title'=>'Settings','page_title'=>'Module Settings'];
        $settings=[]; foreach ($this->db->get($this->db->dbprefix.'tc_settings')->result() as $r) $settings[$r->skey]=$r->svalue;
        $d['settings']=$settings;
        $this->_view('admin/settings',$d);
    }

    /* ═══════════════════════════════════════════════════════════════
       REQUEST MORE LEADS (agent)
    ═══════════════════════════════════════════════════════════════ */
    public function request_leads()
    {
        $aid=$aid=get_staff_user_id(); $pfx=$this->db->dbprefix;
        if ($this->db->where(['agent_id'=>$aid,'status'=>'pending'])->count_all_results($pfx.'tc_lead_requests')>0) { set_alert('warning','You already have a pending request.'); redirect(admin_url('telecalling_ld')); return; }
        $count=(int)($this->input->post('count')?:tc_setting('tc_default_leads')?:10);
        $this->tc->save_request(['agent_id'=>$aid,'requested'=>$count,'message'=>$this->input->post('message'),'requested_at'=>date('Y-m-d H:i:s')]);
        foreach ($this->db->where('admin',1)->get('tblstaff')->result() as $adm)
            add_notification(['description'=>'tc_lead_request_submitted','touserid'=>$adm->staffid,'link'=>admin_url('telecalling_ld/requests')]);
        set_alert('success','Request submitted.'); redirect(admin_url('telecalling_ld'));
    }

    /* ═══════════════════════════════════════════════════════════════
       EMAIL TRACKING — open pixel + unsubscribe
    ═══════════════════════════════════════════════════════════════ */
    public function track_open($token)
    {
        $this->db->where('track_token',$token)->where('opened_at IS NULL')->update($this->db->dbprefix.'tc_email_log',['opened_at'=>date('Y-m-d H:i:s')]);
        // Return 1x1 transparent pixel
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }

    public function unsubscribe($token)
    {
        $ok = tc_handle_unsubscribe($token);
        $msg = $ok ? 'You have been unsubscribed successfully.' : 'Invalid unsubscribe link.';
        echo '<html><body style="font-family:Arial;text-align:center;padding:60px"><h2>'.$msg.'</h2></body></html>';
        exit;
    }

    /* ═══════════════════════════════════════════════════════════════
       AWS SES BOUNCE WEBHOOK
    ═══════════════════════════════════════════════════════════════ */
    public function bounce_webhook()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw,true);
        if (!$data) { http_response_code(400); exit; }
        if (($data['Type']??'') === 'SubscriptionConfirmation') { file_get_contents($data['SubscribeURL']); exit; }
        $msg = json_decode($data['Message']??'{}',true);
        if (($msg['notificationType']??'') === 'Bounce') {
            foreach ($msg['bounce']['bouncedRecipients']??[] as $r) tc_handle_bounce($r['emailAddress'],$msg['bounce']['bounceType']??'hard_bounce');
        }
        http_response_code(200); echo 'ok'; exit;
    }

    /* ═══════════════════════════════════════════════════════════════
       SEND MANUAL EMAIL (AJAX from lead profile / my_leads)
    ═══════════════════════════════════════════════════════════════ */
    public function send_email_manual()
    {
        if (!tc_setting('tc_manual_email_enabled') && !is_admin()) { echo json_encode(['error'=>'Not permitted']); exit; }
        $lead_id     = (int)$this->input->post('lead_id');
        $template_id = (int)$this->input->post('template_id');
        $ok = tc_send_email($lead_id,$template_id);
        echo json_encode(['success'=>$ok]); exit;
    }

    /* ═══════════════════════════════════════════════════════════════
       SEND WHATSAPP (AJAX)
    ═══════════════════════════════════════════════════════════════ */
    public function send_whatsapp()
    {
        require_once FCPATH.'modules/telecalling_ld/helpers/tc_whatsapp.php';
        $lead_id = (int)$this->input->post('lead_id');
        $message = $this->input->post('message');
        $ok = tc_wa_send($lead_id,$message);
        echo json_encode(['success'=>$ok]); exit;
    }
}
