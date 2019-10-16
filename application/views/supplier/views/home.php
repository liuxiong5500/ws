<div class="panel_s">
    <div class="panel-body">
        <h4 class="no-margin"><?php echo _l('supplier_my_purchase_orders'); ?></h4>
    </div>
</div>
<div class="panel_s">
    <div class="panel-body">
        <table class="table dt-table table-purchase-orders" data-order-col="2" data-order-type="desc">
            <thead>
            <th><?php echo _l('purchase_order_number'); ?></th>
            <th><?php echo _l('client'); ?></th>
            <th><?php echo _l('warehouse'); ?></th>
            <th><?php echo _l('purchase_order_date'); ?></th>
            <th><?php echo _l('currency'); ?></th>
            <th><?php echo _l('purchase_order_currency_rate'); ?></th>
            <th><?php echo _l('total'); ?></th>
            <th><?php echo _l('purchase_order_status'); ?></th>
            <th><?php echo _l('created_at'); ?></th>
            </thead>
            <tbody>
            <?php foreach ($orders as $order) { ?>
                <tr>
                    <td>
                        <a href="<?php echo site_url('suppliers/order/' . $order['id']); ?>"><?php echo $order['order_number']; ?></a>
                    </td>
                    <td><?php echo $order['client_company']; ?></td>
                    <td><?php echo $order['warehouse_name']; ?></td>
                    <td><?php echo _d(date('Y-m-d', strtotime($order['po_date']))); ?></td>
                    <td><?php echo $order['currency_name']; ?></td>
                    <td><?php echo $order['currency_rate']; ?></td>
                    <td><?php echo $order['total']; ?></td>
                    <td><?php echo get_purchase_order_status_by_id($order['status']); ?></td>
                    <td><?php echo $order['created_at']; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
