<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
   <div class="col-md-12">
      <div class="form-group psp-select">
         <label for="settings[cfg_decimal_places]" class="control-label">Decimal Places</label>
         <select name="settings[cfg_decimal_places]" id="settings[cfg_decimal_places]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <option value="2" <?php if(get_option('cfg_decimal_places') == '2'){echo 'selected';} ?>>2</option>
            <option value="3" <?php if(get_option('cfg_decimal_places') == '3'){echo 'selected';} ?>>3</option>
            <option value="4" <?php if(get_option('cfg_decimal_places') == '4'){echo 'selected';} ?>>4</option>
         </select>
      </div>
   </div>
</div>
