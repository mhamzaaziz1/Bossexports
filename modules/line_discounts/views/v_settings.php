<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head();?>

<?php $has_tab = false; ?>

<div id="wrapper" >

    <div class="content">

        <div class="row">

            <h4 class="tw-font-semibold tw-mt-0 tw-text-neutral-800">

                <?php echo _l('line_discount_setting'); ?>

            </h4>

            <div class="col-md-12">

                <div class="panel_s">

                    <div class="panel-body">

                        <div class="form-group">


                            <div class="checkbox checkbox-primary">

                                <?php $value = line_discount_get_option_value( 'hide_column_without_discount' , 0 ) ?>

                                <input type="checkbox" <?php echo $value == 1 ? 'checked' : '' ?> class="line_discount_checkbox" value="1" id="hide_column_without_discount" name="hide_column_without_discount">

                                <label for="hide_column_without_discount"> <?php echo _l('line_discount_hide_column_without_discount')?> </label>

                            </div>

                            <hr />

                            <div class="checkbox checkbox-primary">

                                <?php $value = line_discount_get_option_value( 'enable_negative_discount' , 0 ) ?>

                                <input type="checkbox" <?php echo $value == 1 ? 'checked' : '' ?> class="line_discount_checkbox" value="1" id="enable_negative_discount" name="enable_negative_discount">

                                <label for="enable_negative_discount"> <?php echo _l('line_discount_enable_negative_discount')?> </label>

                            </div>

                            <hr />



                        </div>

                    </div>

                </div>


            </div>

        </div>



    </div>

</div>




<?php init_tail(); ?>


<script>


    (function($) {
        "use strict";

        $(document).ready(function (){


            $('.line_discount_checkbox').change(function ( e ){

                var setting_name    = $(this).attr('id');
                var setting_val     = $(this).prop('checked') ? 1 : 0 ;

                if( setting_name )
                {

                    $.post( admin_url+'line_discounts/setting/save_setting' , {'name' : setting_name , 'value' : setting_val} ).done(function(response) {

                        response = JSON.parse(response);

                        alert_float('success', response.message);

                    });

                }


            })



        });


    })(jQuery);


</script>