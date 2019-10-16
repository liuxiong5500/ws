<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Positions_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblpositions')->row();
        }

        return $this->db->get('tblpositions')->result_array();
    }

    public function add($data)
    {
        unset($data['id']);
        $this->db->insert('tblpositions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Position Added [ID: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function edit($data)
    {
        $positionid = $data['id'];
        unset($data['id']);
        $this->db->where('id', $positionid);
        $this->db->update('tblpositions', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Position Updated [' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
        // Check first if role is used in table
        if (is_reference_in_table('positionid', 'tbllevels', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->delete('tblpositions');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('positionid', $id);
        $this->db->delete('tbllevels');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            logActivity('Position Deleted [ID: ' . $id);

            return true;
        }

        return false;
    }
}