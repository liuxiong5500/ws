<?php if (isset($client)) { ?>
    <h4 class="customer-profile-group-heading"><?php echo _l('customer_warehouses'); ?></h4>
    <div class="inline-block new-warehouse-wrapper">
        <a href="#" onclick="warehouse(<?php echo $client->userid; ?>); return false;"
           class="btn btn-info new-warehouse mbot25"><?php echo _l('new_warehouse'); ?></a>
    </div>
    <?php
    $table_data = array(_l('warehouses_lise_name'));
    $table_data = array_merge($table_data, array(_l('address'), _l('contact'), _l('telephone'), _l('options')));
    echo render_datatable($table_data, 'customerwarehouses'); ?>
<?php } ?>
<div id="warehouse_data"></div>
