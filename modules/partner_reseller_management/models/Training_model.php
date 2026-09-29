<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Training_model extends App_Model
{
    private $courses;
    private $progress;
    private $certs;

    public function __construct()
    {
        parent::__construct();
        $this->courses  = db_prefix() . 'prm_training_courses';
        $this->progress = db_prefix() . 'prm_training_progress';
        $this->certs    = db_prefix() . 'prm_certifications';
    }

    public function list_courses($active_only = true)
    {
        if ($active_only) $this->db->where('active', 1);
        return $this->db->order_by('id', 'desc')->get($this->courses)->result();
    }

    public function get_course($id)
    {
        return $this->db->where('id', $id)->get($this->courses)->row();
    }

    public function add_course(array $data)
    {
        $data['slug']       = $data['slug'] ?? url_title($data['title'] ?? '', 'dash', true);
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->courses, $data);
        return $this->db->insert_id();
    }

    public function start_course($partner_id, $course_id, $contact_id = null)
    {
        $row = $this->db->where(['partner_id'=>$partner_id, 'course_id'=>$course_id])->get($this->progress)->row();
        if ($row) return $row->id;
        $this->db->insert($this->progress, [
            'partner_id'  => $partner_id,
            'contact_id'  => $contact_id,
            'course_id'   => $course_id,
            'progress_pct'=> 0,
            'started_at'  => date('Y-m-d H:i:s'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function update_progress($partner_id, $course_id, $pct, $score = null)
    {
        $course = $this->get_course($course_id);
        $passed = $score !== null && $course && $score >= (int) $course->pass_score ? 1 : 0;
        $this->db->where(['partner_id'=>$partner_id,'course_id'=>$course_id])->update($this->progress, [
            'progress_pct' => $pct,
            'score'        => $score,
            'passed'       => $passed,
            'completed_at' => $pct >= 100 ? date('Y-m-d H:i:s') : null,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($passed) {
            $this->issue_certificate($partner_id, $course);
        }
    }

    public function issue_certificate($partner_id, $course)
    {
        if (!$course) return null;
        $code = strtoupper(bin2hex(random_bytes(6)));
        $this->db->insert($this->certs, [
            'partner_id'        => $partner_id,
            'cert_name'         => $course->title,
            'cert_code'         => 'CERT-' . $course->slug . '-' . $partner_id,
            'issued_at'         => date('Y-m-d'),
            'expires_at'        => date('Y-m-d', strtotime('+2 years')),
            'verification_code' => $code,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $cert_id = $this->db->insert_id();
        hooks()->do_action('prm_certification_issued', $partner_id, $cert_id);
        return $cert_id;
    }

    public function partner_progress($partner_id)
    {
        return $this->db->select(db_prefix().'prm_training_progress.*, c.title, c.difficulty, c.duration_min')
            ->from($this->progress)
            ->join($this->courses . ' c', 'c.id = ' . $this->progress . '.course_id', 'left')
            ->where(db_prefix().'prm_training_progress.partner_id', $partner_id)
            ->order_by(db_prefix().'prm_training_progress.id', 'desc')
            ->get()->result();
    }

    public function partner_certifications($partner_id)
    {
        return $this->db->where('partner_id', $partner_id)->order_by('issued_at', 'desc')->get($this->certs)->result();
    }
}
