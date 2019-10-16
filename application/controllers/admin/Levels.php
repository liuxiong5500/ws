<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Levels extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('levels_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('levels');
        }
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();

        $this->load->model('positions_model');
        $data['positions'] = $this->positions_model->get();
        $data['title'] = _l('levels');
        $this->load->view('admin/levels/manage', $data);
    }

    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $success = $this->levels_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('level'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $success = $this->levels_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('level'));
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
            redirect(admin_url('levels'));
        }
        $response = $this->levels_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('level_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('level')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('level_lowercase')));
        }
        redirect(admin_url('levels'));
    }
}