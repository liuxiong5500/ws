<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['suppliers_model']);
    }

    public function index()
    {
        $this->load->view('admin/suppliers/manage');
    }

    public function table()
    {
        $this->app->get_table_data('suppliers');
    }

    public function supplier($id = '')
    {
        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($id == '') {
                $data = $this->input->post();

                $save_and_add_contact = false;
                if (isset($data['save_and_add_contact'])) {
                    unset($data['save_and_add_contact']);
                    $save_and_add_contact = true;
                }
                $id = $this->suppliers_model->add($data);

                $assign['supplier_admins'] = [];
                $assign['supplier_admins'][] = get_staff_user_id();
                $this->suppliers_model->assign_admins($assign, $id);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('supplier')));
                    if ($save_and_add_contact == false) {
                        redirect(admin_url('suppliers/supplier/' . $id));
                    } else {
                        redirect(admin_url('suppliers/supplier/' . $id . '?group=contacts&new_contact=true'));
                    }
                }
            } else {
                $success = $this->suppliers_model->update($this->input->post(), $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('supplier')));
                }
                redirect(admin_url('suppliers/supplier/' . $id));
            }
        }

        if (!$this->input->get('group')) {
            $group = 'profile';
        } else {
            $group = $this->input->get('group');
        }

        if ($group != 'contacts' && $contact_id = $this->input->get('contactid')) {
            redirect(admin_url('suppliers/supplier/' . $id . '?group=contacts&contactid=' . $contact_id));
        }

        // View group
        $data['group'] = $group;
        // Customer groups
        $data['groups'] = $this->suppliers_model->get_groups();

        if ($id == '') {
            $title = _l('add_new', _l('supplier_lowercase'));
        } else {
            $supplier = $this->suppliers_model->get($id);
            if (!$supplier) {
                blank_page('Supplier Not Found');
            }
            $data['contacts'] = $this->suppliers_model->get_contacts($id);
            if ($group == 'profile') {
                $data['supplier_groups'] = $this->suppliers_model->get_supplier_groups($id);
                $data['supplier_admins'] = $this->suppliers_model->get_admins($id);
            }

            $data['staff'] = $this->staff_model->get('', ['active' => 1]);

            $data['supplier'] = $supplier;
            $title = $supplier->company;
            $data['members'] = $data['staff'];

            if (!empty($data['supplier']->company)) {
                // Check if is realy empty client company so we can set this field to empty
                // The query where fetch the client auto populate firstname and lastname if company is empty
                if (is_empty_supplier_company($data['supplier']->id)) {
                    $data['supplier']->company = '';
                }
            }
        }

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['title'] = $title;

        $this->load->view('admin/suppliers/supplier', $data);
    }

    public function groups()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('suppliers_groups');
        }
        $data['title'] = _l('supplier_groups');
        $this->load->view('admin/suppliers/groups_manage', $data);
    }

    public function group()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $id = $this->suppliers_model->add_group($data);
                $message = $id ? _l('added_successfully', _l('supplier_group')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id' => $id,
                    'name' => $data['name'],
                ]);
            } else {
                $success = $this->suppliers_model->edit_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('supplier_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete_group($id)
    {
        if (!$id) {
            redirect(admin_url('suppliers/groups'));
        }
        $response = $this->suppliers_model->delete_group($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('supplier_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('supplier_group_lowercase')));
        }
        redirect(admin_url('suppliers/groups'));
    }

    public function contacts($supplier_id)
    {
        $this->app->get_table_data('supplier_contacts', [
            'supplier_id' => $supplier_id,
        ]);
    }

    public function assign_admins($id)
    {
        $success = $this->suppliers_model->assign_admins($this->input->post(), $id);
        if ($success == true) {
            set_alert('success', _l('updated_successfully', _l('supplier')));
        }

        redirect(admin_url('suppliers/supplier/' . $id . '?tab=supplier_admins'));
    }

    public function delete_supplier_admin($supplier_id, $staff_id)
    {
        $this->db->where('supplier_id', $supplier_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete('tblsupplieradmins');
        redirect(admin_url('suppliers/supplier/' . $supplier_id) . '?tab=supplier_admins');
    }

    public function contact($supplier_id, $contact_id = '')
    {
        $data['supplier_id'] = $supplier_id;
        $data['contactid'] = $contact_id;
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            unset($data['contactid']);
            if ($contact_id == '') {
                $id = $this->suppliers_model->add_contact($data, $supplier_id);
                $message = '';
                $success = false;
                if ($id) {
                    handle_supplier_contact_profile_image_upload($id);
                    $success = true;
                    $message = _l('added_successfully', _l('contact'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'has_primary_contact' => (total_rows('tblsuppliercontacts', ['supplier_id' => $supplier_id, 'is_primary' => 1]) > 0 ? true : false),
                    'is_individual' => is_empty_supplier_company($supplier_id) && total_rows('tblsuppliercontacts', ['supplier_id' => $supplier_id]) == 1,
                ]);
                die;
            }
            $success = $this->suppliers_model->update_contact($data, $contact_id);
            $message = '';
            $updated = false;
            if (is_array($success)) {
                if (isset($success['set_password_email_sent'])) {
                    $message = _l('set_password_email_sent_to_supplier');
                } elseif (isset($success['set_password_email_sent_and_profile_updated'])) {
                    $updated = true;
                    $message = _l('set_password_email_sent_to_supplier_and_profile_updated');
                }
            } else {
                if ($success == true) {
                    $updated = true;
                    $message = _l('updated_successfully', _l('contact'));
                }
            }
            if (handle_supplier_contact_profile_image_upload($contact_id) && !$updated) {
                $message = _l('updated_successfully', _l('contact'));
                $success = true;
            }
            echo json_encode([
                'success' => $success,
                'message' => $message,
                'has_primary_contact' => (total_rows('tblsuppliercontacts', ['id' => $supplier_id, 'is_primary' => 1]) > 0 ? true : false),
            ]);
            die;
        }
        if ($contact_id == '') {
            $title = _l('add_new', _l('contact_lowercase'));
        } else {
            $data['contact'] = $this->suppliers_model->get_contact($contact_id);

            if (!$data['contact']) {
                header('HTTP/1.0 400 Bad error');
                echo json_encode([
                    'success' => false,
                    'message' => 'Contact Not Found',
                ]);
                die;
            }
            $title = $data['contact']->firstname . ' ' . $data['contact']->lastname;
        }

        $data['title'] = $title;
        $this->load->view('admin/suppliers/modals/contact', $data);
    }

    public function delete_contact_profile_image($contact_id)
    {
        do_action('before_remove_contact_profile_image');
        if (file_exists(get_upload_path_by_type('supplier_contact_profile_images') . $contact_id)) {
            delete_dir(get_upload_path_by_type('supplier_contact_profile_images') . $contact_id);
        }
        $this->db->where('id', $contact_id);
        $this->db->update('tblsuppliercontacts', [
            'profile_image' => null,
        ]);
    }

    public function delete_contact($customer_id, $id)
    {
        $this->suppliers_model->delete_contact($id);
        redirect(admin_url('suppliers/supplier/' . $customer_id . '?group=contacts'));
    }

    public function change_contact_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->suppliers_model->change_contact_status($id, $status);
        }
    }

    public function change_supplier_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->suppliers_model->change_supplier_status($id, $status);
        }
    }

    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('suppliers'));
        }
        $response = $this->suppliers_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('supplier_delete_transactions_warning'));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('supplier')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('supplier_lowercase')));
        }
        redirect(admin_url('suppliers'));
    }
}
