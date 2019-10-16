<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_stages extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('sales_stages_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('sales_stages');
        }
        $data['title'] = _l('sales_stages');
        $this->load->view('admin/sales_stages/manage', $data);
    }

    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $success = $this->sales_stages_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('sales_stage'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $success = $this->sales_stages_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('sales_stage'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('sales_stages'));
        }
        $response = $this->sales_stages_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('sales_stage_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('sales_stage')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('sales_stage_lowercase')));
        }
        redirect(admin_url('sales_stages'));
    }
}
