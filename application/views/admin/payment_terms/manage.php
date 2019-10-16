<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal"
                               data-target="#payment_term_modal"><?php echo _l('new_payment_term'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('payment_term_list_name'),
                            _l('deposit'),
                            _l('days'),
                            _l('options'),
                        ), 'payment_terms'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="payment_term_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('payment_term_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('payment_term_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/payment_terms/manage', array('id' => 'payment_term_form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'name', ''); ?>
                        <?php echo render_input('deposit', 'payment_term_add_edit_deposit', '', 'number'); ?>
                        <?php echo render_input('days', 'payment_term_add_edit_days', '', 'number'); ?>
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
        initDataTable('.table-payment_terms', window.location.href, [1], [1]);
        _validate_form($('form'), {
            name: {required: true},
        }, manage_payment_terms);
        $('#payment_term_modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id');
            $('#payment_term_modal input[name="id"]').val('');
            $('#payment_term_modal input[name="name"]').val('');
            $('#payment_term_modal input[name="deposit"]').val('');
            $('#payment_term_modal input[name="days"]').val('');
            $('#payment_term_modal .add-title').removeClass('hide');
            $('#payment_term_modal .edit-title').addClass('hide');
            if (typeof (id) !== 'undefined') {
                $('#payment_term_modal input[name="id"]').val(id);
                var name = $(button).parents('tr').find('td').eq(0).find('span.name').text();
                var deposit = $(button).parents('tr').find('td').eq(1).text();
                var days = $(button).parents('tr').find('td').eq(2).text();
                $('#payment_term_modal .add-title').addClass('hide');
                $('#payment_term_modal .edit-title').removeClass('hide');
                $('#payment_term_modal input[name="name"]').val(name);
                $('#payment_term_modal input[name="deposit"]').val(deposit);
                $('#payment_term_modal input[name="days"]').val(days);
            }
        });
    });

    function manage_payment_terms(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                $('.table-payment_terms').DataTable().ajax.reload();
                alert_float('success', response.message);
            }
            $('#payment_term_modal').modal('hide');
        });
        return false;
    }
</script>
</body>
</html>
