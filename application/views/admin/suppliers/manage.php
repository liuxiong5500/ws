<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s mbot10">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('suppliers/supplier'); ?>"
                               class="btn btn-info mright5 test pull-left display-block">
                                <?php echo _l('new_supplier'); ?></a>
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
                                    '#',
                                    _l('suppliers_list_company'),
                                    _l('contact_primary'),
                                    _l('company_primary_email'),
                                    _l('suppliers_list_phone'),
                                    _l('supplier_active'),
                                    _l('supplier_groups'),
                                    _l('date_created'),
                                );

                                foreach ($_table_data as $_t) {
                                    array_push($table_data, $_t);
                                }

                                render_datatable($table_data, 'suppliers', [], [
                                    'data-last-order-identifier' => 'suppliers',
                                    'data-default-order' => get_table_last_order('suppliers'),
                                ]);
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
        initDataTable('.table-suppliers', admin_url + 'suppliers/table', [7], [7]);
    });
</script>
</body>
</html>
