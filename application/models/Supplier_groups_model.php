<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_groups_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_groups($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblsuppliersgroups')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get('tblsuppliersgroups')->result_array();
    }

    public function add($data)
    {
        $this->db->insert('tblsuppliersgroups', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Supplier Group Created [ID:' . $insert_id . ', Name:' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function edit($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('tblsuppliersgroups', [
            'name' => $data['name'],
        ]);
        if ($this->db->affected_rows() > 0) {
            logActivity('Supplier Group Updated [ID:' . $data['id'] . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblsuppliersgroups');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('groupid', $id);
            $this->db->delete('tblsuppliergroups_in');
            logActivity('Supplier Group Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_supplier_groups($id)
    {
        $this->db->where('supplier_id', $id);

        return $this->db->get('tblsuppliergroups_in')->result_array();
    }

    public function sync_supplier_groups($id, $groups_in)
    {
        if ($groups_in == false) {
            unset($groups_in);
        }
        $affectedRows = 0;
        $supplier_groups = $this->get_supplier_groups($id);
        if (sizeof($supplier_groups) > 0) {
            foreach ($supplier_groups as $supplier_group) {
                if (isset($groups_in)) {
                    if (!in_array($supplier_group['groupid'], $groups_in)) {
                        $this->db->where('customer_id', $id);
                        $this->db->where('id', $supplier_group['id']);
                        $this->db->delete('tblsuppliergroups_in');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('supplier_id', $id);
                    $this->db->delete('tblsuppliergroups_in');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($groups_in)) {
                foreach ($groups_in as $group) {
                    $this->db->where('supplier_id', $id);
                    $this->db->where('groupid', $group);
                    $_exists = $this->db->get('tblsuppliergroups_in')->row();
                    if (!$_exists) {
                        if (empty($group)) {
                            continue;
                        }
                        $this->db->insert('tblsuppliergroups_in', [
                            'supplier_id' => $id,
                            'groupid' => $group,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } else {
            if (isset($groups_in)) {
                foreach ($groups_in as $group) {
                    if (empty($group)) {
                        continue;
                    }
                    $this->db->insert('tblsuppliergroups_in', [
                        'supplier_id' => $id,
                        'groupid' => $group,
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
}
