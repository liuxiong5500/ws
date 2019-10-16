<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
            $rel_type = '';
            $rel_id = '';
            if (isset($opportunity) || ($this->input->get('rel_id') && $this->input->get('rel_type'))) {
                if ($this->input->get('rel_id')) {
                    $rel_id = $this->input->get('rel_id');
                    $rel_type = $this->input->get('rel_type');
                } else {
                    $rel_id = $opportunity->rel_id;
                    $rel_type = $opportunity->rel_type;
                }
            }
            ?>
            <?php echo form_open($this->uri->uri_string(), array('id' => 'opportunity_form')); ?>
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading"/>
                        <?php
                        $disable_type_edit = '';
                        if (isset($opportunity)) {
                            if ($opportunity->billing_type != 1) {
                                if (total_rows('tblstafftasks', array('rel_id' => $opportunity->id, 'rel_type' => 'opportunity', 'billable' => 1, 'billed' => 1)) > 0) {
                                    $disable_type_edit = 'disabled';
                                }
                            }
                        }
                        ?>
                        <?php $value = (isset($opportunity) ? $opportunity->name : ''); ?>
                        <?php echo render_input('name', 'opportunity_name', $value); ?>
                        <div class="form-group select-placeholder">
                            <label for="rel_type"
                                   class="control-label"><?php echo _l('opportunity_related'); ?></label>
                            <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <option
                                    value="lead" <?php if ((isset($opportunity) && $opportunity->rel_type == 'lead') || $this->input->get('rel_type')) {
                                    if ($rel_type == 'lead') {
                                        echo 'selected';
                                    }
                                } ?>><?php echo _l('opportunity_for_lead'); ?></option>
                                <option
                                    value="customer" <?php if ((isset($opportunity) && $opportunity->rel_type == 'customer') || $this->input->get('rel_type')) {
                                    if ($rel_type == 'customer') {
                                        echo 'selected';
                                    }
                                } ?>><?php echo _l('opportunity_for_customer'); ?></option>
                            </select>
                        </div>
                        <div class="form-group select-placeholder<?php if ($rel_id == '') {
                            echo ' hide';
                        } ?> " id="rel_id_wrapper">
                            <label for="rel_id"><span class="rel_id_label"></span></label>
                            <div id="rel_id_select">
                                <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%"
                                        data-live-search="true"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php if ($rel_id != '' && $rel_type != '') {
                                        $rel_data = get_relation_data($rel_type, $rel_id);
                                        $rel_val = get_relation_values($rel_data, $rel_type);
                                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <?php $value = (isset($opportunity) ? $opportunity->rel_to : ''); ?>
                        <?php echo render_input('rel_to', 'opportunity_rel_to', $value); ?>
                        <?php $value = (isset($opportunity) ? $opportunity->amount : ''); ?>
                        <?php echo render_input('amount', 'opportunity_amount', $value, 'number'); ?>
                        <?php $value = (isset($opportunity) ? $opportunity->sales_stage_id : ''); ?>
                        <?php echo render_select('sales_stage_id', $stages, array('id', 'name'), 'opportunity_sales_stage', $value); ?>
                        <?php $value = (isset($opportunity) ? $opportunity->probability : ''); ?>
                        <?php echo render_input('probability', 'opportunity_probability', $value, 'number'); ?>
                        <?php $value = (isset($opportunity) ? $opportunity->nextstep : ''); ?>
                        <?php echo render_input('nextstep', 'opportunity_nextstep', $value); ?>
                        <?php $value = (isset($opportunity) ? $opportunity->type : ''); ?>
                        <?php echo render_select('type', $types, array('id', 'name'), 'opportunity_type', $value); ?>
                        <?php $value = (isset($opportunity) ? $opportunity->source : ''); ?>
                        <?php echo render_select('source', $sources, array('id', 'name'), 'opportunity_source', $value); ?>
                        <?php $value = (isset($opportunity) ? $opportunity->campaign_id : ''); ?>
                        <?php echo render_select('campaign_id', $campaigns, array('id', 'name'), 'opportunity_campaign', $value); ?>
                        <div class="form-group">
                            <div class="checkbox checkbox-success">
                                <input
                                    type="checkbox" <?php if ((isset($opportunity) && $opportunity->progress_from_tasks == 1) || !isset($opportunity)) {
                                    echo 'checked';
                                } ?> name="progress_from_tasks" id="progress_from_tasks">
                                <label
                                    for="progress_from_tasks"><?php echo _l('calculate_progress_through_tasks'); ?></label>
                            </div>
                        </div>
                        <?php
                        if (isset($opportunity) && $opportunity->progress_from_tasks == 1) {
                            $value = $this->opportunities_model->calc_progress_by_tasks($opportunity->id);
                        } else if (isset($opportunity) && $opportunity->progress_from_tasks == 0) {
                            $value = $opportunity->progress;
                        } else {
                            $value = 0;
                        }
                        ?>
                        <label for=""><?php echo _l('opportunity_progress'); ?> <span
                                class="label_progress"><?php echo $value; ?>%</span></label>
                        <?php echo form_hidden('progress', $value); ?>
                        <div class="opportunity_progress_slider opportunity_progress_slider_horizontal mbot15"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group select-placeholder">
                                    <label for="billing_type"><?php echo _l('opportunity_billing_type'); ?></label>
                                    <div class="clearfix"></div>
                                    <select name="billing_type" class="selectpicker" id="billing_type"
                                            data-width="100%" <?php echo $disable_type_edit; ?>
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option
                                            value="1" <?php if (isset($opportunity) && $opportunity->billing_type == 1 || !isset($opportunity) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 1) {
                                            echo 'selected';
                                        } ?>><?php echo _l('opportunity_billing_type_fixed_cost'); ?></option>
                                        <option
                                            value="2" <?php if (isset($opportunity) && $opportunity->billing_type == 2 || !isset($opportunity) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 2) {
                                            echo 'selected';
                                        } ?>><?php echo _l('opportunity_billing_type_opportunity_hours'); ?></option>
                                        <option value="3"
                                                data-subtext="<?php echo _l('opportunity_billing_type_opportunity_task_hours_hourly_rate'); ?>" <?php if (isset($opportunity) && $opportunity->billing_type == 3 || !isset($opportunity) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 3) {
                                            echo 'selected';
                                        } ?>><?php echo _l('opportunity_billing_type_opportunity_task_hours'); ?></option>
                                    </select>
                                    <?php if ($disable_type_edit != '') {
                                        echo '<p class="text-danger">' . _l('cant_change_billing_type_billed_tasks_found') . '</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group select-placeholder">
                                    <label for="status"><?php echo _l('opportunity_status'); ?></label>
                                    <div class="clearfix"></div>
                                    <select name="status" id="status" class="selectpicker" data-width="100%"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <?php foreach ($statuses as $status) { ?>
                                            <option
                                                value="<?php echo $status['id']; ?>" <?php if (!isset($opportunity) && $status['id'] == 2 || (isset($opportunity) && $opportunity->status == $status['id'])) {
                                                echo 'selected';
                                            } ?>><?php echo $status['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php if (total_rows('tblemailtemplates', array('slug' => 'opportunity-finished-to-customer', 'active' => 0)) == 0) { ?>
                            <div class="form-group opportunity_marked_as_finished hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="opportunity_marked_as_finished_email_to_contacts"
                                           id="opportunity_marked_as_finished_email_to_contacts">
                                    <label
                                        for="opportunity_marked_as_finished_email_to_contacts"><?php echo _l('opportunity_marked_as_finished_to_contacts'); ?></label>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isset($opportunity)) { ?>
                            <div class="form-group mark_all_tasks_as_completed hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="mark_all_tasks_as_completed"
                                           id="mark_all_tasks_as_completed">
                                    <label
                                        for="mark_all_tasks_as_completed"><?php echo _l('opportunity_mark_all_tasks_as_completed'); ?></label>
                                </div>
                            </div>
                            <div class="notify_opportunity_members_status_change hide">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="notify_opportunity_members_status_change"
                                           id="notify_opportunity_members_status_change">
                                    <label
                                        for="notify_opportunity_members_status_change"><?php echo _l('notify_opportunity_members_status_change'); ?></label>
                                </div>
                                <hr/>
                            </div>
                        <?php } ?>
                        <?php
                        $input_field_hide_class_total_cost = '';
                        if (!isset($opportunity)) {
                            if ($auto_select_billing_type && $auto_select_billing_type->billing_type != 1 || !$auto_select_billing_type) {
                                $input_field_hide_class_total_cost = 'hide';
                            }
                        } else if (isset($opportunity) && $opportunity->billing_type != 1) {
                            $input_field_hide_class_total_cost = 'hide';
                        }
                        ?>
                        <div id="opportunity_cost" class="<?php echo $input_field_hide_class_total_cost; ?>">
                            <?php $value = (isset($opportunity) ? $opportunity->opportunity_cost : ''); ?>
                            <?php echo render_input('opportunity_cost', 'opportunity_total_cost', $value, 'number'); ?>
                        </div>
                        <?php
                        $input_field_hide_class_rate_per_hour = '';
                        if (!isset($opportunity)) {
                            if ($auto_select_billing_type && $auto_select_billing_type->billing_type != 2 || !$auto_select_billing_type) {
                                $input_field_hide_class_rate_per_hour = 'hide';
                            }
                        } else if (isset($opportunity) && $opportunity->billing_type != 2) {
                            $input_field_hide_class_rate_per_hour = 'hide';
                        }
                        ?>
                        <div id="opportunity_rate_per_hour"
                             class="<?php echo $input_field_hide_class_rate_per_hour; ?>">
                            <?php $value = (isset($opportunity) ? $opportunity->opportunity_rate_per_hour : ''); ?>
                            <?php
                            $input_disable = array();
                            if ($disable_type_edit != '') {
                                $input_disable['disabled'] = true;
                            }
                            ?>
                            <?php echo render_input('opportunity_rate_per_hour', 'opportunity_rate_per_hour', $value, 'number', $input_disable); ?>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_input('estimated_hours', 'estimated_hours', isset($opportunity) ? $opportunity->estimated_hours : '', 'number'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                $selected = array();
                                if (isset($opportunity_members)) {
                                    foreach ($opportunity_members as $member) {
                                        array_push($selected, $member['staff_id']);
                                    }
                                } else {
                                    array_push($selected, get_staff_user_id());
                                }
                                echo render_select('opportunity_members[]', $staff, array('staffid', array('firstname', 'lastname')), 'opportunity_members', $selected, array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($opportunity) ? _d($opportunity->start_date) : _d(date('Y-m-d'))); ?>
                                <?php echo render_date_input('start_date', 'opportunity_start_date', $value); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($opportunity) ? _d($opportunity->deadline) : ''); ?>
                                <?php echo render_date_input('deadline', 'opportunity_deadline', $value); ?>
                            </div>
                        </div>
                        <?php if (isset($opportunity) && $opportunity->date_finished != null && $opportunity->status == 4) { ?>
                            <?php echo render_datetime_input('date_finished', 'opportunity_completed_date', _dt($opportunity->date_finished)); ?>
                        <?php } ?>
                        <div class="form-group">
                            <label for="tags" class="control-label"><i class="fa fa-tag"
                                                                       aria-hidden="true"></i> <?php echo _l('tags'); ?>
                            </label>
                            <input type="text" class="tagsinput" id="tags" name="tags"
                                   value="<?php echo(isset($opportunity) ? prep_tags_input(get_tags_in($opportunity->id, 'opportunity')) : ''); ?>"
                                   data-role="tagsinput">
                        </div>
                        <?php $rel_id_custom_field = (isset($opportunity) ? $opportunity->id : false); ?>
                        <?php echo render_custom_fields('opportunities', $rel_id_custom_field); ?>
                        <p class="bold"><?php echo _l('opportunity_description'); ?></p>
                        <?php $contents = '';
                        if (isset($opportunity)) {
                            $contents = $opportunity->description;
                        } ?>
                        <?php echo render_textarea('description', '', $contents, array(), array(), '', 'tinymce'); ?>
                        <?php if (total_rows('tblemailtemplates', array('slug' => 'assigned-to-opportunity', 'active' => 0)) == 0) { ?>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="send_created_email" id="send_created_email">
                                <label
                                    for="send_created_email"><?php echo _l('opportunity_send_created_email'); ?></label>
                            </div>
                        <?php } ?>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" data-form="#opportunity_form" class="btn btn-info" autocomplete="off"
                                    data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body" id="opportunity-settings-area">
                        <h4 class="no-margin">
                            <?php echo _l('opportunity_settings'); ?>
                        </h4>
                        <hr class="hr-panel-heading"/>
                        <?php foreach ($settings as $setting) { ?>

                            <?php
                            $checked = ' checked';
                            if (isset($opportunity)) {
                                if ($opportunity->settings->{$setting} == 0) {
                                    $checked = '';
                                }
                            } else {
                                foreach ($last_opportunity_settings as $_l_setting) {
                                    if ($setting == $_l_setting['name']) {
                                        // hide_opportunity_tasks_on_main_tasks_table is not applied on most used settings to prevent confusions
                                        if ($_l_setting['value'] == 0 || $_l_setting['name'] == 'hide_opportunity_tasks_on_main_tasks_table') {
                                            $checked = '';
                                        }
                                    }
                                }
                                if (count($last_opportunity_settings) == 0 && $setting == 'hide_opportunity_tasks_on_main_tasks_table') {
                                    $checked = '';
                                }
                            } ?>
                            <?php if ($setting != 'available_features') { ?>
                                <div class="checkbox">
                                    <input type="checkbox"
                                           name="settings[<?php echo $setting; ?>]" <?php echo $checked; ?>
                                           id="<?php echo $setting; ?>">
                                    <label for="<?php echo $setting; ?>">
                                        <?php if ($setting == 'hide_opportunity_tasks_on_main_tasks_table') { ?>
                                            <?php echo _l('hide_opportunity_tasks_on_main_tasks_table'); ?>
                                        <?php } else { ?>
                                            <?php echo _l('opportunity_allow_client_to', _l('opportunity_setting_' . $setting)); ?>
                                        <?php } ?>
                                    </label>
                                </div>
                            <?php } else { ?>
                                <div class="form-group mtop15 select-placeholder opportunity-available-features">
                                    <label for="available_features"><?php echo _l('visible_tabs'); ?></label>
                                    <select name="settings[<?php echo $setting; ?>][]" id="<?php echo $setting; ?>"
                                            multiple="true" class="selectpicker" id="available_features"
                                            data-width="100%" data-actions-box="true" data-hide-disabled="true">
                                        <?php $tabs = get_opportunity_tabs_admin(null, $rel_type); ?>
                                        <?php foreach ($tabs as $tab) {
                                            $selected = '';
                                            ?>
                                            <?php if (isset($tab['dropdown'])) { ?>
                                                <optgroup label="<?php echo $tab['lang']; ?>">
                                                    <?php foreach ($tab['dropdown'] as $tab_dropdown) {
                                                        $selected = '';
                                                        if (isset($opportunity) && $opportunity->settings->available_features[$tab_dropdown['name']] == 1) {
                                                            $selected = ' selected';
                                                        } else if (!isset($opportunity) && count($last_opportunity_settings) > 0) {
                                                            foreach ($last_opportunity_settings as $last_opportunity_setting) {
                                                                if ($last_opportunity_setting['name'] == $setting) {
                                                                    if ($last_opportunity_setting['value'][$tab_dropdown['name']] == 1) {
                                                                        $selected = ' selected';
                                                                    }
                                                                }
                                                            }
                                                        } else if (!isset($opportunity)) {
                                                            $selected = ' selected';
                                                        }
                                                        ?>
                                                        <option
                                                            value="<?php echo $tab_dropdown['name']; ?>"<?php echo $selected; ?><?php if (isset($tab_dropdown['linked_to_customer_option']) && is_array($tab_dropdown['linked_to_customer_option']) && count($tab_dropdown['linked_to_customer_option']) > 0) { ?> data-linked-customer-option="<?php echo implode(',', $tab_dropdown['linked_to_customer_option']); ?>"<?php } ?>><?php echo $tab_dropdown['lang']; ?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } else {
                                                if (isset($opportunity) && $opportunity->settings->available_features[$tab['name']] == 1) {
                                                    $selected = ' selected';
                                                } else if (!isset($opportunity) && count($last_opportunity_settings) > 0) {
                                                    foreach ($last_opportunity_settings as $last_opportunity_setting) {
                                                        if ($last_opportunity_setting['name'] == $setting) {
                                                            if ($last_opportunity_setting['value'][$tab['name']] == 1) {
                                                                $selected = ' selected';
                                                            }
                                                        }
                                                    }
                                                } else if (!isset($opportunity)) {
                                                    $selected = ' selected';
                                                }
                                                ?>
                                                <option
                                                    value="<?php echo $tab['name']; ?>"<?php if ($tab['name'] == 'opportunity_overview') {
                                                    echo ' disabled selected';
                                                } ?>
                                                    <?php echo $selected; ?><?php if (isset($tab['linked_to_customer_option']) && is_array($tab['linked_to_customer_option']) && count($tab['linked_to_customer_option']) > 0) { ?> data-linked-customer-option="<?php echo implode(',', $tab['linked_to_customer_option']); ?>"<?php } ?>><?php echo $tab['lang']; ?>
                                                </option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                            <hr class="no-margin"/>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    <?php if(isset($opportunity)){ ?>
    var original_opportunity_status = '<?php echo $opportunity->status; ?>';
    <?php } ?>
    var _rel_id = $('#rel_id'),
        _rel_type = $('#rel_type'),
        _rel_id_wrapper = $('#rel_id_wrapper'),
        data = {};
    $(function () {

        $('select[name="billing_type"]').on('change', function () {
            var type = $(this).val();
            if (type == 1) {
                $('#opportunity_cost').removeClass('hide');
                $('#opportunity_rate_per_hour').addClass('hide');
            } else if (type == 2) {
                $('#opportunity_cost').addClass('hide');
                $('#opportunity_rate_per_hour').removeClass('hide');
            } else {
                $('#opportunity_cost').addClass('hide');
                $('#opportunity_rate_per_hour').addClass('hide');
            }
        });

        _validate_form($('form'), {
            name: 'required',
            clientid: 'required',
            start_date: 'required',
            billing_type: 'required',
            rel_type: 'required',
            rel_id: 'required',
            type: 'required',
            source: 'required',
            sales_stage_id: 'required',
            campaign_id: 'required',
        });

        $('select[name="status"]').on('change', function () {
            var status = $(this).val();
            var mark_all_tasks_completed = $('.mark_all_tasks_as_completed');
            var notify_opportunity_members_status_change = $('.notify_opportunity_members_status_change');
            mark_all_tasks_completed.removeClass('hide');
            if (typeof (original_opportunity_status) != 'undefined') {
                if (original_opportunity_status != status) {
                    mark_all_tasks_completed.removeClass('hide');
                    mark_all_tasks_completed.find('input').prop('checked', true);
                    notify_opportunity_members_status_change.removeClass('hide');
                } else {
                    mark_all_tasks_completed.addClass('hide');
                    mark_all_tasks_completed.find('input').prop('checked', false);
                    notify_opportunity_members_status_change.addClass('hide');
                }
            }
            if (status == 4) {
                $('.opportunity_marked_as_finished').removeClass('hide');
            } else {
                $('.opportunity_marked_as_finished').addClass('hide');
                $('.opportunity_marked_as_finished').prop('checked', false);
            }
        });

        $('form').on('submit', function () {
            $('select[name="billing_type"]').prop('disabled', false);
            $('#available_features,#available_features option').prop('disabled', false);
            $('input[name="opportunity_rate_per_hour"]').prop('disabled', false);
        });

        var progress_input = $('input[name="progress"]');
        var progress_from_tasks = $('#progress_from_tasks');
        var progress = progress_input.val();

        $('.opportunity_progress_slider').slider({
            min: 0,
            max: 100,
            value: progress,
            disabled: progress_from_tasks.prop('checked'),
            slide: function (event, ui) {
                progress_input.val(ui.value);
                $('.label_progress').html(ui.value + '%');
            }
        });

        progress_from_tasks.on('change', function () {
            var _checked = $(this).prop('checked');
            $('.opportunity_progress_slider').slider({disabled: _checked});
        });

        $('#opportunity-settings-area input').on('change', function () {
            if ($(this).attr('id') == 'view_tasks' && $(this).prop('checked') == false) {
                $('#create_tasks').prop('checked', false).prop('disabled', true);
                $('#edit_tasks').prop('checked', false).prop('disabled', true);
                $('#view_task_comments').prop('checked', false).prop('disabled', true);
                $('#comment_on_tasks').prop('checked', false).prop('disabled', true);
                $('#view_task_attachments').prop('checked', false).prop('disabled', true);
                $('#view_task_checklist_items').prop('checked', false).prop('disabled', true);
                $('#upload_on_tasks').prop('checked', false).prop('disabled', true);
                $('#view_task_total_logged_time').prop('checked', false).prop('disabled', true);
            } else if ($(this).attr('id') == 'view_tasks' && $(this).prop('checked') == true) {
                $('#create_tasks').prop('disabled', false);
                $('#edit_tasks').prop('disabled', false);
                $('#view_task_comments').prop('disabled', false);
                $('#comment_on_tasks').prop('disabled', false);
                $('#view_task_attachments').prop('disabled', false);
                $('#view_task_checklist_items').prop('disabled', false);
                $('#upload_on_tasks').prop('disabled', false);
                $('#view_task_total_logged_time').prop('disabled', false);
            }
        });

        // Auto adjust customer permissions based on selected opportunity visible tabs
        // Eq opportunity creator disable TASKS tab, then this function will auto turn off customer opportunity option Allow customer to view tasks

        $('#available_features').on('change', function () {
            $("#available_features option").each(function () {
                if ($(this).data('linked-customer-option') && !$(this).is(':selected')) {
                    var opts = $(this).data('linked-customer-option').split(',');
                    for (var i = 0; i < opts.length; i++) {
                        var opportunity_option = $('#' + opts[i]);
                        opportunity_option.prop('checked', false);
                        if (opts[i] == 'view_tasks') {
                            opportunity_option.trigger('change');
                        }
                    }
                }
            });
        });
        $("#view_tasks").trigger('change');
        <?php if(!isset($opportunity)) { ?>
        $('#available_features').trigger('change');
        <?php } ?>

        $('body').on('change', '#rel_id', function () {
            if ($(this).val() != '') {
                $('input[name="rel_to"]').val(_rel_id.find('option:selected').text());
            }
        });
        $('.rel_id_label').html(_rel_type.find('option:selected').text());
        _rel_type.on('change', function () {
            var clonedSelect = _rel_id.html('').clone();
            _rel_id.selectpicker('destroy').remove();
            _rel_id = clonedSelect;
            $('#rel_id_select').append(clonedSelect);
            opportunity_rel_id_select();
            if ($(this).val() != '') {
                _rel_id_wrapper.removeClass('hide');
            } else {
                _rel_id_wrapper.addClass('hide');
            }
            $('.rel_id_label').html(_rel_type.find('option:selected').text());
        });
        opportunity_rel_id_select();
        <?php if(!isset($opportunity) && $rel_id != ''){ ?>
        _rel_id.change();
        <?php } ?>
    });

    function opportunity_rel_id_select() {
        var serverData = {};
        serverData.rel_id = _rel_id.val();
        data.type = _rel_type.val();
        <?php if(isset($opportunity)){ ?>
        serverData.connection_type = 'opportunity';
        serverData.connection_id = '<?php echo $opportunity->id; ?>';
        <?php } ?>
        init_ajax_search(_rel_type.val(), _rel_id, serverData);
    }
</script>
</body>
</html>
