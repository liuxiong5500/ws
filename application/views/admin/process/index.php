<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s mbot10">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('process/edit'); ?>"
                               class="btn btn-info mright5 test pull-left display-block">
                                <?php echo _l('new_process'); ?></a>
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
                                    _l('process_name'),
                                    _l('process_remarks'),
                                    //_l('process_status'),
                                    _l('created_at'),
                                );

                                foreach ($_table_data as $_t) {
                                    array_push($table_data, $_t);
                                }

                                render_datatable($table_data, 'process-master');
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
        initDataTable('.table-process-master', admin_url + 'process/process_mastertable');
    });
</script>
</body>
</html>
