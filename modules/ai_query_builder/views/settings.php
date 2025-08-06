<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('ai_query_builder_settings'); ?></h4>
                        <hr class="hr-panel-heading" />

                        <?php echo form_open(admin_url('ai_query_builder/settings')); ?>

                        <?php
                        // Default settings if not set
                        if (!isset($settings)) {
                            $settings = new stdClass();
                            $settings->openai_api_key = '';
                            $settings->model = 'gpt-3.5-turbo';
                            $settings->max_rows = 100;
                        }
                        ?>

                        <div class="form-group">
                            <label for="openai_api_key"><?php echo _l('openai_api_key'); ?></label>
                            <input type="password" class="form-control" id="openai_api_key" name="openai_api_key" value="<?php echo set_value('openai_api_key', $settings->openai_api_key); ?>" autocomplete="off">
                            <small class="text-muted"><?php echo _l('openai_api_key_help'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="model"><?php echo _l('openai_model'); ?></label>
                            <select class="form-control" id="model" name="model">
                                <option value="gpt-3.5-turbo" <?php echo $settings->model == 'gpt-3.5-turbo' ? 'selected' : ''; ?>>GPT-3.5 Turbo</option>
                                <option value="gpt-4" <?php echo $settings->model == 'gpt-4' ? 'selected' : ''; ?>>GPT-4</option>
                                <option value="gpt-4-turbo" <?php echo $settings->model == 'gpt-4-turbo' ? 'selected' : ''; ?>>GPT-4 Turbo</option>
                            </select>
                            <small class="text-muted"><?php echo _l('openai_model_help'); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="max_rows"><?php echo _l('max_rows'); ?></label>
                            <input type="number" class="form-control" id="max_rows" name="max_rows" value="<?php echo set_value('max_rows', $settings->max_rows); ?>" min="1" max="1000">
                            <small class="text-muted"><?php echo _l('max_rows_help'); ?></small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
                        </div>

                        <?php echo form_close(); ?>

                        <hr class="hr-panel-heading" />

                        <h4><?php echo _l('usage_instructions'); ?></h4>
                        <div class="well">
                            <p><strong><?php echo _l('how_to_use'); ?></strong></p>
                            <ol>
                                <li><?php echo _l('usage_step_1'); ?></li>
                                <li><?php echo _l('usage_step_2'); ?></li>
                                <li><?php echo _l('usage_step_3'); ?></li>
                                <li><?php echo _l('usage_step_4'); ?></li>
                            </ol>

                            <p><strong><?php echo _l('example_queries'); ?></strong></p>
                            <ul>
                                <li><?php echo _l('example_query_1'); ?></li>
                                <li><?php echo _l('example_query_2'); ?></li>
                                <li><?php echo _l('example_query_3'); ?></li>
                            </ul>

                            <p><strong><?php echo _l('limitations'); ?></strong></p>
                            <ul>
                                <li><?php echo _l('limitation_1'); ?></li>
                                <li><?php echo _l('limitation_2'); ?></li>
                                <li><?php echo _l('limitation_3'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
