<tr class="checklist_tr_<?php echo $checklist->id?>">

    <td>

        <div class="checkbox checkbox-success checklist-checkbo">

            <a href="#" style="margin-right: 20px" onclick="toggle_lead_checklist_content( <?php echo $checklist->id?> , this ); return false;">
                <i class="fa fa-lg fa-arrow-down"></i>
                &nbsp;
            </a>

            <input type="checkbox" class="lead_checklists_box" <?php echo !empty( $checklist->complated_staff_id ) ? ' checked ' : '' ?> onchange="lead_checklist_status_change( <?php echo $checklist->id?> )"  id="lead_checklist_value_<?php echo $checklist->id?>" name="lead_checklist_value_<?php echo $checklist->id?>">

            <label for="lead_checklist_value_<?php echo $checklist->id?>"><?php echo $checklist->checklist_text?></label>

            <div class="checklist_toggle_<?php echo $checklist->id?>" style="display: none">

                <?php if ( !empty( $checklist->added_staff_id ) ) { ?>

                    <br />
                    <small>
                        <?php echo  _l('task_created_by', get_staff_full_name( $checklist->added_staff_id ) ); ?>
                        &nbsp;
                        [ <?php echo _dt( $checklist->added_date ) ?> ]
                    </small>

                <?php } ?>

                <?php if ( !empty( $checklist->assigned_staff_id ) ) { ?>

                    <br />
                    <small>
                        <?php echo  _l('task_checklist_assigned', get_staff_full_name( $checklist->assigned_staff_id ) ); ?>
                        &nbsp;
                        [ <?php echo _dt( $checklist->assigned_date ) ?> ]
                    </small>

                <?php } ?>

                <?php if ( !empty( $checklist->complated_staff_id ) ) { ?>

                    <br />
                    <small>
                        <?php echo  _l('task_checklist_item_completed_by', get_staff_full_name( $checklist->complated_staff_id ) ); ?>
                        &nbsp;
                        [ <?php echo _dt( $checklist->complated_date ) ?> ]
                    </small>

                <?php } ?>

            </div>

        </div>
    </td>

    <td>

        <span class="dropdown" data-title="<?php echo _l('task_checklist_assign'); ?>" data-toggle="tooltip">

            <a href="#" class="tw-text-neutral-500 dropdown-toggle" data-toggle="dropdown"

               aria-haspopup="true" aria-expanded="false" id="checklist-item-<?php echo $list['id']; ?>"

               onclick="return false;">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"

                     stroke="currentColor" class="tw-w-5 tw-h-5">

                    <path stroke-linecap="round" stroke-linejoin="round"

                          d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />

                </svg>

            </a>

            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="checklist-item-<?php echo $checklist->id ?>">

                <?php foreach ($task_staff_members as $_staff) { ?>

                    <li>

                        <a href="#" onclick="lead_checklist_assigned_staff('<?php echo $_staff['staffid'] ; ?>', '<?php echo $checklist->id ?>'); return false;">

                            <?php echo  $_staff['firstname'] . ' ' . $_staff['lastname'] ?>

                        </a>

                    </li>

                <?php } ?>

            </ul>

        </span>



        <?php if ( empty( $checklist->template_id ) ) { ?>

            <a style="cursor: pointer" class="tw-text-neutral-500 checklist_template_<?php echo $checklist->id?>" onclick="lead_checklist_save_template( <?php echo $checklist->id?> )" data-toggle="tooltip" data-title="<?php echo _l('save_as_template'); ?>">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"

                     stroke="currentColor" class="tw-w-5 tw-h-5">

                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5A3.375 3.375 0 006.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0015 2.25h-1.5a2.251 2.251 0 00-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 00-9-9z" />

                </svg>

            </a>

            &nbsp;
        <?php } ?>

        <a style="cursor: pointer" class="tw-text-neutral-500" onclick="lead_checklist_remove( <?php echo $checklist->id?> )"  data-toggle="tooltip" data-title="<?php echo _l('lead_checklist_remove')?>" >

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"

                 stroke="currentColor" class="tw-w-5 tw-h-6">

                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />

            </svg>

        </a>

    </td>

</tr>

<script>
    $(document).ready(function (){
        lead_checklist_recalculate_progress();
    })
</script>
