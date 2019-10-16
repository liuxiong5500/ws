<?php init_head(); ?>
<div id="wrapper" class="customer_profile">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (isset($supplier) && $supplier->registration_confirmed == 0 && is_admin()) { ?>
                    <div class="alert alert-warning">
                        <?php echo _l('supplier_requires_registration_confirmation'); ?>
                        <br/>
                        <a href="<?php echo admin_url('suppliers/confirm_registration/' . $supplier->id); ?>"><?php echo _l('confirm_registration'); ?></a>
                    </div>
                <?php } else if (isset($supplier) && $supplier->active == 0 && $supplier->registration_confirmed == 1) { ?>
                    <div class="alert alert-warning">
                        <?php echo _l('supplier_inactive_message'); ?>
                        <br/>
                        <a href="<?php echo admin_url('suppliers/mark_as_active/' . $supplier->id); ?>"><?php echo _l('mark_as_active'); ?></a>
                    </div>
                <?php } ?>
            </div>
            <?php if ($group == 'profile') { ?>
                <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
                    <button class="btn btn-info only-save supplier-form-submiter">
                        <?php echo _l('submit'); ?>
                    </button>
                    <?php if (!isset($supplier)) { ?>
                        <button class="btn btn-info save-and-add-contact supplier-form-submiter">
                            <?php echo _l('save_supplier_and_add_contact'); ?>
                        </button>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if (isset($supplier)) { ?>
                <div class="col-md-3">
                    <div class="panel_s mbot5">
                        <div class="panel-body padding-10">
                            <h4 class="bold">
                                <div class="btn-group pull-left mright10">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                                       aria-expanded="false">
                                        <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left">
                                        <li>
                                            <a href="<?php echo admin_url('suppliers/delete/' . $supplier->id); ?>"
                                               class="text-danger delete-text _delete"><i
                                                    class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                #<?php echo $supplier->id . ' ' . $title; ?>
                            </h4>
                        </div>
                    </div>
                    <?php $this->load->view('admin/suppliers/tabs'); ?>
                </div>
            <?php } ?>
            <div class="col-md-<?php if (isset($supplier)) {
                echo 9;
            } else {
                echo 12;
            } ?>">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (isset($supplier)) { ?>
                            <?php echo form_hidden('isedit'); ?>
                            <?php echo form_hidden('id', $supplier->id); ?>
                            <div class="clearfix"></div>
                        <?php } ?>
                        <div>
                            <div class="tab-content">
                                <?php $this->load->view('admin/suppliers/groups/' . $group); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($group == 'profile') { ?>
            <div class="btn-bottom-pusher"></div>
        <?php } ?>
    </div>
</div>
<?php init_tail(); ?>
<?php if (!empty(get_option('google_api_key')) && !empty($supplier->latitude) && !empty($supplier->longitude)) { ?>
    <script>
        var latitude = '<?php echo $supplier->latitude; ?>';
        var longitude = '<?php echo $supplier->longitude; ?>';
        var mapMarkerTitle = '<?php echo $supplier->company; ?>';
    </script>
    <?php echo app_script('assets/js', 'map.js'); ?>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=<?php echo get_option('google_api_key'); ?>&callback=initMap"></script>
<?php } ?>
<?php $this->load->view('admin/suppliers/supplier_js'); ?>
</body>
</html>
