<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php echo form_open($this->uri->uri_string(), array( 'id' => 'process-form', 'class' => '_transaction_form process-form' )); ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <?php $value = (isset($process) ? $process->name : ''); ?>
                                <?php echo render_input('name', 'process_name', $value, 'text'); ?>
                                <?php $value = (isset($process) ? _d(date('Y-m-d', $process->created_at)) : _d(date('Y-m-d'))); ?>
                            </div>
                            <div class="col-md-6">

                                <?php
                                $selected = '';
                                $s_attrs = array( 'data-show-subtext' => true );
                                foreach($statuses as $status){
                                    if(isset($process)){
                                        if($status['id'] == $process->status){
                                            $selected = $status['id'];
                                            $s_attrs['disabled'] = true;
                                        }
                                    }else{
                                        $selected = 1;
                                    }
                                }
                                ?>
                                <?php echo render_textarea('remarks', 'process_remarks', $process->remarks); ?>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar bottom-transaction text-right">
                            <button class="btn btn-info mleft5 proposal-form-submit transaction-submit" type="button">
                                <?php echo _l('submit'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <?php $this->load->view('admin/process/_add_edit_items'); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        validate_process_form();
        $('#sub_process_item_select').change(function(e){
            let processId = $(this).val();
            requestGetJSON('process/get_sub_process_by_id/' + processId).done(function(response){
                let html = '<tr class="process_item_line item"><td><input type="hidden" name="process_item[id][]" value="'+response.id+'"><input type="text" name="process_item[name][]" class="form-control" value="'+response.name+'" disabled></td><td><input type="text" name="process_item[remarks][]" class="form-control" value="'+response.remarks+'" disabled></td><td><a class="btn btn-danger btn-delete">删除</a></td></tr>';
                $('#process_item_list_table').append(html);
            });

        });

        $('#process_item_list_table').on('click', '.btn-delete', function (e) {
            let el = $(this).parent().parent();
            el.remove();
        });
    });
</script>
</body>
</html>
