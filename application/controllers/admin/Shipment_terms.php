<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shipment_terms extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('shipment_terms_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('shipment_terms');
        }
        $data['title'] = _l('shipment_terms');
        $this->load->view('admin/shipment_terms/manage', $data);
    }

    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $success = $this->shipment_terms_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('shipment_term'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $success = $this->shipment_terms_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('shipment_term'));
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
            redirect(admin_url('shipment_terms'));
        }
        $response = $this->shipment_terms_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('shipment_term_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('shipment_term')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('shipment_term_lowercase')));
        }
        redirect(admin_url('shipment_terms'));
    }
}
