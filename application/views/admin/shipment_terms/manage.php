<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal"
                               data-target="#shipment_term_modal"><?php echo _l('new_shipment_term'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('shipment_term_list_name'),
                            _l('options'),
                        ), 'shipment_terms'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="shipment_term_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('shipment_term_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('shipment_term_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/shipment_terms/manage', array('id' => 'shipment_term_form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'name', ''); ?>
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
        initDataTable('.table-shipment_terms', window.location.href, [1], [1]);
        _validate_form($('form'), {
            name: {required: true},
        }, manage_shipment_terms);
        $('#shipment_term_modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id');
            $('#shipment_term_modal input[name="id"]').val('');
            $('#shipment_term_modal input[name="name"]').val('');
            $('#shipment_term_modal .add-title').removeClass('hide');
            $('#shipment_term_modal .edit-title').addClass('hide');
            if (typeof (id) !== 'undefined') {
                $('#shipment_term_modal input[name="id"]').val(id);
                var name = $(button).parents('tr').find('td').eq(0).find('span.name').text();
                $('#shipment_term_modal .add-title').addClass('hide');
                $('#shipment_term_modal .edit-title').removeClass('hide');
                $('#shipment_term_modal input[name="name"]').val(name);
            }
        });
    });

    function manage_shipment_terms(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                $('.table-shipment_terms').DataTable().ajax.reload();
                alert_float('success', response.message);
            }
            $('#shipment_term_modal').modal('hide');
        });
        return false;
    }
</script>
</body>
</html>
