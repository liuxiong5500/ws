<!-- opportunity Tasks -->
<?php
    if($opportunity->settings->hide_opportunity_tasks_on_main_tasks_table == '1') {
        echo '<i class="fa fa-exclamation fa-2x pull-left" data-toggle="tooltip" data-title="'._l('opportunity_hide_tasks_settings_info').'"></i>';
    }
?>
<div class="tasks-table">
    <?php init_relation_tasks_table(array( 'data-new-rel-id'=>$opportunity->id,'data-new-rel-type'=>'opportunity')); ?>
</div>
