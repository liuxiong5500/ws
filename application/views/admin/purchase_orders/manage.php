<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s mbot10">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('purchase_orders/order'); ?>"
                               class="btn btn-info mright5 test pull-left display-block">
                                <?php echo _l('new_purchase_order'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" id="small-table">
                        <div class="panel_s">
                            <div class="panel-body">
                                <?php
                                $table_data = array();
                                $_table_data = array(
                                    _l('order_number'),
                                    _l('supplier'),
                                    _l('client'),
                                    _l('warehouse'),
                                    _l('purchase_order_proposal_estimate_number'),
                                    _l('purchase_order_date'),
                                    _l('currency'),
                                    _l('purchase_order_currency_rate'),
                                    _l('purchase_order_status'),
                                    _l('total'),
                                    _l('created_at'),
                                );

                                foreach ($_table_data as $_t) {
                                    array_push($table_data, $_t);
                                }

                                render_datatable($table_data, 'purchase-orders');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-purchase-orders', admin_url + 'purchase_orders/table', [10], [10]);
    });
</script>
</body>
</html>
