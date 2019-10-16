<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers_model extends CRM_Model
{
    private $contact_columns;

    public function __construct()
    {
        parent::__construct();

        $this->contact_columns = do_action('contact_columns', ['firstname', 'lastname', 'email', 'phonenumber', 'title', 'password', 'send_set_password_email', 'donotsendwelcomeemail', 'permissions', 'direction', 'invoice_emails', 'estimate_emails', 'credit_note_emails', 'contract_emails', 'task_emails', 'project_emails', 'opportunity_emails', 'ticket_emails', 'is_primary']);
        $this->load->model(['supplier_groups_model']);
    }

    public function get($id = '', $where = [])
    {
        $this->db->select(implode(',', prefixed_table_fields_array('tblsuppliers')) . ',' . get_sql_select_supplier_company());

        $this->db->join('tblcountries', 'tblcountries.country_id = tblsuppliers.country', 'left');
        $this->db->join('tblsuppliercontacts', 'tblsuppliercontacts.supplier_id = tblsuppliers.id AND is_primary = 1', 'left');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('tblsuppliers.id', $id);
            $supplier = $this->db->get('tblsuppliers')->row();

            return $supplier;
        }

        $this->db->order_by('company', 'asc');

        return $this->db->get('tblsuppliers')->result_array();
    }

    public function get_groups($id = '')
    {
        return $this->supplier_groups_model->get_groups($id);
    }

    public function get_contacts($supplier_id = '', $where = ['active' => 1])
    {
        $this->db->where($where);
        if ($supplier_id != '') {
            $this->db->where('supplier_id', $supplier_id);
        }
        $this->db->order_by('is_primary', 'DESC');

        return $this->db->get('tblsuppliercontacts')->result_array();
    }

    public function get_supplier_groups($id)
    {
        return $this->supplier_groups_model->get_supplier_groups($id);
    }

    public function get_admins($id)
    {
        $this->db->where('supplier_id', $id);

        return $this->db->get('tblsupplieradmins')->result_array();
    }

    public function assign_admins($data, $id)
    {
        $affectedRows = 0;
        if (count($data) == 0) {
            $this->db->where('supplier_id', $id);
            $this->db->delete('tblsupplieradmins');
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $current_admins = $this->get_admins($id);
            $current_admins_ids = [];
            foreach ($current_admins as $c_admin) {
                array_push($current_admins_ids, $c_admin['staff_id']);
            }
            foreach ($current_admins_ids as $c_admin_id) {
                if (!in_array($c_admin_id, $data['supplier_admins'])) {
                    $this->db->where('staff_id', $c_admin_id);
                    $this->db->where('supplier_id', $id);
                    $this->db->delete('tblsupplieradmins');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            foreach ($data['supplier_admins'] as $n_admin_id) {
                if (total_rows('tblsupplieradmins', [
                        'supplier_id' => $id,
                        'staff_id' => $n_admin_id,
                    ]) == 0) {
                    $this->db->insert('tblsupplieradmins', [
                        'supplier_id' => $id,
                        'staff_id' => $n_admin_id,
                        'date_assigned' => date('Y-m-d H:i:s'),
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    private function check_zero_columns($data)
    {
        if (!isset($data['show_primary_contact'])) {
            $data['show_primary_contact'] = 0;
        }

        if (isset($data['default_currency']) && $data['default_currency'] == '' || !isset($data['default_currency'])) {
            $data['default_currency'] = 0;
        }

        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }

        if (isset($data['billing_country']) && $data['billing_country'] == '' || !isset($data['billing_country'])) {
            $data['billing_country'] = 0;
        }

        if (isset($data['shipping_country']) && $data['shipping_country'] == '' || !isset($data['shipping_country'])) {
            $data['shipping_country'] = 0;
        }

        return $data;
    }

    public function add($data)
    {
        $contact_data = [];
        foreach ($this->contact_columns as $field) {
            if (isset($data[$field])) {
                $contact_data[$field] = $data[$field];
                // Phonenumber is also used for the company profile
                if ($field != 'phonenumber') {
                    unset($data[$field]);
                }
            }
        }

        // From customer profile register
        if (isset($data['contact_phonenumber'])) {
            $contact_data['phonenumber'] = $data['contact_phonenumber'];
            unset($data['contact_phonenumber']);
        }

        if (isset($data['groups_in'])) {
            $groups_in = $data['groups_in'];
            unset($data['groups_in']);
        }

        $data = $this->check_zero_columns($data);

        $data['datecreated'] = date('Y-m-d H:i:s');

        if (is_staff_logged_in()) {
            $data['addedfrom'] = get_staff_user_id();
        }

        $hook_data = do_action('before_supplier_added', ['data' => $data]);
        $data = $hook_data['data'];

        $this->db->insert('tblsuppliers', $data);

        $supplier_id = $this->db->insert_id();
        if ($supplier_id) {

            if (isset($groups_in)) {
                foreach ($groups_in as $group) {
                    $this->db->insert('tblsuppliergroups_in', [
                        'supplier_id' => $supplier_id,
                        'groupid' => $group,
                    ]);
                }
            }
            do_action('after_supplier_added', $supplier_id);
            $log = 'ID: ' . $supplier_id;


            logActivity('New Client Created [' . $log . ']');
        }

        return $supplier_id;
    }

    public function update($data, $id)
    {
        $affectedRows = 0;

        if (isset($data['groups_in'])) {
            $groups_in = $data['groups_in'];
            unset($data['groups_in']);
        }

        $data = $this->check_zero_columns($data);

        $_data = do_action('before_supplier_updated', [
            'supplier_id' => $id,
            'data' => $data,
        ]);

        $data = $_data['data'];
        $this->db->where('id', $id);
        $this->db->update('tblsuppliers', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if (!isset($groups_in)) {
            $groups_in = false;
        }

        if ($this->supplier_groups_model->sync_supplier_groups($id, $groups_in)) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            do_action('after_supplier_updated', $id);
            logActivity('Supplier Info Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
        $affectedRows = 0;

        do_action('before_supplier_deleted', $id);

        $last_activity = get_last_system_activity_id();
        $this->db->where('id', $id);
        $this->db->delete('tblsuppliers');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            // Delete all user contacts
            $this->db->where('supplier_id', $id);
            $contacts = $this->db->get('tblsuppliercontacts')->result_array();
            foreach ($contacts as $contact) {
                $this->delete_contact($contact['id']);
            }

            $this->db->where('supplier_id', $id);
            $this->db->delete('tblsupplieradmins');

            $this->db->where('supplier_id', $id);
            $this->db->delete('tblsuppliergroups_in');
        }
        if ($affectedRows > 0) {
            do_action('after_supplier_deleted', $id);

            // Delete activity log caused by delete customer function
            if ($last_activity) {
                $this->db->where('id >', $last_activity->id);
                $this->db->delete('tblactivitylog');
            }

            logActivity('Supplier Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete_group($id)
    {
        return $this->supplier_groups_model->delete($id);
    }

    public function add_group($data)
    {
        return $this->supplier_groups_model->add($data);
    }

    public function edit_group($data)
    {
        return $this->supplier_groups_model->edit($data);
    }

    public function get_contact($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblsuppliercontacts')->row();
    }

    public function add_contact($data, $supplier_id, $not_manual_request = false)
    {
        $send_set_password_email = isset($data['send_set_password_email']) ? true : false;

        $send_welcome_email = true;
        if (isset($data['donotsendwelcomeemail'])) {
            $send_welcome_email = false;
        } elseif (strpos($_SERVER['HTTP_REFERER'], 'register') !== false) {
            $send_welcome_email = true;

            // Do not send welcome email if confirmation for registration is enabled
            if (get_option('customers_register_require_confirmation') == '1') {
                $send_welcome_email = false;
            }
            // If client register set this auto contact as primary
            $data['is_primary'] = 1;
        }

        if (isset($data['is_primary'])) {
            $data['is_primary'] = 1;
            $this->db->where('supplier_id', $supplier_id);
            $this->db->update('tblsuppliercontacts', [
                'is_primary' => 0,
            ]);
        } else {
            $data['is_primary'] = 0;
        }

        $password_before_hash = '';
        $data['supplier_id'] = $supplier_id;
        if (isset($data['password'])) {
            $password_before_hash = $data['password'];
            $this->load->helper('phpass');
            $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
            $data['password'] = $hasher->HashPassword($data['password']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');

        $hook_data = [
            'data' => $data,
            'not_manual_request' => $not_manual_request,
        ];

        $hook_data = do_action('before_create_supplier_contact', $hook_data);
        $data = $hook_data['data'];

        $data['email'] = trim($data['email']);

        $this->db->insert('tblsuppliercontacts', $data);
        $contact_id = $this->db->insert_id();

        if ($contact_id) {
            logActivity('Supplier Contact Created [ID: ' . $contact_id . ']');
            do_action('supplier_contact_created', $contact_id);

            return $contact_id;
        }

        return false;
    }

    public function update_contact($data, $id)
    {
        $affectedRows = 0;
        $contact = $this->get_contact($id);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $this->load->helper('phpass');
            $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
            $data['password'] = $hasher->HashPassword($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }

        $send_set_password_email = isset($data['send_set_password_email']) ? true : false;
        $set_password_email_sent = false;

        $permissions = isset($data['permissions']) ? $data['permissions'] : [];
        $data['is_primary'] = isset($data['is_primary']) ? 1 : 0;

        $hook_data = do_action('before_update_supplier_contact', ['data' => $data, 'id' => $id]);
        $data = $hook_data['data'];

        $this->db->where('id', $id);
        $this->db->update('tblsuppliercontacts', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if (isset($data['is_primary']) && $data['is_primary'] == 1) {
                $this->db->where('supplier_id', $contact->supplier_id);
                $this->db->where('id !=', $id);
                $this->db->update('tblsuppliercontacts', [
                    'is_primary' => 0,
                ]);
            }
        }

        if ($affectedRows > 0 && !$set_password_email_sent) {
            logActivity('Supplier Contact Updated [ID: ' . $id . ']');

            return true;
        } elseif ($affectedRows > 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent_and_profile_updated' => true,
            ];
        } elseif ($affectedRows == 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent' => true,
            ];
        }

        return false;
    }

    public function delete_contact($id)
    {
        do_action('before_delete_supplier_contact', $id);

        $last_activity = get_last_system_activity_id();

        $this->db->where('id', $id);
        $this->db->delete('tblsuppliercontacts');

        if ($this->db->affected_rows() > 0) {
            if (is_dir(get_upload_path_by_type('supplier_contact_profile_images') . $id)) {
                delete_dir(get_upload_path_by_type('supplier_contact_profile_images') . $id);
            }

            // Delete activity log caused by delete contact function
            if ($last_activity) {
                $this->db->where('id >', $last_activity->id);
                $this->db->delete('tblactivitylog');
            }

            return true;
        }

        return false;
    }

    public function change_contact_status($id, $status)
    {
        $hook_data['id'] = $id;
        $hook_data['status'] = $status;
        $hook_data = do_action('change_supplier_contact_status', $hook_data);
        $status = $hook_data['status'];
        $id = $hook_data['id'];
        $this->db->where('id', $id);
        $this->db->update('tblsuppliercontacts', [
            'active' => $status,
        ]);
        if ($this->db->affected_rows() > 0) {
            logActivity('Supplier Contact Status Changed [ContactID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    public function get_suppliers_distinct_countries()
    {
        return $this->db->query('SELECT DISTINCT(country_id), short_name FROM tblsuppliers JOIN tblcountries ON tblcountries.country_id=tblsuppliers.country')->result_array();
    }

    public function change_supplier_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('tblsuppliers', [
            'active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            logActivity('Supplier Status Changed [ID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    public function change_contact_password($data)
    {
        $hook_data['data'] = $data;
        $hook_data = do_action('before_supplier_contact_change_password', $hook_data);
        $data = $hook_data['data'];

        // Get current password
        $this->db->where('id', get_supplier_contact_user_id());
        $client = $this->db->get('tblsuppliercontacts')->row();
        $this->load->helper('phpass');
        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        if (!$hasher->CheckPassword($data['oldpassword'], $client->password)) {
            return [
                'old_password_not_match' => true,
            ];
        }
        $update_data['password'] = $hasher->HashPassword($data['newpasswordr']);
        $update_data['last_password_change'] = date('Y-m-d H:i:s');
        $this->db->where('id', get_supplier_contact_user_id());
        $this->db->update('tblsuppliercontacts', $update_data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    public function update_company_details($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }
        if (isset($data['billing_country']) && $data['billing_country'] == '') {
            $data['billing_country'] = 0;
        }
        if (isset($data['shipping_country']) && $data['shipping_country'] == '') {
            $data['shipping_country'] = 0;
        }

        // From v.1.9.4 these fields are textareas
        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
        if (isset($data['billing_street'])) {
            $data['billing_street'] = trim($data['billing_street']);
            $data['billing_street'] = nl2br($data['billing_street']);
        }
        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $this->db->where('id', $id);
        $this->db->update('tblsuppliers', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            do_action('supplier_updated_company_info', $id);
            return true;
        }

        return false;
    }
}
