<div class="modal fade" id="supplier_group_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button group="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('supplier_group_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('supplier_group_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/suppliers/group', array('id' => 'supplier-group-modal')); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'supplier_group_name'); ?>
                        <?php echo form_hidden('id'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button group="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button group="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    window.addEventListener('load', function () {
        _validate_form($('#supplier-group-modal'), {
            name: 'required'
        }, manage_supplier_groups);

        $('#supplier_group_modal').on('show.bs.modal', function (e) {
            var invoker = $(e.relatedTarget);
            var group_id = $(invoker).data('id');
            $('#supplier_group_modal .add-title').removeClass('hide');
            $('#supplier_group_modal .edit-title').addClass('hide');
            $('#supplier_group_modal input[name="id"]').val('');
            $('#supplier_group_modal input[name="name"]').val('');
            // is from the edit button
            if (typeof (group_id) !== 'undefined') {
                $('#supplier_group_modal input[name="id"]').val(group_id);
                $('#supplier_group_modal .add-title').addClass('hide');
                $('#supplier_group_modal .edit-title').removeClass('hide');
                $('#supplier_group_modal input[name="name"]').val($(invoker).parents('tr').find('td').eq(0).text());
            }
        });
    });

    function manage_supplier_groups(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                if ($.fn.DataTable.isDataTable('.table-supplier-groups')) {
                    $('.table-supplier-groups').DataTable().ajax.reload();
                }
                if ($('body').hasClass('dynamic-create-groups') && typeof (response.id) != 'undefined') {
                    var groups = $('select[name="groups_in[]"]');
                    groups.prepend('<option value="' + response.id + '">' + response.name + '</option>');
                    groups.selectpicker('refresh');
                }
                alert_float('success', response.message);
            }
            $('#supplier_group_modal').modal('hide');
        });
        return false;
    }

</script>
