<?php
    $this->load->view('admin/tickets/summary',array('opportunity_id'=>$opportunity->id));
    echo form_hidden('opportunity_id',$opportunity->id);
    echo '<div class="clearfix"></div>';
    if(((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member())){
        echo '<a href="'.admin_url('tickets/add?opportunity_id='.$opportunity->id).'" class="mbot20 btn btn-info">'._l('new_ticket').'</a>';
    }
    echo AdminTicketsTableStructure('tickets-table');
?>
