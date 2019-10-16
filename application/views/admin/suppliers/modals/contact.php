<!-- Modal Contact -->
<div class="modal fade" id="contact" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/suppliers/contact/' . $supplier_id . '/' . $contactid, array('id' => 'contact-form', 'autocomplete' => 'off')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $title; ?><br/>
                    <small class="color-white" id=""><?php echo get_supplier_company_name($supplier_id, true); ?></small>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php if (isset($contact)) { ?>
                            <img src="<?php echo supplier_contact_profile_image_url($contact->id, 'thumb'); ?>" id="contact-img"
                                 class="client-profile-image-thumb">
                            <?php if (!empty($contact->profile_image)) { ?>
                                <a href="#"
                                   onclick="delete_contact_profile_image(<?php echo $contact->id; ?>); return false;"
                                   class="text-danger pull-right" id="contact-remove-img"><i
                                            class="fa fa-remove"></i></a>
                            <?php } ?>
                            <hr/>
                        <?php } ?>
                        <div id="contact-profile-image"
                             class="form-group<?php if (isset($contact) && !empty($contact->profile_image)) {
                                 echo ' hide';
                             } ?>">
                            <label for="profile_image"
                                   class="profile-image"><?php echo _l('supplier_profile_image'); ?></label>
                            <input type="file" name="profile_image" class="form-control" id="profile_image">
                        </div>
                        <?php if (isset($contact)) { ?>
                            <div class="alert alert-warning hide" role="alert" id="contact_proposal_warning">
                                <?php echo _l('proposal_warning_email_change', array(_l('contact_lowercase'), _l('contact_lowercase'), _l('contact_lowercase'))); ?>
                                <hr/>
                                <a href="#" id="contact_update_proposals_emails" data-original-email=""
                                   onclick="update_all_proposal_emails_linked_to_contact(<?php echo $contact->id; ?>); return false;"><?php echo _l('update_proposal_email_yes'); ?></a>
                                <br/>
                                <a href="#"
                                   onclick="close_modal_manually('#contact'); return false;"><?php echo _l('update_proposal_email_no'); ?></a>
                            </div>
                        <?php } ?>
                        <!-- // For email exist check -->
                        <?php echo form_hidden('contactid', $contactid); ?>
                        <?php $value = (isset($contact) ? $contact->firstname : ''); ?>
                        <?php echo render_input('firstname', 'supplier_firstname', $value); ?>
                        <?php $value = (isset($contact) ? $contact->lastname : ''); ?>
                        <?php echo render_input('lastname', 'supplier_lastname', $value); ?>
                        <?php $value = (isset($contact) ? $contact->title : ''); ?>
                        <?php echo render_input('title', 'contact_position', $value); ?>
                        <?php $value = (isset($contact) ? $contact->email : ''); ?>
                        <?php echo render_input('email', 'supplier_email', $value, 'email'); ?>
                        <?php $value = (isset($contact) ? $contact->phonenumber : ''); ?>
                        <?php echo render_input('phonenumber', 'supplier_phonenumber', $value, 'text', array('autocomplete' => 'off')); ?>
                        <div class="form-group contact-direction-option">
                            <label for="direction"><?php echo _l('document_direction'); ?></label>
                            <select class="selectpicker"
                                    data-none-selected-text="<?php echo _l('system_default_string'); ?>"
                                    data-width="100%" name="direction" id="direction">
                                <option value="" <?php if (isset($contact) && empty($contact->direction)) {
                                    echo 'selected';
                                } ?>></option>
                                <option value="ltr" <?php if (isset($contact) && $contact->direction == 'ltr') {
                                    echo 'selected';
                                } ?>>LTR
                                </option>
                                <option value="rtl" <?php if (isset($contact) && $contact->direction == 'rtl') {
                                    echo 'selected';
                                } ?>>RTL
                                </option>
                            </select>
                        </div>
                        <?php $rel_id = (isset($contact) ? $contact->id : false); ?>
                        <?php echo render_custom_fields('contacts', $rel_id); ?>


                        <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                        <input type="text" class="fake-autofill-field" name="fakeusernameremembered" value=''
                               tabindex="-1"/>
                        <input type="password" class="fake-autofill-field" name="fakepasswordremembered" value=''
                               tabindex="-1"/>

                        <div class="supplier_password_set_wrapper">
                            <label for="password" class="control-label">
                                <?php echo _l('supplier_password'); ?>
                            </label>
                            <div class="input-group">

                                <input type="password" class="form-control password" name="password"
                                       autocomplete="false">
                                <span class="input-group-addon">
                                <a href="#password" class="show_password"
                                   onclick="showPassword('password'); return false;"><i class="fa fa-eye"></i></a>
                            </span>
                                <span class="input-group-addon">
                                <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i
                                            class="fa fa-refresh"></i></a>
                            </span>
                            </div>
                            <?php if (isset($contact)) { ?>
                                <p class="text-muted">
                                    <?php echo _l('supplier_password_change_populate_note'); ?>
                                </p>
                                <?php if ($contact->last_password_change != NULL) {
                                    echo _l('supplier_password_last_changed');
                                    echo '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($contact->last_password_change) . '"> ' . time_ago($contact->last_password_change) . '</span>';
                                }
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" data-loading-text="<?php echo _l('wait_text'); ?>"
                        autocomplete="off" data-form="#contact-form"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php if (!isset($contact)) { ?>
    <script>
        $(function () {
            // Guess auto email notifications based on the default contact permissios
            var permInputs = $('input[name="permissions[]"]');
            $.each(permInputs, function (i, input) {
                input = $(input);
                if (input.prop('checked') === true) {
                    $('#contact_email_notifications [data-perm-id="' + input.val() + '"]').prop('checked', true);
                }
            });
        });
    </script>
<?php } ?>
