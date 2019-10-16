<div class="horizontal-scrollable-tabs">
    <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
    <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#general" aria-controls="general" role="tab"
                   data-toggle="tab"><?php echo _l('settings_purchase_order_heading_general'); ?></a>
            </li>
        </ul>
    </div>
</div>
<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="general">
        <div class="form-group">
            <label class="control-label"
                   for="purchase_order_prefix"><?php echo _l('settings_purchase_order_prefix'); ?></label>
            <input title="<?php echo _l('settings_purchase_order_prefix'); ?>" type="text" name="settings[purchase_order_prefix]" class="form-control"
                   value="<?php echo get_option('purchase_order_prefix'); ?>">
        </div>
    </div>
</div>
