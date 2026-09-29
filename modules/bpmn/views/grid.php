<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI = & get_instance();

$start = intval($CI->input->post('start'));
$length = intval($CI->input->post('length'));
$draw = intval($CI->input->post('draw'));

$CI->db->query("SET sql_mode = ''");

$aColumns = [
    'title',
    db_prefix() . 'bpmn.description',
    'staffid',
    db_prefix() . 'bpmn_groups.name',
];


$sIndexColumn = 'id';
$sTable       = db_prefix() . 'bpmn';
$join = [
    'LEFT JOIN ' . db_prefix() . 'bpmn_groups ON ' . db_prefix() . 'bpmn_groups.id = ' . db_prefix() . 'bpmn.bpmn_group_id',
];
$where        = [];
$filter = [];

$join = hooks()->apply_filters('bpmn_grid_sql_join', $join);

$result = get_cl_list_bpmn_query($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'bpmn.id', db_prefix() . 'bpmn.bpmn_content']);


$output  = $result['output'];
$rResult = $result['rResult'];

$prevPage = (($draw - 1) < 0)?0:($draw-1);
$nextPage = $draw + 1;
$nxtStart = ($start +1 ) * $length; //($draw <= 2)?$length:($draw - 1) * $length;
$prevStart = ($start -1 ) * $length; //(($draw - 1) >= 0)?(($draw - 1) * $length):0;
$this->load->library('pagination');

$config['base_url'] = '';
$config['total_rows'] = $output['iTotalDisplayRecords'];
$config['per_page'] = $length;
$config['use_page_numbers'] = TRUE;
$config['full_tag_open'] = "<ul class='pagination pagination-sm pull-right' style='position:relative; top:-25px;'>";
$config['full_tag_close'] ="</ul>";
$config['num_tag_open'] = '<li>';
$config['num_tag_close'] = '</li>';
$config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='javascript:;'>";
$config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
$config['next_tag_open'] = "<li>";
$config['next_tagl_close'] = "</li>";
$config['prev_tag_open'] = "<li>";
$config['prev_tagl_close'] = "</li>";
$config['first_tag_open'] = "<li>";
$config['first_tagl_close'] = "</li>";
$config['last_tag_open'] = "<li>";
$config['last_tagl_close'] = "</li>";
$config['attributes'] = array('class' => 'paginate');
$config["uri_segment"] = 4;

$this->pagination->initialize($config);

$CI->load->model('staff_model');
$CI->load->model('bpmn_model');
?>
<div class="row">
    <input type="hidden" id="mainurl" value="<?php echo base_url();?>">
    <div id="cl-grid-view" class="container-fluid">
<?php
if($output['iTotalDisplayRecords'] > 0){
foreach ($rResult as $aRow) {
    $oStaff = $CI->staff_model->get($aRow['staffid']);
?>
    <div class="col-md-3">
        <div class="cardbox text-center">
            <textarea class="gridtextarea" id="m_map_<?php echo $aRow['id'];?>"><?php echo $aRow['bpmn_content'];?></textarea>
            <div class="map_grid" id="map_<?php echo $aRow['id'];?>">
                <iframe src="" class="iframe_style" title="Iframe Example" id="ifrm_map_<?php echo $aRow['id'];?>"></iframe>
            </div>
            <h4><a href="<?php echo admin_url('bpmn/preview/' . $aRow['id']);?>"><?php echo $aRow['title'];?></a></h4>
            <p><?php echo $aRow[db_prefix() . 'bpmn_groups.name']; ?></p>
            <?php if($oStaff) {?>
            <p>Created by: <a href="<?php echo admin_url('profile/'.$oStaff->staffid);?>"><?php echo $oStaff->firstname.' '. $oStaff->lastname; ?></a></p>
        <?php } ?>
        </div>
    </div>
<?php } }else{?>
    <div class="col-md-12">
        <div class="cardbox text-center dataTables_empty">
            <p>No entries found</p>
        </div>
    </div>
<?php } ?>
</div></div>
<div class="row">
    <divid='pagination'>
        <?php echo $this->pagination->create_links(); ?>
    </div>
</div>
 <link rel="stylesheet" href="<?php echo base_url();?>modules/bpmn/assets/css/style.css">
<link href="<?php echo module_dir_url('bpmn', 'assets/css/cl.css'); ?>" rel="stylesheet">