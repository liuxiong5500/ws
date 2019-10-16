<?php init_head(); ?>
<div id="wrapper">
    <div class="panel_s">
        <div class="panel-body">
            <table class="table dt-table" data-order-col="2" data-order-type="desc">
                <thead>
                <th><?php echo _l('purchase_order_date'); ?></th>
                <th><?php echo _l('finished'); ?></th>
                <th><?php echo _l('shipped'); ?></th>
                </thead>
                <tbody>
                <?php foreach ($logs as $log) { ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($log['p_date'])) ?></td>
                        <td><?php echo $log['finished']; ?></td>
                        <td><?php echo $log['shipped']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {

    });
</script>
</body>
</html>
