<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Purchase_orders
 * @property Purchase_orders_model $purchase_orders_model
 * @property Process_master_model $process_master_model
 * @property Shipment_terms_model $shipment_terms_model
 * @description
 * @version 1.0.0
 */
class Purchase_orders extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['currencies_model', 'payment_terms_model', 'shipment_terms_model', 'suppliers_model', 'purchase_orders_model', 'production_logs_model', 'process_master_model']);
    }

    public function index()
    {
        $this->load->view('admin/purchase_orders/manage');
    }

    public function order($id = '')
    {
        if ($this->input->post()) {
            $purchase_order_data = $this->input->post();
            if ($id == '') {
                $id = $this->purchase_orders_model->add($purchase_order_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('purchase_order')));
                }
            } else {
                $success = $this->purchase_orders_model->update($purchase_order_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('purchase_order')));
                }
            }
            redirect(admin_url('purchase_orders'));
        }
        if ($id == '') {
            $title = _l('create_new_purchase_order');
        } else {
            $purchase_order = $this->purchase_orders_model->get($id);

            if (!$purchase_order || !user_can_view_estimate($id)) {
                blank_page(_l('purchase_order_not_found'));
            }

            $data['estimate'] = $purchase_order;
            $data['edit'] = true;
            $title = _l('edit', _l('purchase_order_lowercase'));
        }
        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items'] = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();
        $data['currencies'] = $this->currencies_model->get();
        $data['statuses'] = $this->purchase_orders_model->get_statuses();
        $data['suppliers'] = $this->suppliers_model->get();
        $data['payment_terms'] = $this->payment_terms_model->get();
        $data['shipment_terms'] = $this->shipment_terms_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['title'] = $title;
        $this->load->view('admin/purchase_orders/order', $data);
    }

    public function table()
    {
        $this->app->get_table_data('purchase_orders');
    }

    public function view($id)
    {
        if (!$id) {
            redirect(admin_url('purchase_orders'));
        }
        $order = $this->purchase_orders_model->get($id);
        $data['order'] = $order;
        $data['title'] = _l('purchase_order');
        $this->load->view('admin/purchase_orders/view', $data);
    }

    public function item_logs($id)
    {
        $logs = $this->production_logs_model->get($id);
        $data['logs'] = $logs;
        $data['title'] = _l('purchase_order_item_logs');
        $this->load->view('admin/purchase_orders/item_logs', $data);
    }

    public function approve($id)
    {
        if (!$id) {
            redirect(admin_url('purchase_orders'));
        }
        $success = $this->purchase_orders_model->set_status($id, 2);
        if ($success) {
            set_alert('success', _l('approved', _l('purchase_order')));
        } else {
            set_alert('warning', _l('purchase_order_approve_error'));
        }
        redirect(admin_url('purchase_orders'));
    }

    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('purchase_orders'));
        }

        $success = $this->purchase_orders_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('purchase_order_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('purchase_order')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('purchase_order_lowercase')));
        }
        redirect(admin_url('purchase_orders'));
    }
}
