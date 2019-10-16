<div class="modal fade" id="mark_tasks_finished_modal" tabindex="-1" role="dialog" data-toggle="modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4><?php echo _l('additional_action_required'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="notify_opportunity_members_status_change"
                           id="notify_opportunity_members_status_change">
                    <label
                        for="notify_opportunity_members_status_change"><?php echo _l('notify_opportunity_members_status_change'); ?></label>
                </div>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="mark_all_tasks_as_completed" checked id="mark_all_tasks_as_completed">
                    <label
                        for="mark_all_tasks_as_completed"><?php echo _l('opportunity_mark_all_tasks_as_completed'); ?></label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info" id="opportunity_mark_status_confirm"
                        onclick="confirm_opportunity_status_change(this); return false;"><?php echo _l('opportunity_mark_tasks_finished_confirm'); ?></button>
            </div>
        </div>
    </div>
</div>
