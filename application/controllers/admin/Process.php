<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Process
 * @property Process_master_model $Process_master_model
 * @property Process_sub_model $Process_sub_model
 * @description
 * @version 1.0.0
 */
class Process extends Admin_controller
{
    protected $statuses;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Process_master_model');
        $this->load->model('Process_sub_model');
        $this->statuses = $this->Process_master_model->getStatuses();
    }

    public function index()
    {
        $this->load->view('admin/process/index', [ 'title' => _l('Process') ]);
    }

    public function view()
    {

    }

    public function edit($id = '')
    {
        if($this->input->post()){
            $process_data = $this->input->post();
            if(empty($id)){
                $id = $this->Process_master_model->add($process_data);
            }else{
                $this->Process_master_model->update($id, $process_data);
            }
            redirect(admin_url('process'));
        }else{
            $sub_process_list = $this->Process_sub_model->get_sub_process_list();
            if(empty($id)){
                $this->load->view('admin/process/edit', [ 'sub_process_list' => $sub_process_list, 'statuses' => $this->statuses, 'title' => _l('new_process') ]);
            }else{
                list($process, $processItem) = $this->Process_master_model->get($id);
                $this->load->view('admin/process/edit', [ 'process' => $process, 'processItem' => $processItem, 'sub_process_list' => $sub_process_list, 'statuses' => $this->statuses, 'title' => $process->name . ' - ' . _l('edit_process'),  ]);
            }
        }
    }

    public function delete($id)
    {
        if(!$id){
            redirect(admin_url('process'));
        }

        $success = $this->Process_master_model->delete($id);
        if(is_array($success)){
            set_alert('warning', _l('process_order_delete_error'));
        }elseif($success == true){
            set_alert('success', _l('deleted', _l('process')));
        }else{
            set_alert('warning', _l('process_deleting', _l('process_order_lowercase')));
        }
        redirect(admin_url('process'));
    }

    public function process_mastertable()
    {
        $this->app->get_table_data('process_mastertable');
    }

    public function process_subtable()
    {
        $this->app->get_table_data('process_subtable');
    }

    public function sub()
    {
        $this->load->view('admin/process/sub', [ 'title' => _l('Sub Process') ]);
    }

    public function sub_edit()
    {
        if($this->input->post()){
            $process_data = $this->input->post();
            if(!isset($process_data['process_id']) || empty($process_data['process_id'])){
                $this->Process_sub_model->add($process_data);
                $message = _l('added_successfully');
            }else{
                $process_id = intval($process_data['process_id']);
                unset($process_data['process_id']);
                $this->Process_sub_model->update($process_id, $process_data);
                $message = _l('updated_successfully');
            }
            echo helper_json_encode([
                'success' => true,
                'message' => $message
            ]);
        }
    }

    public function sub_delete($id)
    {
        if(!$id){
            redirect(admin_url('process/sub'));
        }

        $success = $this->Process_sub_model->delete($id);
        if(is_array($success)){
            set_alert('warning', _l('process_order_delete_error'));
        }elseif($success == true){
            set_alert('success', _l('deleted', _l('process')));
        }else{
            set_alert('warning', _l('process_deleting', _l('process_order_lowercase')));
        }
        redirect(admin_url('process/sub'));
    }

    public function get_sub_process_by_id($id)
    {
        if(!$id){
            exit();
        }
        $data = $this->Process_sub_model->get($id);
        if(!empty($data)){
            echo helper_json_encode($data);
        }
    }
}
