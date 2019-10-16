<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_stages_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblsalesstages')->row();
        }

        return $this->db->get('tblsalesstages')->result_array();
    }

    public function add($data)
    {
        unset($data['id']);
        $this->db->insert('tblsalesstages', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Sales Stage Added [ID: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function edit($data)
    {
        $salesstageid = $data['id'];
        unset($data['id']);
        $this->db->where('id', $salesstageid);
        $this->db->update('tblsalesstages', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Sales Stage Updated [' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
        // Check first if role is used in table
        if (is_reference_in_table('sales_stage_id', 'tblopportunities', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->delete('tblsalesstages');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('sales_stage_id', $id);
        $this->db->delete('tblopportunities');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            logActivity('Sales Stage Deleted [ID: ' . $id);

            return true;
        }

        return false;
    }
}
