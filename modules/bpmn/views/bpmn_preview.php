
<link rel="stylesheet" type="text/css" id="bootstrap-css" href="<?php echo site_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">

<link rel="stylesheet" type="text/css" id="roboto-css" href="<?php echo site_url('assets/plugins/roboto/roboto.css'); ?>">
<link rel="stylesheet" type="text/css" id="datatables-css" href="<?php echo site_url('assets/plugins/datatables/datatables.min.css'); ?>">
<link rel="stylesheet" type="text/css" id="fontawesome-css" href="<?php echo site_url('assets/plugins/font-awesome/css/font-awesome.min.css'); ?>">
<link rel="stylesheet" type="text/css" id="datetimepicker-css" href="<?php echo site_url('assets/plugins/datetimepicker/jquery.datetimepicker.min.css'); ?>">
<link rel="stylesheet" type="text/css" id="bootstrap-select-css" href="<?php echo site_url('assets/plugins/bootstrap-select/css/bootstrap-select.min.css'); ?>">
<link rel="stylesheet" type="text/css" id="lightbox-css" href="<?php echo site_url('assets/plugins/lightbox/css/lightbox.min.css'); ?>">
<link rel="stylesheet" type="text/css" id="colorpicker-css" href="<?php echo site_url('assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css'); ?>">
<link rel="stylesheet" type="text/css" id="bootstrap-overrides-css" href="<?php echo site_url('assets/css/bs-overides.min.css'); ?>">
<link rel="stylesheet" type="text/css" id="theme-css" href="<?php echo site_url('assets/themes/perfex/css/style.min.css'); ?>">
 <link rel="stylesheet" href="<?php echo base_url();?>modules/bpmn/assets/css/style.css">
<nav class="navbar navbar-default header">
   <script type="text/javascript">
   var admin_url = "<?php echo admin_url();?>";
 </script>
  <style type="text/css">
    .bpmn-view-page {
border: 2px solid #505f7b;
background-color: #f6f6f6;
}
#canvas {
    position: relative;
}
#canvas:before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #fbfbfb00;
    z-index: 99;
}
  </style>
  <script src="<?php echo base_url();?>modules/bpmn/assets/js/mynjquery.js"></script>
<script src="<?php echo base_url();?>modules/bpmn/assets/js/development.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>modules/bpmn/assets/js/style.js"></script>
   <div class="container">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
         <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#theme-navbar-collapse" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
         </button>
         <a href="https://workspace.zonvoir.com/prontoinvoices/" class="navbar-brand logo logo logo-text"><?php echo !empty(get_option('companyname')) ? get_option('companyname') : 'Perfex';?></a>      </div>
      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="theme-navbar-collapse">
         <ul class="nav navbar-nav navbar-right">
                <li class="customers-nav-item-knowledge-base">
                  <a href="<?php echo site_url('knowledge-base'); ?>">
                     Knowledge Base  </a>
               </li>
                 
               <?php if(is_client_logged_in()) { ?>
                 <li class="customers-nav-item-logout">
                        <a href="<?php echo site_url('authentication/logout'); ?>">
                           <?php echo _l('clients_nav_logout'); ?>
                        </a>
                     </li>
               <?php }else{?>
                <li class="customers-nav-item-login">
                        <a href="<?php echo site_url('authentication/login'); ?>">
                           <?php echo _l('clients_nav_login'); ?>
                        </a>
                     </li>

               <?php }  ?>
         </ul>
      </div>
      <!-- /.navbar-collapse -->
   </div>
   <!-- /.container-fluid -->
</nav>
<div id="wrapper">
   <div id="content">
      <div class="container bpmn-view-page">
         <?php $value = (isset($bpmn) ? $bpmn->bpmn_content : ''); ?>
            <textarea  id="bpmn_content" name="bpmn_content"><?php echo $bpmn->bpmn_content; ?></textarea>
           <div class="row">
                            <div class="col-md-12">
                                <div id="canvas"></div>
                            </div>
                        </div>
        </div>
    </div>

    <footer class="navbar-fixed-bottom footer">
        <div class="container">
          <div class="row">
            <div class="col-md-12 text-center">
             <span class="copyright-footer"><?php echo date('Y'); ?> <?php echo _l('clients_copyright', get_option('companyname')); ?></span>
          </div>
        </div>
    </div>
    </footer>
</div>

