<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $groups = get_all_knowledge_base_articles_grouped(false); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-heading">
                  <?php echo _l('reports_choose_kb_group'); ?>
               </div>
               <div class="panel-body">
                  <div class="row">
                     <div class="col-md-3">
                        <select class="selectpicker" name="report-group-change" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <?php foreach($groups as $group){ ?>
                           <option value="<?php echo $group['groupid']; ?>"><?php echo $group['name']; ?></option>
                           <?php } ?>
                        </select>
                     </div>
                     <div class="col-md-3">
                        <select class="selectpicker" name="report_months" id="report_months" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="" <?php if(isset($report_months) && $report_months == ''){echo 'selected';} ?>><?php echo _l('report_sales_months_all_time'); ?></option>
                           <option value="this_month" <?php if(isset($report_months) && $report_months == 'this_month'){echo 'selected';} ?>><?php echo _l('this_month'); ?></option>
                           <option value="1" <?php if(isset($report_months) && $report_months == '1'){echo 'selected';} ?>><?php echo _l('last_month'); ?></option>
                           <option value="this_year" <?php if(isset($report_months) && $report_months == 'this_year'){echo 'selected';} ?>><?php echo _l('this_year'); ?></option>
                           <option value="last_year" <?php if(isset($report_months) && $report_months == 'last_year'){echo 'selected';} ?>><?php echo _l('last_year'); ?></option>
                           <option value="3" <?php if(isset($report_months) && $report_months == '3'){echo 'selected';} ?> data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                           <option value="6" <?php if(isset($report_months) && $report_months == '6'){echo 'selected';} ?> data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                           <option value="12" <?php if(isset($report_months) && $report_months == '12'){echo 'selected';} ?> data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                           <option value="custom" <?php if(isset($report_months) && $report_months == 'custom'){echo 'selected';} ?>><?php echo _l('period_datepicker'); ?></option>
                        </select>
                     </div>
                  </div>
                  <div class="row date-range" <?php if(!isset($report_months) || $report_months != 'custom'){echo 'style="display:none;"';} ?>>
                     <div class="col-md-6">
                        <div class="form-group mtop15">
                           <label for="report_from"><?php echo _l('report_sales_from_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" id="report_from" name="report_from" value="<?php echo isset($report_from) ? $report_from : ''; ?>">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group mtop15">
                           <label for="report_to"><?php echo _l('report_sales_to_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" id="report_to" name="report_to" value="<?php echo isset($report_to) ? $report_to : ''; ?>">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-12">
            <div class="row">
               <?php foreach($groups as $group){ ?>
               <div class="col-md-12 group-report hide" id="group_<?php echo $group['groupid']; ?>">
                  <div class="panel_s">
                     <div class="panel-heading">
                        <?php echo $group['name']; ?>
                     </div>
                     <div class="panel-body">
                        <?php foreach($group['articles'] as $article) {
                           $total_answers = total_rows(db_prefix().'knowedge_base_article_feedback',array('articleid'=>$article['articleid']));
                           $total_yes_answers = total_rows(db_prefix().'knowedge_base_article_feedback',array('articleid'=>$article['articleid'],'answer'=>1));
                           $total_no_answers = total_rows(db_prefix().'knowedge_base_article_feedback',array('articleid'=>$article['articleid'],'answer'=>0));
                           $percent_yes = 0;
                           $percent_no = 0;
                           if($total_yes_answers > 0){
                            $percent_yes = number_format(($total_yes_answers * 100) / $total_answers,2);
                           }
                           if($total_no_answers > 0){
                            $percent_no = number_format(($total_no_answers * 100) / $total_answers,2);
                           }
                           ?>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="row">
                                 <div class="col-md-8">
                                    <span class="bold">
                                    <?php if($article['staff_article'] == 1){ ?>
                                    <span class="label label-default mright5 inline-block mbot10"><?php echo _l('internal_article'); ?></span>
                                    <?php } ?>
                                    <?php echo $article['subject']; ?></span>
                                    (<?php echo _l('kb_report_total_answers'); ?>: <?php echo $total_answers; ?>)
                                 </div>
                                 <?php if($total_yes_answers > 0){ ?>
                                 <div class="col-md-4 text-right">
                                    <?php echo _l('report_kb_yes'); ?>: <?php echo $total_yes_answers; ?>
                                 </div>
                                 <?php } ?>
                              </div>
                           </div>
                           <?php if($total_no_answers > 0 || $total_yes_answers > 0){ ?>
                           <div class="col-md-12 progress-bars-report-articles">
                              <div class="progress">
                                 <div class="progress-bar progress-bar-info progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_yes; ?>">
                                    0%
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-md-12 text-right">
                                    <?php echo _l('report_kb_no'); ?>: <?php echo $total_no_answers; ?>
                                 </div>
                              </div>
                              <div class="progress">
                                 <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_no; ?>">
                                    0%
                                 </div>
                              </div>
                           </div>
                           <?php } else { ?>
                           <div class="col-md-12">
                              <p class="no-margin text-info"><?php echo _l('report_kb_no_votes'); ?></p>
                           </div>
                           <?php } ?>
                        </div>
                        <hr />
                        <?php } ?>
                     </div>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   $(function(){
       var groupid = $('select[name="report-group-change"]').val();
       $('#group_'+groupid).removeClass('hide');

       // Used for knowledge base reports
       $('select[name="report-group-change"]').on('change', function() {
           var groupid = $(this).val();
           $('.progress .progress-bar').each(function() {
               $(this).css('width', 0 + '%');
               $(this).text(0 + '%');
           });

           setTimeout(function() {
               $('.group-report').addClass('hide');
               $('#group_' + groupid).removeClass('hide');
           }, 200);

           init_progress_bars();

           // Submit form when group changes
           applyFilters();
       });

       // Show/hide date range inputs based on report_months selection
       $('#report_months').on('change', function() {
           if ($(this).val() === 'custom') {
               $('.date-range').show();
           } else {
               $('.date-range').hide();
               // Submit form when date filter changes
               applyFilters();
           }
       });

       // Handle date range inputs change
       $('#report_from, #report_to').on('change', function() {
           applyFilters();
       });

       // Function to apply filters
       function applyFilters() {
           var groupid = $('select[name="report-group-change"]').val();
           var report_months = $('#report_months').val();
           var report_from = $('#report_from').val();
           var report_to = $('#report_to').val();

           // Redirect with filter parameters
           var url = admin_url + 'reports/knowledge_base_articles?';
           url += 'group=' + groupid;

           if (report_months) {
               url += '&report_months=' + report_months;

               if (report_months === 'custom') {
                   if (report_from) {
                       url += '&report_from=' + report_from;
                   }
                   if (report_to) {
                       url += '&report_to=' + report_to;
                   }
               }
           }

           window.location.href = url;
       }
   })
</script>
</body>
</html>
