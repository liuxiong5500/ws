<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal"
                               data-target="#supplier_group_modal"><?php echo _l('new_supplier_group'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('supplier_group_name'),
                            _l('options'),
                        ), 'supplier-groups'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/suppliers/supplier_group'); ?>
<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-supplier-groups', window.location.href, [1], [1]);
    });
</script>
</body>
</html>
