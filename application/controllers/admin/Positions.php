<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Positions extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('positions_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('positions');
        }
        $data['title'] = _l('positions');
        $this->load->view('admin/positions/manage', $data);
    }

    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $success = $this->positions_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('position'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $success = $this->positions_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('position'));
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
            redirect(admin_url('positions'));
        }
        $response = $this->positions_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('position_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('position')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('position_lowercase')));
        }
        redirect(admin_url('positions'));
    }
}