<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Campaigns extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('campaigns_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('campaigns');
        }
        $data['title'] = _l('campaigns');
        $data['types'] = $this->campaigns_model->get_type();
        $this->load->view('admin/campaigns/manage', $data);
    }

    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $success = $this->campaigns_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('campaign'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $success = $this->campaigns_model->edit($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('campaign'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function types()
    {
        $data['types'] = $this->campaigns_model->get_type();
        $data['title'] = 'Campaign types';
        $this->load->view('admin/campaigns/manage_types', $data);
    }

    public function type()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }

                $id = $this->campaigns_model->add_type($data);

                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('campaign_type')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : fales, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->campaigns_model->update_type($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('campaign_type')));
                }
            }
        }
    }

    public function delete_type($id)
    {
        if (!$id) {
            redirect(admin_url('campaigns/types'));
        }
        $response = $this->campaigns_model->delete_type($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('campaign_type_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('campaign_type')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('campaign_type_lowercase')));
        }
        redirect(admin_url('campaigns/types'));
    }
}
