<div class="panel_s">
    <div class="panel-body">
        <?php echo form_open_multipart('suppliers/add_item', array( 'autocomplete' => 'off' )); ?>
        <?php echo form_hidden('item_id', $item_id); ?>
        <?php echo form_hidden('process_item_id', $process_item_id); ?>
        <?php echo render_date_input('p_date', 'purchase_order_date'); ?>
        <?php echo render_input('finished', 'finished', '', 'number'); ?>
        <?php echo render_input('shipped', 'shipped', '', 'number'); ?>
        <?php
        echo render_select('process_sub_id', $process_list, array( 'id', 'name' ), 'process_name');
        ?>
        <div class="form-group">
            <button type="submit"
                    class="btn btn-info"><?php echo _l('item_logs_add'); ?></button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<div class="panel_s">
    <div class="panel-body">
        <table class="table dt-table" data-order-col="2" data-order-type="desc">
            <caption>Product Process Count</caption>
            <thead>
            <th><?php echo _l('process_name'); ?></th>
            <th><?php echo _l('finished'); ?></th>
            <th><?php echo _l('shipped'); ?></th>
            </thead>
            <tbody>
            <?php foreach($process_list as $process_info){ ?>
                <tr>
                    <td><?php echo $process_info['name'];?></td>
                    <td><?php echo $process_count[$process_info['id']]['finished'];?></td>
                    <td><?php echo $process_count[$process_info['id']]['shipped'];?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <br/>
    <div class="panel-body">
        <table class="table dt-table" data-order-col="2" data-order-type="desc">
            <thead>
            <th><?php echo _l('purchase_order_date'); ?></th>
            <th><?php echo _l('finished'); ?></th>
            <th><?php echo _l('shipped'); ?></th>
            <th><?php echo _l('process_name'); ?></th>
            </thead>
            <tbody>
            <?php foreach($logs as $log){ ?>
                <tr>
                    <td><?php echo date('Y-m-d', strtotime($log['p_date'])) ?></td>
                    <td><?php echo $log['finished']; ?></td>
                    <td><?php echo $log['shipped']; ?></td>
                    <td><?php echo $log['process_sub_name'] ?: ''; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
