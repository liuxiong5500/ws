<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
            <div class="panel_s">
              <div class="panel-body">
              <div class="_buttons">
              <?php if(has_permission('opportunities','','create')){ ?>
                <a href="<?php echo admin_url('opportunities/opportunity'); ?>" class="btn btn-info pull-left display-block">
                  <?php echo _l('new_opportunity'); ?>
                </a>
              <?php } ?>
              <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-filter" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right width300">
                  <li>
                    <a href="#" data-cview="all" onclick="dt_custom_view('','.table-opportunities',''); return false;">
                      <?php echo _l('expenses_list_all'); ?>
                    </a>
                  </li>
                  <?php
                  // Only show this filter if user has permission for opportunities view otherwisde wont need this becuase by default this filter will be applied
                  if(has_permission('opportunities','','view')){ ?>
                  <li>
                    <a href="#" data-cview="my_opportunities" onclick="dt_custom_view('my_opportunities','.table-opportunities','my_opportunities'); return false;">
                      <?php echo _l('home_my_opportunities'); ?>
                    </a>
                  </li>
                  <?php } ?>
                  <li class="divider"></li>
                  <?php foreach($statuses as $status){ ?>
                    <li class="<?php if($status['filter_default'] == true && !$this->input->get('status') || $this->input->get('status') == $status['id']){echo 'active';} ?>">
                      <a href="#" data-cview="<?php echo 'opportunity_status_'.$status['id']; ?>" onclick="dt_custom_view('opportunity_status_<?php echo $status['id']; ?>','.table-opportunities','opportunity_status_<?php echo $status['id']; ?>'); return false;">
                        <?php echo $status['name']; ?>
                      </a>
                    </li>
                    <?php } ?>
                  </ul>
                </div>
                <div class="clearfix"></div>
                <hr class="hr-panel-heading" />
              </div>
               <div class="row mbot15">
                <div class="col-md-12">
                  <h4 class="no-margin"><?php echo _l('opportunities_summary'); ?></h4>
                  <?php
                  $_where = '';
                  if(!has_permission('opportunities','','view')){
                    $_where = 'id IN (SELECT opportunity_id FROM tblopportunitymembers WHERE staff_id='.get_staff_user_id().')';
                  }
                  ?>
                </div>
                <div class="_filters _hidden_inputs">
                  <?php
                  echo form_hidden('my_opportunities');
                  foreach($statuses as $status){
                   $value = $status['id'];
                     if($status['filter_default'] == false && !$this->input->get('status')){
                        $value = '';
                     } else if($this->input->get('status')) {
                        $value = ($this->input->get('status') == $status['id'] ? $status['id'] : "");
                     }
                     echo form_hidden('opportunity_status_'.$status['id'],$value);
                    ?>
                   <div class="col-md-2 col-xs-6 border-right">
                    <?php $where = ($_where == '' ? '' : $_where.' AND ').'status = '.$status['id']; ?>
                    <a href="#" onclick="dt_custom_view('opportunity_status_<?php echo $status['id']; ?>','.table-opportunities','opportunity_status_<?php echo $status['id']; ?>',true); return false;">
                     <h3 class="bold"><?php echo total_rows('tblopportunities',$where); ?></h3>
                     <span style="color:<?php echo $status['color']; ?>" opportunity-status-<?php echo $status['id']; ?>">
                     <?php echo $status['name']; ?>
                     </span>
                   </a>
                 </div>
                 <?php } ?>
               </div>
             </div>
             <div class="clearfix"></div>
              <hr class="hr-panel-heading" />
             <?php echo form_hidden('custom_view'); ?>
             <?php $this->load->view('admin/opportunities/table_html'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('admin/opportunities/copy_settings'); ?>
<?php init_tail(); ?>
<script>
$(function(){
     var opportunitiesServerParams = {};

     $.each($('._hidden_inputs._filters input'),function(){
         opportunitiesServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
     });

     initDataTable('.table-opportunities', admin_url+'opportunities/table', undefined, undefined, opportunitiesServerParams, <?php echo do_action('opportunities_table_default_order',json_encode(array(5,'asc'))); ?>);

     init_ajax_search('customer', '#clientid_copy_opportunity.ajax-search');
});
</script>
</body>
</html>
