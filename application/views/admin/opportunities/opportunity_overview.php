<div class="row">
    <div class="col-md-6 border-right opportunity-overview-left">
        <div class="row">
            <div class="col-md-12">
                <p class="opportunity-info bold font-size-14">
                    <?php echo _l('overview'); ?>
                </p>
            </div>
            <div class="col-md-7">
                <table class="table no-margin opportunity-overview-table">
                    <tbody>
                    <tr class="opportunity-overview-customer">
                        <td class="bold"><?php echo _l('opportunity_for_' . $opportunity->rel_type); ?></td>
                        <td>
                            <?php if ($opportunity->rel_type == 'lead') { ?>
                                <a href="<?php echo admin_url(); ?>leads/index/<?php echo $opportunity->rel_id; ?>"
                                   onclick="init_lead(<?php echo $opportunity->rel_id; ?>);return false;"><?php echo $opportunity->rel_to; ?></a>
                            <?php } else { ?>
                                <a href="<?php echo admin_url(); ?>clients/client/<?php echo $opportunity->rel_id; ?>"><?php echo $opportunity->rel_to; ?></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php if (has_permission('opportunities', '', 'create') || has_permission('opportunities', '', 'edit')){ ?>
                    <tr class="opportunity-overview-billing">
                        <td class="bold"><?php echo _l('opportunity_billing_type'); ?></td>
                        <td>
                            <?php
                            if ($opportunity->billing_type == 1) {
                                $type_name = 'opportunity_billing_type_fixed_cost';
                            } else if ($opportunity->billing_type == 2) {
                                $type_name = 'opportunity_billing_type_opportunity_hours';
                            } else {
                                $type_name = 'opportunity_billing_type_opportunity_task_hours';
                            }
                            echo _l($type_name);
                            ?>
                        </td>
                        <?php if ($opportunity->billing_type == 1 || $opportunity->billing_type == 2) {
                            echo '<tr>';
                            if ($opportunity->billing_type == 1) {
                                echo '<td class="bold">' . _l('opportunity_total_cost') . '</td>';
                                echo '<td>' . format_money($opportunity->opportunity_cost, $currency->symbol) . '</td>';
                            } else {
                                echo '<td class="bold">' . _l('opportunity_rate_per_hour') . '</td>';
                                echo '<td>' . format_money($opportunity->opportunity_rate_per_hour, $currency->symbol) . '</td>';
                            }
                            echo '<tr>';
                        }
                        }
                        ?>
                    <tr class="opportunity-overview-status">
                        <td class="bold"><?php echo _l('opportunity_status'); ?></td>
                        <td><?php echo $opportunity_status['name']; ?></td>
                    </tr>
                    <tr class="opportunity-overview-date-created">
                        <td class="bold"><?php echo _l('opportunity_datecreated'); ?></td>
                        <td><?php echo _d($opportunity->opportunity_created); ?></td>
                    </tr>
                    <tr class="opportunity-overview-start-date">
                        <td class="bold"><?php echo _l('opportunity_start_date'); ?></td>
                        <td><?php echo _d($opportunity->start_date); ?></td>
                    </tr>
                    <?php if ($opportunity->deadline) { ?>
                        <tr class="opportunity-overview-deadline">
                            <td class="bold"><?php echo _l('opportunity_deadline'); ?></td>
                            <td><?php echo _d($opportunity->deadline); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($opportunity->date_finished) { ?>
                        <tr class="opportunity-overview-date-finished">
                            <td class="bold"><?php echo _l('opportunity_completed_date'); ?></td>
                            <td class="text-success"><?php echo _dt($opportunity->date_finished); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($opportunity->estimated_hours && $opportunity->estimated_hours != '0') { ?>
                        <tr class="opportunity-overview-estimated-hours">
                            <td class="bold<?php if (hours_to_seconds_format($opportunity->estimated_hours) < (int)$opportunity_total_logged_time) {
                                echo ' text-warning';
                            } ?>"><?php echo _l('estimated_hours'); ?></td>
                            <td><?php echo str_replace('.', ':', $opportunity->estimated_hours); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (has_permission('opportunities', '', 'create')) { ?>
                        <tr class="opportunity-overview-total-logged-hours">
                            <td class="bold"><?php echo _l('opportunity_overview_total_logged_hours'); ?></td>
                            <td><?php echo seconds_to_time_format($opportunity_total_logged_time); ?></td>
                        </tr>
                    <?php } ?>
                    <?php $custom_fields = get_custom_fields('opportunities');
                    if (count($custom_fields) > 0) { ?>
                        <?php foreach ($custom_fields as $field) { ?>
                            <?php $value = get_custom_field_value($opportunity->id, $field['id'], 'opportunities');
                            if ($value == '') {
                                continue;
                            } ?>
                            <tr>
                                <td class="bold"><?php echo ucfirst($field['name']); ?></td>
                                <td><?php echo $value; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-5 text-center opportunity-percent-col mtop10">
                <p class="bold"><?php echo _l('opportunity') . ' ' . _l('opportunity_progress'); ?></p>
                <div class="opportunity-progress relative mtop15" data-value="<?php echo $percent_circle; ?>"
                     data-size="150" data-thickness="22" data-reverse="true">
                    <strong class="opportunity-percent"></strong>
                </div>
            </div>
        </div>
        <?php $tags = get_tags_in($opportunity->id, 'opportunity'); ?>
        <?php if (count($tags) > 0) { ?>
            <div class="clearfix"></div>
            <div class="tags-read-only-custom opportunity-overview-tags">
                <hr class="hr-panel-heading opportunity-area-separation hr-10"/>
                <?php echo '<p class="font-size-14"><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                <input type="text" class="tagsinput read-only" id="tags" name="tags"
                       value="<?php echo prep_tags_input($tags); ?>" data-role="tagsinput">
            </div>
            <div class="clearfix"></div>
        <?php } ?>
        <div class="tc-content opportunity-overview-description">
            <hr class="hr-panel-heading opportunity-area-separation"/>
            <p class="bold font-size-14 opportunity-info"><?php echo _l('opportunity_description'); ?></p>
            <?php if (empty($opportunity->description)) {
                echo '<p class="text-muted no-mbot mtop15">' . _l('no_description_opportunity') . '</p>';
            }
            echo check_for_links($opportunity->description); ?>
        </div>
        <div class="team-members opportunity-overview-team-members">
            <hr class="hr-panel-heading opportunity-area-separation"/>
            <?php if (has_permission('opportunities', '', 'edit') || has_permission('opportunities', '', 'create')) { ?>
                <div class="inline-block pull-right mright10 opportunity-member-settings" data-toggle="tooltip"
                     data-title="<?php echo _l('add_edit_members'); ?>">
                    <a href="#" data-toggle="modal" class="pull-right" data-target="#add-edit-members"><i
                            class="fa fa-cog"></i></a>
                </div>
            <?php } ?>
            <p class="bold font-size-14 opportunity-info">
                <?php echo _l('opportunity_members'); ?>
            </p>
            <div class="clearfix"></div>
            <?php
            if (count($members) == 0) {
                echo '<p class="text-muted mtop10 no-mbot">' . _l('no_opportunity_members') . '</p>';
            }
            foreach ($members as $member) { ?>
                <div class="media">
                    <div class="media-left">
                        <a href="<?php echo admin_url('profile/' . $member["staff_id"]); ?>">
                            <?php echo staff_profile_image($member['staff_id'], array('staff-profile-image-small', 'media-object')); ?>
                        </a>
                    </div>
                    <div class="media-body">
                        <?php if (has_permission('opportunities', '', 'edit') || has_permission('opportunities', '', 'create')) { ?>
                            <a href="<?php echo admin_url('opportunities/remove_team_member/' . $opportunity->id . '/' . $member['staff_id']); ?>"
                               class="pull-right text-danger _delete"><i class="fa fa fa-times"></i></a>
                        <?php } ?>
                        <h5 class="media-heading mtop5"><a
                                href="<?php echo admin_url('profile/' . $member["staff_id"]); ?>"><?php echo get_staff_full_name($member['staff_id']); ?></a>
                            <?php if (has_permission('opportunities', '', 'create') || $member['staff_id'] == get_staff_user_id()) { ?>
                                <br/>
                                <small
                                    class="text-muted"><?php echo _l('total_logged_hours_by_staff') . ': ' . seconds_to_time_format($member['total_logged_time']); ?></small>
                            <?php } ?>
                        </h5>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="col-md-6 opportunity-overview-right">
        <div class="row">
            <div class="col-md-<?php echo($opportunity->deadline ? 6 : 12); ?> opportunity-progress-bars">
                <div class="row">
                    <div class="opportunity-overview-open-tasks">
                        <div class="col-md-9">
                            <p class="text-uppercase bold text-dark font-medium">
                                <?php echo $tasks_not_completed; ?>
                                / <?php echo $total_tasks; ?> <?php echo _l('opportunity_open_tasks'); ?>
                            </p>
                            <p class="text-muted bold"><?php echo $tasks_not_completed_progress; ?>%</p>
                        </div>
                        <div class="col-md-3 text-right">
                            <i class="fa fa-check-circle<?php if ($tasks_not_completed_progress >= 100) {
                                echo ' text-success';
                            } ?>" aria-hidden="true"></i>
                        </div>
                        <div class="col-md-12 mtop5">
                            <div class="progress no-margin progress-bar-mini">
                                <div class="progress-bar progress-bar-success no-percent-text not-dynamic"
                                     role="progressbar" aria-valuenow="<?php echo $tasks_not_completed_progress; ?>"
                                     aria-valuemin="0" aria-valuemax="100" style="width: 0%"
                                     data-percent="<?php echo $tasks_not_completed_progress; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($opportunity->deadline) { ?>
                <div class="col-md-6 opportunity-progress-bars opportunity-overview-days-left">
                    <div class="row">
                        <div class="col-md-9">
                            <p class="text-uppercase bold text-dark font-medium">
                                <?php echo $opportunity_days_left; ?>
                                / <?php echo $opportunity_total_days; ?> <?php echo _l('opportunity_days_left'); ?>
                            </p>
                            <p class="text-muted bold"><?php echo $opportunity_time_left_percent; ?>%</p>
                        </div>
                        <div class="col-md-3 text-right">
                            <i class="fa fa-calendar-check-o<?php if ($opportunity_time_left_percent >= 100) {
                                echo ' text-success';
                            } ?>" aria-hidden="true"></i>
                        </div>
                        <div class="col-md-12 mtop5">
                            <div class="progress no-margin progress-bar-mini">
                                <div class="progress-bar<?php if ($opportunity_time_left_percent == 0) {
                                    echo ' progress-bar-warning ';
                                } else {
                                    echo ' progress-bar-success ';
                                } ?>no-percent-text not-dynamic" role="progressbar"
                                     aria-valuenow="<?php echo $opportunity_time_left_percent; ?>" aria-valuemin="0"
                                     aria-valuemax="100" style="width: 0%"
                                     data-percent="<?php echo $opportunity_time_left_percent; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <hr class="hr-panel-heading"/>

        <?php if (has_permission('opportunities', '', 'create')) { ?>
            <div class="row">
                <?php if ($opportunity->billing_type == 3 || $opportunity->billing_type == 2) { ?>
                    <div class="col-md-12 opportunity-overview-logged-hours-finance">
                        <div class="col-md-3">
                            <?php
                            $data = $this->opportunities_model->total_logged_time_by_billing_type($opportunity->id);
                            ?>
                            <p class="text-uppercase text-muted"><?php echo _l('opportunity_overview_logged_hours'); ?>
                                <span class="bold"><?php echo $data['logged_time']; ?></span></p>
                            <p class="bold font-medium"><?php echo format_money($data['total_money'], $currency->symbol); ?></p>
                        </div>
                        <div class="col-md-3">
                            <?php
                            $data = $this->opportunities_model->data_billable_time($opportunity->id);
                            ?>
                            <p class="text-uppercase text-info"><?php echo _l('opportunity_overview_billable_hours'); ?>
                                <span class="bold"><?php echo $data['logged_time'] ?></span></p>
                            <p class="bold font-medium"><?php echo format_money($data['total_money'], $currency->symbol); ?></p>
                        </div>
                        <div class="col-md-3">
                            <?php
                            $data = $this->opportunities_model->data_billed_time($opportunity->id);
                            ?>
                            <p class="text-uppercase text-success"><?php echo _l('opportunity_overview_billed_hours'); ?>
                                <span class="bold"><?php echo $data['logged_time']; ?></span></p>
                            <p class="bold font-medium"><?php echo format_money($data['total_money'], $currency->symbol); ?></p>
                        </div>
                        <div class="col-md-3">
                            <?php
                            $data = $this->opportunities_model->data_unbilled_time($opportunity->id);
                            ?>
                            <p class="text-uppercase text-danger"><?php echo _l('opportunity_overview_unbilled_hours'); ?>
                                <span class="bold"><?php echo $data['logged_time']; ?></span></p>
                            <p class="bold font-medium"><?php echo format_money($data['total_money'], $currency->symbol); ?></p>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                    </div>
                <?php } ?>
            </div>
            <div class="row">
                <div class="col-md-12 opportunity-overview-expenses-finance">
                    <div class="col-md-3">
                        <p class="text-uppercase text-muted"><?php echo _l('opportunity_overview_expenses'); ?></p>
                        <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses', array('where' => array('opportunity_id' => $opportunity->id), 'field' => 'amount')), $currency->symbol); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-uppercase text-info"><?php echo _l('opportunity_overview_expenses_billable'); ?></p>
                        <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses', array('where' => array('opportunity_id' => $opportunity->id, 'billable' => 1), 'field' => 'amount')), $currency->symbol); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-uppercase text-success"><?php echo _l('opportunity_overview_expenses_billed'); ?></p>
                        <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses', array('where' => array('opportunity_id' => $opportunity->id, 'invoiceid !=' => 'NULL', 'billable' => 1), 'field' => 'amount')), $currency->symbol); ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="text-uppercase text-danger"><?php echo _l('opportunity_overview_expenses_unbilled'); ?></p>
                        <p class="bold font-medium"><?php echo format_money(sum_from_table('tblexpenses', array('where' => array('opportunity_id' => $opportunity->id, 'invoiceid IS NULL', 'billable' => 1), 'field' => 'amount')), $currency->symbol); ?></p>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="opportunity-overview-timesheets-chart">
            <hr class="hr-panel-heading"/>
            <div class="dropdown pull-right">
                <a href="#" class="dropdown-toggle" type="button" id="dropdownMenuopportunityLoggedTime"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <?php if (!$this->input->get('overview_chart')) {
                        echo _l('this_week');
                    } else {
                        echo _l($this->input->get('overview_chart'));
                    }
                    ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuopportunityLoggedTime">
                    <li>
                        <a href="<?php echo admin_url('opportunities/view/' . $opportunity->id . '?group=opportunity_overview&overview_chart=this_week'); ?>"><?php echo _l('this_week'); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('opportunities/view/' . $opportunity->id . '?group=opportunity_overview&overview_chart=last_week'); ?>"><?php echo _l('last_week'); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('opportunities/view/' . $opportunity->id . '?group=opportunity_overview&overview_chart=this_month'); ?>"><?php echo _l('this_month'); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo admin_url('opportunities/view/' . $opportunity->id . '?group=opportunity_overview&overview_chart=last_month'); ?>"><?php echo _l('last_month'); ?></a>
                    </li>
                </ul>
            </div>
            <div class="clearfix"></div>
            <canvas id="timesheetsChart" style="max-height:300px;" width="300" height="300"></canvas>
        </div>

    </div>
</div>
<div class="modal fade" id="add-edit-members" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('opportunities/add_edit_members/' . $opportunity->id)); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('opportunity_members'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $selected = array();
                foreach ($members as $member) {
                    array_push($selected, $member['staff_id']);
                }
                echo render_select('opportunity_members[]', $staff, array('staffid', array('firstname', 'lastname')), 'opportunity_members', $selected, array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" autocomplete="off"
                        data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php if (isset($opportunity_overview_chart)) { ?>
    <script>
        var opportunity_overview_chart = <?php echo json_encode($opportunity_overview_chart); ?>;
    </script>
<?php } ?>
