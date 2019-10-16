<div class="modal fade" id="opportunity_contact_model" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(admin_url('opportunities/contact'), array('id' => 'opportunity-contact-form')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo $title; ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group select-placeholder contacts-wrapper">
                    <label for="contact_id"><?php echo _l('contact'); ?></label>
                    <div id="contact_ajax_search_wrapper">
                        <select name="contact_id" id="contact_id"
                                class="contacts ajax-search"
                                data-live-search="true" data-width="100%"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php
                            if (isset($contact) && $contact->contact_id != 0) {
                                echo '<option value="' . $contact->contact_id . '" selected>' . $contact->firstname . ' ' . $contact->lastname . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <?php $value = isset($contact) ? $contact->note : ''; ?>
                <?php echo render_input('note', 'opportunity_contact_note', $value); ?>
                <?php $value = isset($contact) ? $contact->id : ''; ?>
                <?php echo form_hidden('id', $value); ?>
                <?php echo form_hidden('opportunity_id', $opportunity_id); ?>
                <div class="clearfix"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
