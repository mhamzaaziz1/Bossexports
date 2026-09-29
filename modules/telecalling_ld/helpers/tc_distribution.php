<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
 * tc_distribution.php — Lead assignment & utility helpers
 * ─────────────────────────────────────────────────────────
 * tc_bulk_assign($agent, $n, $group)   Assign N unassigned leads to agent
 * tc_range_assign($agent, $from, $to)  Assign leads by ID range
 * tc_group_distribute($group, $n)      Spread leads across group members
 * tc_auto_assign_next($agent)          Pull next lead from rotation queue
 * tc_setting($key)                     Read a module setting
 * tc_save_setting($key, $val)          Write a module setting
 * tc_is_manager()                      Is current staff a group leader?
 * tc_period_range($period)             Return [from, to] date strings
 * tc_target_status($agent, $type, $p)  Current vs target stats
 * tc_check_target_achieved($agent)     Fire notification if target hit
 * tc_avatar_color($staff_id)           Consistent hex color for avatars
 */

function tc_bulk_assign($agent_id, $count, $group_id = null)
{
    $CI  =& get_instance();
    $pfx = $CI->db->dbprefix;
    $leads = $CI->db->select('l.id')->from('tblleads l')
        ->join($pfx.'tc_assignments a','a.lead_id=l.id','left')
        ->where('a.id IS NULL')->limit((int)$count)->get()->result();
    $n = 0;
    foreach ($leads as $l) {
        $CI->db->replace($pfx.'tc_assignments', [
            'lead_id'    => $l->id, 'agent_id'   => $agent_id,
            'group_id'   => $group_id, 'status'  => 'pending',
            'assigned_by'=> get_staff_user_id(), 'assigned_at'=> date('Y-m-d H:i:s'),
        ]);
        $n++;
    }
    if ($n > 0) add_notification(['description'=>'tc_leads_assigned','touserid'=>$agent_id,
        'link'=>admin_url('telecalling_ld/my_leads'),'additional_data'=>json_encode(['count'=>$n])]);
    return $n;
}

function tc_range_assign($agent_id, $from_id, $to_id, $group_id = null)
{
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    $leads = $CI->db->select('l.id')->from('tblleads l')
        ->join($pfx.'tc_assignments a','a.lead_id=l.id','left')
        ->where('a.id IS NULL')->where("l.id BETWEEN ".(int)$from_id." AND ".(int)$to_id)
        ->get()->result();
    $n = 0;
    foreach ($leads as $l) {
        $CI->db->replace($pfx.'tc_assignments', ['lead_id'=>$l->id,'agent_id'=>$agent_id,
            'group_id'=>$group_id,'assigned_by'=>get_staff_user_id(),'assigned_at'=>date('Y-m-d H:i:s')]);
        $n++;
    }
    return $n;
}

function tc_group_distribute($group_id, $count)
{
    $CI      =& get_instance();
    $members = $CI->db->where('group_id',$group_id)->get($CI->db->dbprefix.'tc_group_members')->result();
    if (!$members) return 0;
    $per = (int)ceil($count / count($members));
    $tot = 0;
    foreach ($members as $m) $tot += tc_bulk_assign($m->agent_id, $per, $group_id);
    return $tot;
}

function tc_auto_assign_next($agent_id)
{
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    if ($CI->db->where(['agent_id'=>$agent_id,'completed'=>0])->count_all_results($pfx.'tc_assignments') > 0) return false;
    $next = $CI->db->order_by('priority','ASC')->order_by('added_at','ASC')->limit(1)->get($pfx.'tc_rotation_queue')->row();
    if (!$next) return false;
    $CI->db->replace($pfx.'tc_assignments',['lead_id'=>$next->lead_id,'agent_id'=>$agent_id,
        'status'=>'pending','assigned_at'=>date('Y-m-d H:i:s')]);
    $CI->db->where('id',$next->id)->delete($pfx.'tc_rotation_queue');
    return true;
}

function tc_setting($key)
{
    $CI  =& get_instance();
    $row = $CI->db->where('skey',$key)->get($CI->db->dbprefix.'tc_settings')->row();
    return $row ? $row->svalue : null;
}

function tc_save_setting($key, $value)
{
    get_instance()->db->replace(get_instance()->db->dbprefix.'tc_settings',['skey'=>$key,'svalue'=>$value]);
}

function tc_is_manager()
{
    if (is_admin()) return false;
    $CI = &get_instance();
    return (bool)$CI->db->where('manager_id',get_staff_user_id())->count_all_results($CI->db->dbprefix.'tc_groups');
}

function tc_period_range($period)
{
    switch($period){
        case 'today': return [date('Y-m-d'),date('Y-m-d')];
        case 'month': return [date('Y-m-01'),date('Y-m-d')];
        default:      return [date('Y-m-d',strtotime('monday this week')),date('Y-m-d')];
    }
}

function tc_target_status($agent_id, $type, $period)
{
    $CI  =& get_instance(); $pfx = $CI->db->dbprefix;
    $t   = $CI->db->where(['agent_id'=>$agent_id,'target_type'=>$type,'period'=>$period])->get($pfx.'tc_targets')->row();
    if (!$t) return null;
    [$from,$to] = tc_period_range($period);
    $map = ['appointments'=>'appointment_scheduled','conversions'=>'converted'];
    $q   = $CI->db->where('agent_id',$agent_id)->where("DATE(called_at) BETWEEN '$from' AND '$to'");
    if (isset($map[$type])) $q->where('call_status',$map[$type]);
    $actual = $q->count_all_results($pfx.'tc_call_log');
    return ['target'=>(int)$t->target_value,'actual'=>$actual,'pct'=>$t->target_value>0?round($actual/$t->target_value*100):0];
}

function tc_check_target_achieved($agent_id)
{
    foreach (['calls','appointments','conversions'] as $type) {
        foreach (['daily','weekly','monthly'] as $period) {
            $s = tc_target_status($agent_id,$type,$period);
            if ($s && $s['actual']>=$s['target'] && $s['target']>0)
                add_notification(['description'=>'tc_target_achieved','touserid'=>$agent_id,
                    'link'=>admin_url('telecalling_ld'),'additional_data'=>json_encode(['type'=>$type,'period'=>$period])]);
        }
    }
}

function tc_avatar_color($id)
{
    $c=['#1F4E79','#0F6E56','#534AB7','#C55A11','#A32D2D','#0F766E','#7C3AED','#B45309'];
    return $c[$id%count($c)];
}
