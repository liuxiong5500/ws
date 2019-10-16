<p><?php echo _l('opportunity_note_private'); ?></p>
<hr />
<?php echo form_open(admin_url('opportunities/save_note/'.$opportunity->id)); ?>
<?php echo render_textarea('content','',$staff_notes,array(),array(),'','tinymce'); ?>
<button type="submit" class="btn btn-info"><?php echo _l('opportunity_save_note'); ?></button>
<?php echo form_close(); ?>
