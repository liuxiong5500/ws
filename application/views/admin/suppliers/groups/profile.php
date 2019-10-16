<h4 class="supplier-profile-group-heading"><?php echo _l('supplier_add_edit_profile'); ?></h4>
<div class="row">
    <?php echo form_open($this->uri->uri_string(), array('class' => 'supplier-form', 'autocomplete' => 'off')); ?>
    <div class="additional"></div>
    <div class="col-md-12">
        <div class="horizontal-scrollable-tabs">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
                <ul class="nav nav-tabs profile-tabs row supplier-profile-tabs nav-tabs-horizontal" role="tablist">
                    <li role="presentation" class="<?php if (!$this->input->get('tab')) {
                        echo 'active';
                    }; ?>">
                        <a href="#contact_info" aria-controls="contact_info" role="tab" data-toggle="tab">
                            <?php echo _l('supplier_profile_details'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#billing_and_shipping" aria-controls="billing_and_shipping" role="tab"
                           data-toggle="tab">
                            <?php echo _l('billing_shipping'); ?>
                        </a>
                    </li>
                    <?php do_action('after_customer_billing_and_shipping_tab', isset($supplier) ? $supplier : false); ?>
                    <?php if (isset($supplier)) { ?>
                        <li role="presentation">
                            <a href="#supplier_admins" aria-controls="supplier_admins" role="tab" data-toggle="tab">
                                <?php echo _l('supplier_admins'); ?>
                            </a>
                        </li>
                        <?php do_action('after_supplier_admins_tab', $supplier); ?>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <?php do_action('after_supplier_profile_tab_content', isset($supplier) ? $supplier : false); ?>
            <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab')) {
                echo ' active';
            }; ?>" id="contact_info">
                <div class="row">
                    <div
                        class="col-md-12<?php if (isset($supplier) && (!is_empty_supplier_company($supplier->id) && total_rows('tblsuppliercontacts', array('supplier_id' => $supplier->id, 'is_primary' => 1)) > 0)) {
                            echo '';
                        } else {
                            echo ' hide';
                        } ?>" id="supplier-show-primary-contact-wrapper">
                        <div class="checkbox checkbox-info mbot20 no-mtop">
                            <input type="checkbox"
                                   name="show_primary_contact"<?php if (isset($supplier) && $supplier->show_primary_contact == 1) {
                                echo ' checked';
                            } ?> value="1" id="show_primary_contact">
                            <label
                                for="show_primary_contact"><?php echo _l('show_primary_contact'); ?></label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php $value = (isset($supplier) ? $supplier->company : ''); ?>
                        <?php $attrs = (isset($supplier) ? array() : array('autofocus' => true)); ?>
                        <?php echo render_input('company', 'supplier_company', $value, 'text', $attrs); ?>
                        <?php if (get_option('company_requires_vat_number_field') == 1) {
                            $value = (isset($supplier) ? $supplier->vat : '');
                            echo render_input('vat', 'supplier_vat_number', $value);
                        } ?>
                        <?php $value = (isset($supplier) ? $supplier->phonenumber : ''); ?>
                        <?php echo render_input('phonenumber', 'supplier_phonenumber', $value); ?>
                        <?php if ((isset($supplier) && empty($supplier->website)) || !isset($supplier)) {
                            $value = (isset($supplier) ? $supplier->website : '');
                            echo render_input('website', 'supplier_website', $value);
                        } else { ?>
                            <div class="form-group">
                                <label for="website"><?php echo _l('supplier_website'); ?></label>
                                <div class="input-group">
                                    <input type="text" name="website" id="website"
                                           value="<?php echo $supplier->website; ?>" class="form-control">
                                    <div class="input-group-addon">
                                        <span><a href="<?php echo maybe_add_http($supplier->website); ?>"
                                                 target="_blank"
                                                 tabindex="-1"><i class="fa fa-globe"></i></a></span>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        $selected = array();
                        if (isset($supplier_groups)) {
                            foreach ($supplier_groups as $group) {
                                array_push($selected, $group['groupid']);
                            }
                        }
                        if (is_admin() || get_option('staff_members_create_inline_supplier_groups') == '1') {
                            echo render_select_with_input_group('groups_in[]', $groups, array('id', 'name'), 'supplier_groups', $selected, '<a href="#" data-toggle="modal" data-target="#supplier_group_modal"><i class="fa fa-plus"></i></a>', array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                        } else {
                            echo render_select('groups_in[]', $groups, array('id', 'name'), 'supplier_groups', $selected, array('multiple' => true, 'data-actions-box' => true), array(), '', '', false);
                        }
                        ?>
                        <?php if (!isset($supplier)) { ?>
                            <i class="fa fa-question-circle pull-left" data-toggle="tooltip"
                               data-title="<?php echo _l('supplier_currency_change_notice'); ?>"></i>
                        <?php }
                        $s_attrs = array('data-none-selected-text' => _l('system_default_string'));
                        $selected = '';
                        if (isset($client) && supplier_have_transactions($client->userid)) {
                            $s_attrs['disabled'] = true;
                        }
                        foreach ($currencies as $currency) {
                            if (isset($client)) {
                                if ($currency['id'] == $supplier->default_currency) {
                                    $selected = $currency['id'];
                                }
                            }
                        }
                        // Do not remove the currency field from the customer profile!
                        echo render_select('default_currency', $currencies, array('id', 'name', 'symbol'), 'invoice_add_edit_currency', $selected, $s_attrs); ?>
                        <?php if (get_option('disable_language') == 0) { ?>
                            <div class="form-group select-placeholder">
                                <label for="default_language"
                                       class="control-label"><?php echo _l('localization_default_language'); ?>
                                </label>
                                <select name="default_language" id="default_language" class="form-control selectpicker"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""><?php echo _l('system_default_string'); ?></option>
                                    <?php foreach (list_folders(APPPATH . 'language') as $language) {
                                        $selected = '';
                                        if (isset($supplier)) {
                                            if ($supplier->default_language == $language) {
                                                $selected = 'selected';
                                            }
                                        }
                                        ?>
                                        <option
                                            value="<?php echo $language; ?>" <?php echo $selected; ?>><?php echo ucfirst($language); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-md-6">
                        <?php $value = (isset($supplier) ? $supplier->address : ''); ?>
                        <?php echo render_textarea('address', 'supplier_address', $value); ?>
                        <?php $value = (isset($supplier) ? $supplier->city : ''); ?>
                        <?php echo render_input('city', 'supplier_city', $value); ?>
                        <?php $value = (isset($supplier) ? $supplier->state : ''); ?>
                        <?php echo render_input('state', 'supplier_state', $value); ?>
                        <?php $value = (isset($supplier) ? $supplier->zip : ''); ?>
                        <?php echo render_input('zip', 'supplier_postal_code', $value); ?>
                        <?php $countries = get_all_countries();
                        $supplier_default_country = get_option('supplier_default_country');
                        $selected = (isset($supplier) ? $supplier->country : $supplier_default_country);
                        echo render_select('country', $countries, array('country_id', array('short_name')), 'suppliers_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                        ?>
                    </div>
                </div>
            </div>
            <?php if (isset($supplier)) { ?>
                <div role="tabpanel" class="tab-pane" id="supplier_admins">
                    <a href="#" data-toggle="modal" data-target="#supplier_admins_assign"
                       class="btn btn-info mbot30"><?php echo _l('assign_admin'); ?></a>
                    <table class="table dt-table">
                        <thead>
                        <tr>
                            <th><?php echo _l('staff_member'); ?></th>
                            <th><?php echo _l('supplier_admin_date_assigned'); ?></th>
                            <th><?php echo _l('options'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($supplier_admins as $c_admin) { ?>
                            <tr>
                                <td><a href="<?php echo admin_url('profile/' . $c_admin['staff_id']); ?>">
                                        <?php echo staff_profile_image($c_admin['staff_id'], array(
                                            'staff-profile-image-small',
                                            'mright5'
                                        ));
                                        echo get_staff_full_name($c_admin['staff_id']); ?></a>
                                </td>
                                <td data-order="<?php echo $c_admin['date_assigned']; ?>"><?php echo _dt($c_admin['date_assigned']); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('suppliers/delete_supplier_admin/' . $supplier->id . '/' . $c_admin['staff_id']); ?>"
                                       class="btn btn-danger _delete btn-icon"><i class="fa fa-remove"></i></a>
                                </td>

                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane" id="billing_and_shipping">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-mtop"><?php echo _l('billing_address'); ?> <a href="#"
                                                                                            class="pull-right billing-same-as-supplier">
                                        <small
                                            class="font-medium-xs"><?php echo _l('supplier_billing_same_as_profile'); ?></small>
                                    </a></h4>
                                <hr/>
                                <?php $value = (isset($supplier) ? $supplier->billing_street : ''); ?>
                                <?php echo render_textarea('billing_street', 'billing_street', $value); ?>
                                <?php $value = (isset($supplier) ? $supplier->billing_city : ''); ?>
                                <?php echo render_input('billing_city', 'billing_city', $value); ?>
                                <?php $value = (isset($supplier) ? $supplier->billing_state : ''); ?>
                                <?php echo render_input('billing_state', 'billing_state', $value); ?>
                                <?php $value = (isset($supplier) ? $supplier->billing_zip : ''); ?>
                                <?php echo render_input('billing_zip', 'billing_zip', $value); ?>
                                <?php $selected = (isset($supplier) ? $supplier->billing_country : ''); ?>
                                <?php echo render_select('billing_country', $countries, array('country_id', array('short_name')), 'billing_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex'))); ?>
                            </div>
                            <div class="col-md-6">
                                <h4 class="no-mtop">
                                    <i class="fa fa-question-circle" data-toggle="tooltip"
                                       data-title="<?php echo _l('supplier_shipping_address_notice'); ?>"></i>
                                    <?php echo _l('shipping_address'); ?> <a href="#"
                                                                             class="pull-right supplier-copy-billing-address">
                                        <small class="font-medium-xs"><?php echo _l('supplier_billing_copy'); ?></small>
                                    </a>
                                </h4>
                                <hr/>
                                <?php $value = (isset($supplier) ? $supplier->shipping_street : ''); ?>
                                <?php echo render_textarea('shipping_street', 'shipping_street', $value); ?>
                                <?php $value = (isset($supplier) ? $supplier->shipping_city : ''); ?>
                                <?php echo render_input('shipping_city', 'shipping_city', $value); ?>
                                <?php $value = (isset($supplier) ? $supplier->shipping_state : ''); ?>
                                <?php echo render_input('shipping_state', 'shipping_state', $value); ?>
                                <?php $value = (isset($supplier) ? $supplier->shipping_zip : ''); ?>
                                <?php echo render_input('shipping_zip', 'shipping_zip', $value); ?>
                                <?php $selected = (isset($supplier) ? $supplier->shipping_country : ''); ?>
                                <?php echo render_select('shipping_country', $countries, array('country_id', array('short_name')), 'shipping_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex'))); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?php if (isset($supplier)) { ?>
    <div class="modal fade" id="supplier_admins_assign" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <?php echo form_open(admin_url('suppliers/assign_admins/' . $supplier->id)); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo _l('assign_admin'); ?></h4>
                </div>
                <div class="modal-body">
                    <?php
                    $selected = array();
                    foreach ($supplier_admins as $c_admin) {
                        array_push($selected, $c_admin['staff_id']);
                    }
                    echo render_select('supplier_admins[]', $staff, array('staffid', array('firstname', 'lastname')), '', $selected, array('multiple' => true), array(), '', '', false); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                </div>
            </div>
            <!-- /.modal-content -->
            <?php echo form_close(); ?>
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
<?php } ?>
<?php $this->load->view('admin/suppliers/supplier_group'); ?>
