<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="weight" class="control-label"><?php echo _l('weight'); ?></label>
            <div class="input-group">
                <input type="number" step="any" name="weight" class="form-control" value="<?php echo (isset($item) ? $item->weight : ''); ?>">
                <div class="input-group-addon">kg</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="volume" class="control-label"><?php echo _l('volume'); ?></label>
            <div class="input-group">
                <input type="number" step="any" name="volume" class="form-control" value="<?php echo (isset($item) ? $item->volume : ''); ?>">
                <div class="input-group-addon">m³</div>
            </div>
        </div>
    </div>
</div>
