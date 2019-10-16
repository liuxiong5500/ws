<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php echo form_open($this->uri->uri_string(), array('id' => 'purchase-order-form', 'class' => '_transaction_form purchase-order-form')); ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <?php $value = (isset($estimate) ? $estimate->order_number : ''); ?>
                                <?php echo render_input('order_number', 'purchase_order_number', $value, 'text', ['disabled'=>true]); ?>
                                <?php
                                $selected = '';
                                $s_attrs = array('data-show-subtext' => true);
                                foreach ($suppliers as $supplier) {
                                    if (isset($estimate)) {
                                        if ($supplier['id'] == $estimate->supplier) {
                                            $selected = $supplier['id'];
                                        }
                                    }
                                }
                                ?>
                                <?php
                                echo render_select('supplier', $suppliers, array('id', 'company'), 'purchase_order_supplier', $selected, do_action('purchase_order_supplier_disabled', $s_attrs));
                                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="f_client_id">
                                            <div class="form-group select-placeholder">
                                                <label for="clientid"
                                                       class="control-label"><?php echo _l('invoice_select_customer'); ?></label>
                                                <select id="clientid" name="clientid" data-live-search="true"
                                                        data-width="100%"
                                                        class="ajax-search<?php if (isset($estimate) && empty($estimate->clientid)) {
                                                            echo ' customer-removed';
                                                        } ?>"
                                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                    <?php $selected = (isset($estimate) ? $estimate->clientid : '');
                                                    if ($selected == '') {
                                                        $selected = (isset($customer_id) ? $customer_id : '');
                                                    }
                                                    if ($selected != '') {
                                                        $rel_data = get_relation_data('customer', $selected);
                                                        $rel_val = get_relation_values($rel_data, 'customer');
                                                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder warehouses-wrapper">
                                            <label for="warehouse"><?php echo _l('purchase_order_warehouse'); ?></label>
                                            <div id="warehouse_ajax_search_wrapper">
                                                <select name="warehouse" id="warehouse" class="warehouses ajax-search"
                                                        data-live-search="true" data-width="100%"
                                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                    <?php
                                                    if (isset($estimate) && $estimate->warehouse != 0) {
                                                        echo '<option value="' . $estimate->warehouse . '" selected>' . get_warehouse_name_by_id($estimate->warehouse) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $value = (isset($estimate) ? $estimate->pe_number : ''); ?>
                                <?php echo render_input('pe_number', 'purchase_order_proposal_estimate_number', $value, 'text'); ?>
                                <?php $value = (isset($estimate) ? _d(date('Y-m-d', strtotime($estimate->po_date))) : _d(date('Y-m-d'))); ?>
                                <?php echo render_date_input('po_date', 'purchase_order_date', $value); ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                $selected = '';
                                $s_attrs = array('data-show-subtext' => true);
                                $rate = 0;
                                foreach ($currencies as $currency) {
                                    if (isset($estimate)) {
                                        if ($currency['id'] == $estimate->currency) {
                                            $selected = $currency['id'];
                                            $rate = $currency['rate'];
                                        }
                                    } elseif ($currency['isdefault'] == 1) {
                                        $selected = $currency['id'];
                                        $s_attrs['data-base'] = $currency['id'];
                                        $rate = $currency['rate'];
                                    }
                                }
                                ?>
                                <?php
                                echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'purchase_order_currency', $selected, do_action('purchase_order_currency_disabled', $s_attrs));
                                ?>
                                <?php $value = (isset($estimate) ? $estimate->currency_rate : $rate); ?>
                                <?php echo render_input('currency_rate', 'purchase_order_currency_rate', $value); ?>
                                <?php
                                $selected = '';
                                $s_attrs = array('data-show-subtext' => true);
                                foreach ($statuses as $status) {
                                    if (isset($estimate)) {
                                        if ($status['id'] == $estimate->status) {
                                            $selected = $status['id'];
                                            $s_attrs['disabled'] = true;
                                        }
                                    } else {
                                        $selected = 1;
                                    }
                                }
                                ?>
                                <?php
                                echo render_select('status', $statuses, array('id', 'name'), 'purchase_order_status', $selected, do_action('purchase_order_status_disabled', $s_attrs));
                                ?>
                                <?php
                                $selected = '';
                                $s_attrs = array('data-show-subtext' => true);
                                foreach ($payment_terms as $payment_term) {
                                    if (isset($estimate)) {
                                        if ($payment_term['id'] == $estimate->payment_term) {
                                            $selected = $payment_term['id'];
                                        }
                                    }
                                }
                                ?>
                                <?php
                                echo render_select('payment_term', $payment_terms, array('id', 'name'), 'purchase_order_payment_term', $selected, do_action('purchase_order_payment_term_disabled', $s_attrs));
                                ?>
                                <?php
                                $selected = '';
                                $s_attrs = array('data-show-subtext' => true);
                                foreach ($shipment_terms as $shipment_term) {
                                    if (isset($estimate)) {
                                        if ($shipment_term['id'] == $estimate->shipment_term) {
                                            $selected = $shipment_term['id'];
                                        }
                                    }
                                }
                                ?>
                                <?php
                                echo render_select('shipment_term', $shipment_terms, array('id', 'name'), 'purchase_order_shipment_term', $selected, do_action('purchase_order_shipment_term_disabled', $s_attrs));
                                ?>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar bottom-transaction text-right">
                            <p class="no-mbot pull-left mtop5 btn-toolbar-notice"><?php echo _l('include_purchase_order_items_merge_field_help', '<b>{purchase_order_items}</b>'); ?></p>
                            <button type="button"
                                    class="btn btn-info mleft10 proposal-form-submit save-and-send transaction-submit">
                                <?php echo _l('save_and_send'); ?>
                            </button>
                            <button class="btn btn-info mleft5 proposal-form-submit transaction-submit" type="button">
                                <?php echo _l('submit'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <?php $this->load->view('admin/estimates/_add_edit_items', ['type' => 'purchase-order']); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
            <?php $this->load->view('admin/invoice_items/item'); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        validate_purchase_order_form();
        init_currency_symbol();
        init_ajax_warehouse_search_by_customer_id();
    });
</script>
</body>
</html>
