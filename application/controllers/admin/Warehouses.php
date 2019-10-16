<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Warehouses extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('customer_warehouses_model');
    }

    public function table($clientid = '')
    {
        $this->app->get_table_data('warehouses', [
            'clientid' => $clientid,
        ]);
    }

    public function delete($id)
    {
        $warehouse = $this->customer_warehouses_model->get($id);
        if (!$id) {
            redirect(admin_url('clients') . '/client/' . $warehouse->clientid . '?group=warehouses');
        }
        $this->customer_warehouses_model->delete($id);
        redirect(admin_url('clients') . '/client/' . $warehouse->clientid . '?group=warehouses');
    }
}
