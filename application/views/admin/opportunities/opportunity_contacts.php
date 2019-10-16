<a href="#"
   onclick="opportunity_contact_request('<?php echo admin_url('opportunities/get_contact?opportunity_id=' . $opportunity->id); ?>');return false;"
   class="btn btn-info mbot25"><?php echo _l('opportunity_add_contacts'); ?></a>
<?php $this->load->view('admin/opportunities/contacts_table_html', array('url' => admin_url('opportunities/contacts_table?opportunity_id=' . $opportunity->id))); ?>
