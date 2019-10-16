<div class="row">
    <div class="col-md-12">
        <?php echo form_open_multipart('suppliers/company'); ?>
        <!-- Required hidden field -->
        <?php echo form_hidden('company_form', true); ?>
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="no-margin"><?php echo _l('clients_profile_heading'); ?></h4>
            </div>
        </div>
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="company" class="control-label"><?php echo _l('clients_company'); ?></label>
                            <?php
                            $company_val = $supplier->company;
                            if (!empty($company_val)) {
                                // Check if is realy empty client company so we can set this field to empty
                                // The query where fetch the client auto populate firstname and lastname if company is empty
                                if (is_empty_customer_company($supplier->id)) {
                                    $company_val = '';
                                }
                            }
                            ?>
                            <input type="text" class="form-control" name="company"
                                   value="<?php echo set_value('company', $company_val); ?>">
                            <?php echo form_error('company'); ?>
                        </div>
                        <?php if (get_option('company_requires_vat_number_field') == 1) { ?>
                            <div class="form-group">
                                <label for="vat" class="control-label"><?php echo _l('clients_vat'); ?></label>
                                <input type="text" class="form-control" name="vat" value="<?php if (isset($supplier)) {
                                    echo $supplier->vat;
                                } ?>">
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="phonenumber"><?php echo _l('clients_phone'); ?></label>
                            <input type="text" class="form-control" name="phonenumber" id="phonenumber"
                                   value="<?php echo $supplier->phonenumber; ?>">
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="website"><?php echo _l('client_website'); ?></label>
                            <input type="text" class="form-control" name="website" id="website"
                                   value="<?php echo $supplier->website; ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastname"><?php echo _l('clients_country'); ?></label>
                            <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                    data-live-search="true" name="country" class="form-control" id="country">
                                <option value=""></option>
                                <?php foreach (get_all_countries() as $country) { ?>
                                    <?php
                                    $selected = '';
                                    if ($supplier->country == $country['country_id']) {
                                        echo $selected = true;
                                    }
                                    ?>
                                    <option
                                        value="<?php echo $country['country_id']; ?>" <?php echo set_select('country', $country['country_id'], $selected); ?>><?php echo $country['short_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city"><?php echo _l('clients_city'); ?></label>
                            <input type="text" class="form-control" name="city" id="city"
                                   value="<?php echo $supplier->city; ?>">
                        </div>
                        <div class="form-group">
                            <label for="address"><?php echo _l('clients_address'); ?></label>
                            <textarea name="address" id="address" class="form-control"
                                      rows="4"><?php echo clear_textarea_breaks($supplier->address); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="zip"><?php echo _l('clients_zip'); ?></label>
                            <input type="text" class="form-control" name="zip" id="zip"
                                   value="<?php echo $supplier->zip; ?>">
                        </div>
                        <div class="form-group">
                            <label for="state"><?php echo _l('clients_state'); ?></label>
                            <input type="text" class="form-control" name="state" id="state"
                                   value="<?php echo $supplier->state; ?>">
                        </div>
                        <?php if (get_option('disable_language') == 0) { ?>
                            <div class="form-group">
                                <label for="default_language"
                                       class="control-label"><?php echo _l('localization_default_language'); ?>
                                </label>
                                <select data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                        name="default_language" id="default_language" class="form-control selectpicker">
                                    <option value="" <?php if ($supplier->default_language == '') {
                                        echo 'selected';
                                    } ?>><?php echo _l('system_default_string'); ?></option>
                                    <?php foreach (list_folders(APPPATH . 'language') as $language) {
                                        $selected = '';
                                        if ($supplier->default_language == $language) {
                                            $selected = 'selected';
                                        }
                                        ?>
                                        <option
                                            value="<?php echo $language; ?>" <?php echo $selected; ?>><?php echo ucfirst($language); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="row p15">
                        <div class="col-md-12 text-right mtop20">
                            <div class="form-group">
                                <button type="submit"
                                        class="btn btn-info"><?php echo _l('clients_edit_profile_update_btn'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
