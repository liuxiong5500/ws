<!-- Modal Contact -->
<div class="modal fade" id="warehouse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/clients/warehouse/' . $customer_id . '/' . $warehouse_id, array('id' => 'warehouse-form', 'autocomplete' => 'off')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $title; ?><br/></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- // For email exist check -->
                        <?php echo form_hidden('id', $warehouse_id); ?>
                        <?php echo form_hidden('clientid', $customer_id); ?>
                        <?php $value = (isset($warehouse) ? $warehouse->name : ''); ?>
                        <?php echo render_input('name', 'warehouse_name', $value); ?>
                        <?php $value = (isset($warehouse) ? $warehouse->address : ''); ?>
                        <?php echo render_input('address', 'warehouse_address', $value); ?>
                        <?php $value = (isset($warehouse) ? $warehouse->contact : ''); ?>
                        <?php echo render_input('contact', 'warehouse_contact', $value); ?>
                        <?php $value = (isset($warehouse) ? $warehouse->telephone : ''); ?>
                        <?php echo render_input('telephone', 'warehouse_telephone', $value); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" data-loading-text="<?php echo _l('wait_text'); ?>"
                        autocomplete="off" data-form="#warehouse-form"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
