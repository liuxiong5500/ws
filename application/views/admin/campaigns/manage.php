<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" class="btn btn-info pull-left" data-toggle="modal"
                               data-target="#campaign_modal"><?php echo _l('new_campaign'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('campaign_list_name'),
                            _l('campaign_list_type'),
                            _l('campaign_list_date_from'),
                            _l('campaign_list_date_to'),
                            _l('campaign_list_budget_cost'),
                            _l('campaign_list_expected_sale'),
                            _l('options'),
                        ), 'campaigns'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="campaign_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('campaign_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('campaign_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/campaigns/manage', array('id' => 'campaign_form')); ?>
            <?php echo form_hidden('id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'campaign_add_edit_name', '', 'text'); ?>
                        <?php echo render_select('type_id', $types, array('id', 'name'), 'campaign_add_edit_type'); ?>
                        <?php echo render_datetime_input('date_from', 'campaign_add_edit_date_from', ''); ?>
                        <?php echo render_datetime_input('date_to', 'campaign_add_edit_date_to', ''); ?>
                        <?php echo render_input('budget_cost', 'campaign_add_edit_budget_cost', '', 'number'); ?>
                        <?php echo render_input('expected_sale', 'campaign_add_edit_expected_sale', '', 'number'); ?>
                        <?php echo render_textarea('description', 'campaign_add_edit_description', ''); ?>
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
        initDataTable('.table-campaigns', window.location.href, [1], [1]);
        _validate_form($('form'), {
            name: {required: true}
        }, manage_campaigns);

        $('#campaign_modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget)
            var id = button.data('id');
            $('#campaign_modal input[name="name"]').val('');
            $('#campaign_modal select[name="type_id"]').selectpicker('val', '').change();
            $('#campaign_modal input[name="date_from"]').val('');
            $('#campaign_modal input[name="date_to"]').val('');
            $('#campaign_modal input[name="budget_cost"]').val('');
            $('#campaign_modal input[name="expected_sale"]').val('');
            $('#campaign_modal textarea[name="description"]').val('');
            $('#campaign_modal input[name="id"]').val('');
            $('#campaign_modal .add-title').removeClass('hide');
            $('#campaign_modal .edit-title').addClass('hide');

            if (typeof (id) !== 'undefined') {
                $('input[name="id"]').val(id);
                var name = $(button).parents('tr').find('td').eq(0).find('span.name').text();
                var type_id = $(button).parents('tr').find('td').eq(1).find('span.name').data('id');
                var date_from = $(button).parents('tr').find('td').eq(2).text();
                var date_to = $(button).parents('tr').find('td').eq(3).text();
                var budget_cost = $(button).parents('tr').find('td').eq(4).text();
                var expected_sale = $(button).parents('tr').find('td').eq(5).text();
                var description = $(button).parents('tr').find('td').eq(0).find('span.name').data('description');
                $('#campaign_modal .add-title').addClass('hide');
                $('#campaign_modal .edit-title').removeClass('hide');
                $('#campaign_modal input[name="name"]').val(name);
                $('#campaign_modal select[name="type_id"]').selectpicker('val', type_id).change();
                $('#campaign_modal input[name="date_from"]').val(date_from);
                $('#campaign_modal input[name="date_to"]').val(date_to);
                $('#campaign_modal input[name="budget_cost"]').val(budget_cost);
                $('#campaign_modal input[name="expected_sale"]').val(expected_sale);
                $('#campaign_modal textarea[name="description"]').val(description);
            }
        });
    });

    /* CURRENCY MANAGE FUNCTIONS */
    function manage_campaigns(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                $('.table-campaigns').DataTable().ajax.reload();
                alert_float('success', response.message);
            }
            $('#campaign_modal').modal('hide');
        });
        return false;
    }

</script>
</body>
</html>
