<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment_terms_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblpaymentterms')->row();
        }

        return $this->db->get('tblpaymentterms')->result_array();
    }

    public function add($data)
    {
        unset($data['id']);
        $this->db->insert('tblpaymentterms', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Payment Term Added [ID: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function edit($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $this->db->where('id', $id);
        $this->db->update('tblpaymentterms', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Term Updated [' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
//        if (is_reference_in_table('positionid', 'tbllevels', $id)) {
//            return [
//                'referenced' => true,
//            ];
//        }
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->delete('tblpaymentterms');

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            logActivity('Payment Term Deleted [ID: ' . $id);

            return true;
        }

        return false;
    }
}
