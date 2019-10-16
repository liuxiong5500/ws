<?php init_head(); ?>
<div id="wrapper">
    <?php echo form_hidden('opportunity_id', $opportunity->id) ?>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s opportunity-top-panel panel-full">
                    <div class="panel-body _buttons">
                        <div class="row">
                            <div class="col-md-7 opportunity-heading">
                                <h3 class="hide opportunity-name"><?php echo $opportunity->name; ?></h3>
                                <div id="opportunity_view_name" class="pull-left">
                                    <select class="selectpicker" id="opportunity_top"
                                            data-width="fit"<?php if (count($other_opportunities) > 4) { ?> data-live-search="true" <?php } ?>>
                                        <option value="<?php echo $opportunity->id; ?>"
                                                selected><?php echo $opportunity->name; ?></option>
                                        <?php foreach ($other_opportunities as $op) { ?>
                                            <option value="<?php echo $op['id']; ?>"
                                                    data-subtext="<?php echo $op['name']; ?>">
                                                #<?php echo $op['id']; ?> - <?php echo $op['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="visible-xs">
                                    <div class="clearfix"></div>
                                </div>
                                <?php echo '<div class="label pull-left mleft15 mtop5 p8 opportunity-status-label-' . $opportunity->status . '" style="background:' . $opportunity_status['color'] . '">' . $opportunity_status['name'] . '</div>'; ?>
                            </div>
                            <div class="col-md-5 text-right">
                                <?php if (has_permission('tasks', '', 'create')) { ?>
                                    <a href="#"
                                       onclick="new_task_from_relation(undefined,'opportunity',<?php echo $opportunity->id; ?>); return false;"
                                       class="btn btn-info"><?php echo _l('new_task'); ?></a>
                                <?php } ?>
                                <?php
                                $invoice_func = 'pre_invoice_opportunity';
                                ?>
                                <?php if (has_permission('invoices', '', 'create')) { ?>
                                    <a href="#"
                                       onclick="<?php echo $invoice_func; ?>(<?php echo $opportunity->id; ?>); return false;"
                                       class="invoice-opportunity btn btn-info<?php if ($opportunity->client_data->active == 0) {
                                           echo ' hide';
                                       } ?>"><?php echo _l('invoice_opportunity'); ?></a>
                                <?php } ?>
                                <?php
                                $opportunity_pin_tooltip = _l('pin_opportunity');
                                if (total_rows('tblpinnedopportunities', array('staff_id' => get_staff_user_id(), 'opportunity_id' => $opportunity->id)) > 0) {
                                    $opportunity_pin_tooltip = _l('unpin_opportunity');
                                }
                                ?>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                        <?php echo _l('more'); ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right width200 opportunity-actions">
                                        <li>
                                            <a href="<?php echo admin_url('opportunities/pin_action/' . $opportunity->id); ?>">
                                                <?php echo $opportunity_pin_tooltip; ?>
                                            </a>
                                        </li>
                                        <?php if (has_permission('opportunities', '', 'edit')) { ?>
                                            <li>
                                                <a href="<?php echo admin_url('opportunities/opportunity/' . $opportunity->id); ?>">
                                                    <?php echo _l('edit_opportunity'); ?>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if (has_permission('opportunities', '', 'create')) { ?>
                                            <li>
                                                <a href="#" onclick="copy_opportunity(); return false;">
                                                    <?php echo _l('copy_opportunity'); ?>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if (has_permission('opportunities', '', 'create') || has_permission('opportunities', '', 'edit')) { ?>
                                            <li class="divider"></li>
                                            <?php foreach ($statuses as $status) {
                                                if ($status['id'] == $opportunity->status) {
                                                    continue;
                                                }
                                                ?>
                                                <li>
                                                    <a href="#"
                                                       onclick="opportunity_mark_as_modal(<?php echo $status['id']; ?>,<?php echo $opportunity->id; ?>); return false;"><?php echo _l('opportunity_mark_as', $status['name']); ?></a>
                                                </li>
                                            <?php } ?>
                                        <?php } ?>
                                        <li class="divider"></li>
                                        <?php if (has_permission('opportunities', '', 'create')) { ?>
                                            <li>
                                                <a href="<?php echo admin_url('opportunities/export_opportunity_data/' . $opportunity->id); ?>"
                                                   target="_blank"><?php echo _l('export_opportunity_data'); ?></a>
                                            </li>
                                        <?php } ?>
                                        <?php if (has_permission('opportunities', '', 'delete')) { ?>
                                            <li>
                                                <a href="<?php echo admin_url('opportunities/delete/' . $opportunity->id); ?>"
                                                   class="_delete">
                                                    <span
                                                        class="text-danger"><?php echo _l('delete_opportunity'); ?></span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel_s opportunity-menu-panel">
                    <div class="panel-body">
                        <?php do_action('before_render_opportunity_view', $opportunity->id); ?>
                        <?php $this->load->view('admin/opportunities/opportunity_tabs'); ?>
                    </div>
                </div>
                <?php if ($view == 'opportunity_milestones') { ?>
                    <a href="#"
                       class="opportunity-tabs-and-opts-toggler screen-options-btn bold"><?php echo _l('show_tabs_and_options'); ?></a>
                <?php } else { ?>
                    <?php if ((has_permission('opportunities', '', 'create') || has_permission('opportunities', '', 'edit')) && $opportunity->status == 1 && $this->opportunities_model->timers_started_for_opportunity($opportunity->id)) { ?>
                        <div class="alert alert-warning opportunity-no-started-timers-found mbot15">
                            <?php echo _l('opportunity_not_started_status_tasks_timers_found'); ?>
                        </div>
                    <?php } ?>
                    <?php if ($opportunity->deadline && date('Y-m-d') > $opportunity->deadline && $opportunity->status == 2) { ?>
                        <div class="alert alert-warning bold opportunity-due-notice mbot15">
                            <?php echo _l('opportunity_due_notice', floor((abs(time() - strtotime($opportunity->deadline))) / (60 * 60 * 24))); ?>
                        </div>
                    <?php } ?>
                <?php } ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo $group_view; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<?php if (isset($discussion)) {
    echo form_hidden('discussion_id', $discussion->id);
    echo form_hidden('discussion_user_profile_image_url', $discussion_user_profile_image_url);
    echo form_hidden('current_user_is_admin', $current_user_is_admin);
}
echo form_hidden('opportunity_percent', $percent);
?>
<div id="invoice_opportunity"></div>
<div id="pre_invoice_opportunity"></div>
<?php $this->load->view('admin/opportunities/milestone'); ?>
<?php $this->load->view('admin/opportunities/copy_settings'); ?>
<?php $this->load->view('admin/opportunities/_mark_tasks_finished'); ?>
<?php init_tail(); ?>
<?php $discussion_lang = get_opportunity_discussions_language_array(); ?>
<?php echo app_script('assets/js', 'opportunities.js'); ?>
<div id="opportunity_contact_box"></div>
<!-- For invoices table -->
<script>
    taskid = '<?php echo $this->input->get('taskid'); ?>';
</script>
<script>
    var gantt_data = {};
    <?php if(isset($gantt_data)){ ?>
    gantt_data = <?php echo json_encode($gantt_data); ?>;
    <?php } ?>
    var discussion_id = $('input[name="discussion_id"]').val();
    var discussion_user_profile_image_url = $('input[name="discussion_user_profile_image_url"]').val();
    var current_user_is_admin = $('input[name="current_user_is_admin"]').val();
    var opportunity_id = $('input[name="opportunity_id"]').val();
    if (typeof (discussion_id) != 'undefined') {
        discussion_comments('#discussion-comments', discussion_id, 'regular');
    }
    $(function () {
        validate_opportunity_contact_form();
        var opportunity_progress_color = '<?php echo do_action('admin_opportunity_progress_color', '#84c529'); ?>';
        var circle = $('.opportunity-progress').circleProgress({
            fill: {
                gradient: [opportunity_progress_color, opportunity_progress_color]
            }
        }).on('circle-animation-progress', function (event, progress, stepValue) {
            $(this).find('strong.opportunity-percent').html(parseInt(100 * stepValue) + '<i>%</i>');
        });
    });

    function validate_opportunity_contact_form() {
        _validate_form($('#opportunity-contact-form'), {
            contact_id: 'required'
        });
    }

    function opportunity_contact_request(url) {
        requestGet(url).done(function (response) {
            $('#opportunity_contact_box').html(response);
            init_ajax_search('contact', '#contact_id.ajax-search');
            $("body").find('#opportunity_contact_model').modal({show: true, backdrop: 'static'});
        });
    }

    function discussion_comments(selector, discussion_id, discussion_type) {
        $(selector).comments({
            roundProfilePictures: true,
            textareaRows: 4,
            textareaRowsOnFocus: 6,
            profilePictureURL: discussion_user_profile_image_url,
            enableUpvoting: false,
            enableAttachments: true,
            popularText: '',
            enableDeletingCommentWithReplies: false,
            textareaPlaceholderText: "<?php echo $discussion_lang['discussion_add_comment']; ?>",
            newestText: "<?php echo $discussion_lang['discussion_newest']; ?>",
            oldestText: "<?php echo $discussion_lang['discussion_oldest']; ?>",
            attachmentsText: "<?php echo $discussion_lang['discussion_attachments']; ?>",
            sendText: "<?php echo $discussion_lang['discussion_send']; ?>",
            replyText: "<?php echo $discussion_lang['discussion_reply']; ?>",
            editText: "<?php echo $discussion_lang['discussion_edit']; ?>",
            editedText: "<?php echo $discussion_lang['discussion_edited']; ?>",
            youText: "<?php echo $discussion_lang['discussion_you']; ?>",
            saveText: "<?php echo $discussion_lang['discussion_save']; ?>",
            deleteText: "<?php echo $discussion_lang['discussion_delete']; ?>",
            viewAllRepliesText: "<?php echo $discussion_lang['discussion_view_all_replies'] . ' (__replyCount__)'; ?>",
            hideRepliesText: "<?php echo $discussion_lang['discussion_hide_replies']; ?>",
            noCommentsText: "<?php echo $discussion_lang['discussion_no_comments']; ?>",
            noAttachmentsText: "<?php echo $discussion_lang['discussion_no_attachments']; ?>",
            attachmentDropText: "<?php echo $discussion_lang['discussion_attachments_drop']; ?>",
            currentUserIsAdmin: current_user_is_admin,
            getComments: function (success, error) {
                $.get(admin_url + 'opportunities/get_discussion_comments/' + discussion_id + '/' + discussion_type, function (response) {
                    success(response);
                }, 'json');
            },
            postComment: function (commentJSON, success, error) {
                $.ajax({
                    type: 'post',
                    url: admin_url + 'opportunities/add_discussion_comment/' + discussion_id + '/' + discussion_type,
                    data: commentJSON,
                    success: function (comment) {
                        comment = JSON.parse(comment);
                        success(comment)
                    },
                    error: error
                });
            },
            putComment: function (commentJSON, success, error) {
                $.ajax({
                    type: 'post',
                    url: admin_url + 'opportunities/update_discussion_comment',
                    data: commentJSON,
                    success: function (comment) {
                        comment = JSON.parse(comment);
                        success(comment)
                    },
                    error: error
                });
            },
            deleteComment: function (commentJSON, success, error) {
                $.ajax({
                    type: 'post',
                    url: admin_url + 'opportunities/delete_discussion_comment/' + commentJSON.id,
                    success: success,
                    error: error
                });
            },
            timeFormatter: function (time) {
                return moment(time).fromNow();
            },
            uploadAttachments: function (commentArray, success, error) {
                var responses = 0;
                var successfulUploads = [];
                var serverResponded = function () {
                    responses++;
                    // Check if all requests have finished
                    if (responses == commentArray.length) {
                        // Case: all failed
                        if (successfulUploads.length == 0) {
                            error();
                            // Case: some succeeded
                        } else {
                            successfulUploads = JSON.parse(successfulUploads);
                            success(successfulUploads)
                        }
                    }
                }
                $(commentArray).each(function (index, commentJSON) {
                    // Create form data
                    var formData = new FormData();
                    if (commentJSON.file.size && commentJSON.file.size > max_php_ini_upload_size_bytes) {
                        alert_float('danger', "<?php echo _l("file_exceeds_max_filesize"); ?>");
                        serverResponded();
                    } else {
                        $(Object.keys(commentJSON)).each(function (index, key) {
                            var value = commentJSON[key];
                            if (value) formData.append(key, value);
                        });

                        if (typeof (csrfData) !== 'undefined') {
                            formData.append(csrfData['token_name'], csrfData['hash']);
                        }
                        $.ajax({
                            url: admin_url + 'opportunities/add_discussion_comment/' + discussion_id + '/' + discussion_type,
                            type: 'POST',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (commentJSON) {
                                successfulUploads.push(commentJSON);
                                serverResponded();
                            },
                            error: function (data) {
                                var error = JSON.parse(data.responseText);
                                alert_float('danger', error.message);
                                serverResponded();
                            },
                        });
                    }
                });
            }
        });
    }
</script>
</body>
</html>
