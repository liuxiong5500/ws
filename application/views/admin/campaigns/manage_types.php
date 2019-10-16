<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_type(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('campaigns_new_type'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php if(count($types) > 0){ ?>
                            <table class="table dt-table scroll-responsive">
                                <thead>
                                <th><?php echo _l('campaigns_types_table_name'); ?></th>
                                <th><?php echo _l('options'); ?></th>
                                </thead>
                                <tbody>
                                <?php foreach($types as $type){ ?>
                                    <tr>
                                        <td><a href="#" onclick="edit_type(this,<?php echo $type['id']; ?>); return false" data-name="<?php echo $type['name']; ?>"><?php echo $type['name']; ?></a><br />
                                            <span class="text-muted">
                                        <?php echo _l('campaigns_table_total',total_rows('tblcampaigns',array('type_id'=>$type['id']))); ?>
                                    </span>
                                        </td>
                                        <td>
                                            <a href="#" onclick="edit_type(this,<?php echo $type['id']; ?>); return false" data-name="<?php echo $type['name']; ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                            <a href="<?php echo admin_url('campaigns/delete_type/'.$type['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <p class="no-margin"><?php echo _l('campaigns_types_not_found'); ?></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="type" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('campaigns/type')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('campaign_edit_type_heading'); ?></span>
                    <span class="add-title"><?php echo _l('campaign_new_type_heading'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name','campaigns_type_add_edit_name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function(){
        _validate_form($('form'),{name:'required'},manage_campaigns_types);
        $('#type').on('hidden.bs.modal', function(event) {
            $('#additional').html('');
            $('#type input[name="name"]').val('');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
    });
    function manage_campaigns_types(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            window.location.reload();
        });
        return false;
    }
    function new_type(){
        $('#type').modal('show');
        $('.edit-title').addClass('hide');
    }
    function edit_type(invoker,id){
        var name = $(invoker).data('name');
        $('#additional').append(hidden_input('id',id));
        $('#type input[name="name"]').val(name);
        $('#type').modal('show');
        $('.add-title').addClass('hide');
    }
</script>
</body>
</html>
