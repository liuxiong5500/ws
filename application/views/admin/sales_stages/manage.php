<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal"
                               data-target="#sales_stage_modal"><?php echo _l('new_sales_stage'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('sales_stage_list_name'),
                            _l('sales_stage_color_list_name'),
                            _l('options'),
                        ), 'sales_stages'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sales_stage_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('sales_stage_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('sales_stage_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/sales_stages/manage', array('id' => 'sales_stage_form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'sales_stage_add_edit_description', '', 'text'); ?>
                        <?php echo render_color_picker('color', _l('sales_stage_color_add_edit'), '#000000'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-sales_stages', window.location.href, [1], [1]);
        _validate_form($('form'), {
            name: {required: true}
        }, manage_sales_stages);

        $('#sales_stage_modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id');
            $('#sales_stage_modal input[name="name"]').val('');
            $('#sales_stage_modal .colorpicker-input').colorpicker('setValue', '#000000');
            $('#sales_stage_modal input[name="id"]').val('');
            $('#sales_stage_modal .add-title').removeClass('hide');
            $('#sales_stage_modal .edit-title').addClass('hide');

            if (typeof (id) !== 'undefined') {
                $('input[name="id"]').val(id);
                var name = $(button).parents('tr').find('td').eq(0).find('span.name').text();
                var color = $(button).parents('tr').find('td').eq(1).find('span.color').text();
                $('#sales_stage_modal .add-title').addClass('hide');
                $('#sales_stage_modal .edit-title').removeClass('hide');
                $('#sales_stage_modal input[name="name"]').val(name);
                $('#sales_stage_modal .colorpicker-input').colorpicker('setValue', color);
            }
        });
    });

    /* CURRENCY MANAGE FUNCTIONS */
    function manage_sales_stages(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                $('.table-sales_stages').DataTable().ajax.reload();
                alert_float('success', response.message);
            }
            $('#sales_stage_modal').modal('hide');
        });
        return false;
    }

</script>
</body>
</html>
