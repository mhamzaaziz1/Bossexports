<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if ( ! empty($website_maintenance)) { ?>
    <div class="col-md-12">
        <h4 class="tw-font-semibold email-template-heading">
			<?= _l('wmm_email_templates'); ?>
			<?php
			if (staff_can('edit', 'email_templates')) { ?>
                <a href="<?= admin_url('emails/disable_by_type/website_maintenance'); ?>"
                   class="pull-right mleft5 mright25"><small><?= _l('disable_all'); ?></small></a>
                <a href="<?= admin_url('emails/enable_by_type/website_maintenance'); ?>"
                   class="pull-right"><small><?= _l('enable_all'); ?></small></a>
			<?php
			} ?>
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>
                        <span class="tw-font-semibold">
                            <?= _l('email_templates_table_heading_name'); ?>
                        </span>
                    </th>
                </tr>
                </thead>
                <tbody>
				<?php
				foreach ($website_maintenance as $template) { ?>
                    <tr>
                        <td class="<?= $template['active'] == 0 ? 'tw-line-through' : ''; ?>">
                            <a href="<?= admin_url('emails/email_template/'.$template['emailtemplateid']); ?>">
								<?= e($template['name']); ?>
                            </a>
							<?php
							if (ENVIRONMENT !== 'production') { ?>
                                <br/><small><?= e($template['slug']); ?></small>
							<?php
							} ?>
							<?php
							if (staff_can('edit', 'email_templates')) { ?>
                                <a href="<?= admin_url('emails/'.($template['active'] == '1' ? 'disable/' : 'enable/').$template['emailtemplateid']); ?>"
                                   class="pull-right">
                                    <small><?= _l($template['active'] == 1 ? 'disable' : 'enable'); ?></small>
                                </a>
							<?php
							} ?>
                        </td>
                    </tr>
				<?php
				} ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="clearfix"></div>
<?php
} ?>
