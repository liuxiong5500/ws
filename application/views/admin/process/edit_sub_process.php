<div class="modal fade" id="sub_process_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('invoice_item_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('invoice_item_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/process/sub_edit', array( 'id' => 'sub_process_form' )); ?>
            <?php echo form_hidden('process_id'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning affect-warning hide">
                            <?php echo _l('changing_items_affect_warning'); ?>
                        </div>
                        <?php echo render_input('name', 'process_name'); ?>
                        <?php echo render_input('remarks', 'process_remarks'); ?>
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
<script>
    // Maybe in modal? Eq convert to invoice or convert proposal to estimate/invoice
    if (typeof(jQuery) != 'undefined') {
        init_item_js();
    } else {
        window.addEventListener('load', function () {
            var initItemsJsInterval = setInterval(function () {
                if (typeof(jQuery) != 'undefined') {
                    init_item_js();
                    clearInterval(initItemsJsInterval);
                }
            }, 1000);
        });
    }

    // Items add/edit
    function manage_invoice_items(form) {
        var data = $(form).serialize();

        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            }
            $('#sub_process_modal').modal('hide');
            $('.table-process-sub').DataTable().ajax.reload(null, false);
            // window.location.reload();
        }).fail(function (data) {
            alert_float('danger', data.responseText);
        });
        return false;
    }

    function init_item_js() {
        // Items modal show action
        $("body").on('show.bs.modal', '#sub_process_modal', function (event) {

            $('.affect-warning').addClass('hide');

            var $itemModal = $('#sub_process_modal');
            $('input[name="process_id"]').val('');
            $itemModal.find('input').not('input[type="hidden"]').val('');
            $itemModal.find('.add-title').removeClass('hide');
            $itemModal.find('.edit-title').addClass('hide');

            var id = $(event.relatedTarget).data('id');
            // If id found get the text from the datatable
            if (typeof (id) !== 'undefined') {

                $('.affect-warning').removeClass('hide');
                $('input[name="process_id"]').val(id);

                requestGetJSON('process/get_sub_process_by_id/' + id).done(function (response) {
                    $itemModal.find('input[name="name"]').val(response.name);
                    $itemModal.find('input[name="remarks"]').val(response.remarks);

                    $itemModal.find('.add-title').addClass('hide');
                    $itemModal.find('.edit-title').removeClass('hide');
                    validate_item_form();
                });

            }
        });

        validate_item_form();
    }

    function validate_item_form() {
        // Set validation for invoice item form
        _validate_form($('#sub_process_form'), {
            name: 'required'
        }, manage_invoice_items);
    }
</script>
