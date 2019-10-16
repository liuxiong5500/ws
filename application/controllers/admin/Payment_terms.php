<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_terms extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payment_terms_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('payment_terms');
        }
        $data['title'] = _l('payment_terms');
        $this->load->view('admin/payment_terms/manage', $data);
    }

    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $success = $this->payment_terms_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('payment_term'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $success = $this->payment_terms_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('payment_term'));
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
            redirect(admin_url('payment_terms'));
        }
        $response = $this->payment_terms_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('payment_term_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('payment_term')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_term_lowercase')));
        }
        redirect(admin_url('payment_terms'));
    }
}
