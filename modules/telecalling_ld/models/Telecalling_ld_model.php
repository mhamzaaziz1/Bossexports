<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * Telecalling_ld_model.php — All database queries
 * ─────────────────────────────────────────────────
 * HOW TO ADD A QUERY:
 *   Add a public method here, load it in the controller as $this->tc->method()
 *   Always use $this->db->dbprefix.'tc_tablename' for table names
 */
class Telecalling_ld_model extends CI_Model
{
    private $pfx;
    public function __construct() { parent::__construct(); $this->pfx = $this->db->dbprefix; }

    /* ── GROUPS ── */
    public function get_groups($active_only = true)
    {
        if ($active_only) $this->db->where('g.active',1);
        return $this->db->select('g.*, CONCAT(s.firstname," ",s.lastname) AS manager_name, COUNT(m.id) AS member_count')
            ->from($this->pfx.'tc_groups g')
            ->join('tblstaff s','s.staffid=g.manager_id','left')
            ->join($this->pfx.'tc_group_members m','m.group_id=g.id','left')
            ->group_by('g.id')->order_by('g.name')->get()->result();
    }
    public function get_group($id) { return $this->db->get_where($this->pfx.'tc_groups',['id'=>$id])->row(); }
    public function save_group($data,$id=null) {
        if ($id) { $this->db->where('id',$id)->update($this->pfx.'tc_groups',$data); return $id; }
        $this->db->insert($this->pfx.'tc_groups',$data); return $this->db->insert_id();
    }
    public function get_group_members($group_id) {
        return $this->db->select('s.*,m.id AS member_row_id')
            ->from('tblstaff s')->join($this->pfx.'tc_group_members m','m.agent_id=s.staffid')
            ->where('m.group_id',$group_id)->get()->result();
    }
    public function add_group_member($group_id,$agent_id) {
        $this->db->replace($this->pfx.'tc_group_members',['group_id'=>$group_id,'agent_id'=>$agent_id]);
    }
    public function remove_group_member($group_id,$agent_id) {
        $this->db->where(['group_id'=>$group_id,'agent_id'=>$agent_id])->delete($this->pfx.'tc_group_members');
    }
    public function get_manager_group($staff_id) {
        return $this->db->where('manager_id',$staff_id)->get($this->pfx.'tc_groups')->row();
    }
    public function get_available_agents($group_id) {
        $current = array_column($this->db->where('group_id',$group_id)->get($this->pfx.'tc_group_members')->result_array(),'agent_id');
        $q = $this->db->select('staffid,firstname,lastname')->from('tblstaff')->where('admin',0)->where('active',1);
        if ($current) $q->where_not_in('staffid',$current);
        return $q->get()->result();
    }

    /* ── TARGETS ── */
    public function get_targets($agent_id=null,$group_id=null) {
        $q = $this->db->select('t.*,CONCAT(s.firstname," ",s.lastname) AS agent_name')
            ->from($this->pfx.'tc_targets t')->join('tblstaff s','s.staffid=t.agent_id','left');
        if ($agent_id) $q->where('t.agent_id',$agent_id);
        if ($group_id) $q->where('t.group_id',$group_id);
        return $q->get()->result();
    }
    public function save_target($data,$id=null) {
        if ($id) { $this->db->where('id',$id)->update($this->pfx.'tc_targets',$data); return $id; }
        $this->db->insert($this->pfx.'tc_targets',$data); return $this->db->insert_id();
    }
    public function delete_target($id) { $this->db->where('id',$id)->delete($this->pfx.'tc_targets'); }

    /* ── ASSIGNMENTS ── */
    public function get_unassigned_leads($limit=10) {
        return $this->db->select('l.id,l.name,l.email,l.company')
            ->from('tblleads l')->join($this->pfx.'tc_assignments a','a.lead_id=l.id','left')
            ->where('a.id IS NULL')->limit((int)$limit)->get()->result();
    }
    public function get_agent_assignments($agent_id,$completed=0) {
        return $this->db->select('l.*,a.id AS assign_id,a.status AS tc_status,a.assigned_at,
            (SELECT call_status FROM '.$this->pfx.'tc_call_log WHERE lead_id=l.id AND agent_id='.(int)$agent_id.' ORDER BY id DESC LIMIT 1) AS last_status,
            (SELECT called_at FROM '.$this->pfx.'tc_call_log WHERE lead_id=l.id AND agent_id='.(int)$agent_id.' ORDER BY id DESC LIMIT 1) AS last_called')
            ->from('tblleads l')->join($this->pfx.'tc_assignments a','a.lead_id=l.id')
            ->where('a.agent_id',$agent_id)->where('a.completed',$completed)
            ->order_by('a.assigned_at','DESC')->get()->result();
    }

    /* ── CALL LOG ── */
    public function get_call_log($filters=[]) {
        $q = $this->db->select('c.*,CONCAT(s.firstname," ",s.lastname) AS agent_name,l.name AS lead_name,l.email,l.company')
            ->from($this->pfx.'tc_call_log c')
            ->join('tblstaff s','s.staffid=c.agent_id','left')
            ->join('tblleads l','l.id=c.lead_id','left');
        if (!empty($filters['agent_id'])) $q->where('c.agent_id',$filters['agent_id']);
        if (!empty($filters['group_id'])) {
            $ids = array_column($this->db->where('group_id',$filters['group_id'])->get($this->pfx.'tc_group_members')->result_array(),'agent_id');
            if ($ids) $q->where_in('c.agent_id',$ids);
        }
        if (!empty($filters['status']))   $q->where('c.call_status',$filters['status']);
        if (!empty($filters['from']))     $q->where("DATE(c.called_at) >=",  $filters['from']);
        if (!empty($filters['to']))       $q->where("DATE(c.called_at) <=",  $filters['to']);
        return $q->order_by('c.called_at','DESC')->limit(500)->get()->result();
    }
    public function get_lead_call_log($lead_id) {
        return $this->db->select('c.*,CONCAT(s.firstname," ",s.lastname) AS agent_name')
            ->from($this->pfx.'tc_call_log c')->join('tblstaff s','s.staffid=c.agent_id','left')
            ->where('c.lead_id',$lead_id)->order_by('c.called_at','DESC')->get()->result();
    }

    /* ── AGENT STATS (for tables & charts) ── */
    public function get_agent_stats($from,$to) {
        return $this->db->query("
            SELECT s.staffid, s.firstname, s.lastname,
                   g.name AS group_name,
                   COUNT(DISTINCT a.lead_id) AS assigned,
                   SUM(a.completed) AS completed,
                   COUNT(c.id) AS calls,
                   SUM(c.call_status='appointment_scheduled') AS appointments,
                   SUM(c.call_status='converted') AS conversions,
                   SUM(c.call_status='interested') AS interested
            FROM tblstaff s
            LEFT JOIN {$this->pfx}tc_assignments a ON a.agent_id=s.staffid
            LEFT JOIN {$this->pfx}tc_call_log c ON c.agent_id=s.staffid AND DATE(c.called_at) BETWEEN '$from' AND '$to'
            LEFT JOIN {$this->pfx}tc_group_members gm ON gm.agent_id=s.staffid
            LEFT JOIN {$this->pfx}tc_groups g ON g.id=gm.group_id
            WHERE s.admin=0 AND s.active=1
            GROUP BY s.staffid ORDER BY calls DESC
        ")->result();
    }

    /* ── CHARTS DATA ── */
    public function get_daily_data($days=7,$agent_id=null,$group_id=null) {
        $results = [];
        for ($i=$days-1;$i>=0;$i--) {
            $d   = date('Y-m-d',strtotime("-$i days"));
            $q   = $this->db->where("DATE(called_at)='$d'");
            if ($agent_id) $q->where('agent_id',$agent_id);
            if ($group_id) {
                $ids = array_column($this->db->where('group_id',$group_id)->get($this->pfx.'tc_group_members')->result_array(),'agent_id');
                if ($ids) $q->where_in('agent_id',$ids);
            }
            $results[] = ['date'=>date('d M',strtotime($d)),'calls'=>$q->count_all_results($this->pfx.'tc_call_log')];
        }
        return $results;
    }
    public function get_funnel($from,$to) {
        $pfx = $this->pfx;
        $r = $this->db->query("SELECT
            COUNT(DISTINCT lead_id) AS total_leads,
            SUM(call_status='interested') AS interested,
            SUM(call_status='appointment_scheduled') AS appointments,
            SUM(call_status='converted') AS converted
            FROM {$pfx}tc_call_log WHERE DATE(called_at) BETWEEN '$from' AND '$to'")->row();
        return $r ? (array)$r : ['total_leads'=>0,'interested'=>0,'appointments'=>0,'converted'=>0];
    }
    public function get_status_breakdown($agent_id,$from,$to) {
        $pfx = $this->pfx;
        return $this->db->query("SELECT call_status, COUNT(*) AS cnt FROM {$pfx}tc_call_log
            WHERE agent_id=$agent_id AND DATE(called_at) BETWEEN '$from' AND '$to' GROUP BY call_status")->result();
    }

    /* ── CAMPAIGNS & TEMPLATES ── */
    public function get_campaigns() {
        return $this->db->select('c.*,CONCAT(s.firstname," ",s.lastname) AS created_by_name,COUNT(t.id) AS template_count')
            ->from($this->pfx.'tc_campaigns c')->join('tblstaff s','s.staffid=c.created_by','left')
            ->join($this->pfx.'tc_templates t','t.campaign_id=c.id','left')
            ->group_by('c.id')->order_by('c.name')->get()->result();
    }
    public function get_campaign($id)  { return $this->db->get_where($this->pfx.'tc_campaigns',['id'=>$id])->row(); }
    public function save_campaign($d,$id=null) {
        if ($id) { $this->db->where('id',$id)->update($this->pfx.'tc_campaigns',$d); return $id; }
        $this->db->insert($this->pfx.'tc_campaigns',$d); return $this->db->insert_id();
    }
    public function delete_campaign($id) {
        $this->db->where('campaign_id',$id)->delete($this->pfx.'tc_templates');
        $this->db->where('id',$id)->delete($this->pfx.'tc_campaigns');
    }
    public function get_templates($campaign_id) {
        return $this->db->where('campaign_id',$campaign_id)->order_by('sort_order','ASC')->get($this->pfx.'tc_templates')->result();
    }
    public function save_template($d,$id=null) {
        if ($id) { $this->db->where('id',$id)->update($this->pfx.'tc_templates',$d); return $id; }
        $this->db->insert($this->pfx.'tc_templates',$d); return $this->db->insert_id();
    }
    public function delete_template($id) { $this->db->where('id',$id)->delete($this->pfx.'tc_templates'); }

    /* ── EMAIL LOG ── */
    public function get_email_log($lead_id) {
        return $this->db->select('e.*,t.subject AS template_subject,c.name AS campaign_name')
            ->from($this->pfx.'tc_email_log e')
            ->join($this->pfx.'tc_templates t','t.id=e.template_id','left')
            ->join($this->pfx.'tc_campaigns c','c.id=e.campaign_id','left')
            ->where('e.lead_id',$lead_id)->order_by('e.sent_at','DESC')->get()->result();
    }
    public function get_email_stats($from,$to) {
        $r = $this->db->query("SELECT
            COUNT(*) AS total_sent,
            SUM(opened_at IS NOT NULL) AS opened,
            SUM(clicked_at IS NOT NULL) AS clicked,
            SUM(bounced) AS bounced,
            SUM(unsubscribed) AS unsubscribed,
            SUM(replied) AS replied
            FROM {$this->pfx}tc_email_log WHERE DATE(sent_at) BETWEEN '$from' AND '$to'")->row();
        if ($r) {
            $total = (int)$r->total_sent;
            $r->open_rate   = $total > 0 ? ($r->opened / $total) * 100 : 0;
            $r->click_rate  = $total > 0 ? ($r->clicked / $total) * 100 : 0;
            $r->bounce_rate = $total > 0 ? ($r->bounced / $total) * 100 : 0;
            $r->reply_rate  = $total > 0 ? ($r->replied / $total) * 100 : 0;
        }
        return $r;
    }

    /* ── REQUESTS ── */
    public function get_requests() {
        return $this->db->select('r.*,CONCAT(s.firstname," ",s.lastname) AS agent_name')
            ->from($this->pfx.'tc_lead_requests r')->join('tblstaff s','s.staffid=r.agent_id','left')
            ->order_by('r.requested_at','DESC')->get()->result();
    }
    public function save_request($d) { $this->db->insert($this->pfx.'tc_lead_requests',$d); return $this->db->insert_id(); }
    public function update_request($id,$d) { $this->db->where('id',$id)->update($this->pfx.'tc_lead_requests',$d); }

    /* ── WHATSAPP ── */
    public function get_whatsapp_log($lead_id) {
        return $this->db->select('w.*,CONCAT(s.firstname," ",s.lastname) AS agent_name')
            ->from($this->pfx.'tc_whatsapp_log w')->join('tblstaff s','s.staffid=w.agent_id','left')
            ->where('w.lead_id',$lead_id)->order_by('w.sent_at','DESC')->get()->result();
    }
}
