<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customer_warehouses_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblcustomerwarehouses')->row();
        }

        return $this->db->get('tblcustomerwarehouses')->result_array();
    }

    public function delete($id)
    {
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->delete('tblcustomerwarehouses');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            logActivity('Warehouse Deleted [ID: ' . $id);

            return true;
        }

        return false;

    }
}
