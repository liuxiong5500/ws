<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal"
                               data-target="#level_modal"><?php echo _l('new_level'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('level_department_list_name'),
                            _l('position_list_name'),
                            _l('level'),
                            _l('level_from_amount'),
                            _l('level_to_amount'),
                            _l('level_gp'),
                            _l('options'),
                        ), 'levels'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="level_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('level_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('level_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/levels/manage', array('id' => 'level_form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_select('departmentid', $departments, ['departmentid', 'name'], 'level_department', ''); ?>
                        <?php echo render_select('positionid', $positions, ['id', 'name'], 'level_position', ''); ?>
                        <?php echo render_input('level', 'level', '', 'number'); ?>
                        <?php echo render_input('fromamount', 'level_add_edit_fromamount', '', 'number'); ?>
                        <?php echo render_input('toamount', 'level_add_edit_toamount', '', 'number'); ?>
                        <?php echo render_input('gp', 'level_add_edit_gp', '', 'number'); ?>
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
        initDataTable('.table-levels', window.location.href, [6], [6]);
        _validate_form($('form'), {
            departmentid: {required: true},
            positionid: {required: true},
            level: {required: true},
            fromamount: {required: true},
            toamount: {required: true},
            gp: {required: true},
        }, manage_levels);

        $('#level_modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id');
            $('#level_modal input[name="id"]').val('');
            $('#level_modal input[name="level"]').val('');
            $('#level_modal select[name="departmentid"]').selectpicker('val', '').change();
            $('#level_modal select[name="positionid"]').selectpicker('val', '').change();
            $('#level_modal input[name="fromamount"]').val('');
            $('#level_modal input[name="toamount"]').val('');
            $('#level_modal input[name="gp"]').val('');
            $('#level_modal .add-title').removeClass('hide');
            $('#level_modal .edit-title').addClass('hide');
            if (typeof (id) !== 'undefined') {
                $('#level_modal input[name="id"]').val(id);
                var department = $(button).parents('tr').find('td').eq(0).find('span.name').data('id');
                var position = $(button).parents('tr').find('td').eq(1).find('span.name').data('id');
                var level = $(button).parents('tr').find('td').eq(2).text();
                var fromamount = $(button).parents('tr').find('td').eq(3).text();
                var toamount = $(button).parents('tr').find('td').eq(4).text();
                var gp = $(button).parents('tr').find('td').eq(5).text();
                $('#level_modal .add-title').addClass('hide');
                $('#level_modal .edit-title').removeClass('hide');
                $('#level_modal input[name="level"]').val(level);
                $('#level_modal select[name="departmentid"]').selectpicker('val', department).change();
                $('#level_modal select[name="positionid"]').selectpicker('val', position).change();
                $('#level_modal input[name="fromamount"]').val(fromamount);
                $('#level_modal input[name="toamount"]').val(toamount);
                $('#level_modal input[name="gp"]').val(gp);
            }
        });
    });

    /* CURRENCY MANAGE FUNCTIONS */
    function manage_levels(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                $('.table-levels').DataTable().ajax.reload();
                alert_float('success', response.message);
            }
            $('#level_modal').modal('hide');
        });
        return false;
    }

</script>
</body>
</html>
