<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Campaigns_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblcampaigns')->row();
        }

        return $this->db->get('tblcampaigns')->result_array();
    }

    public function add($data)
    {
        unset($data['id']);
        $this->db->insert('tblcampaigns', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Campaign Added [ID: ' . $insert_id . ']');
            return true;
        }

        return false;
    }

    public function edit($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $this->db->where('id', $id);
        $this->db->update('tblcampaigns', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Campaign Updated [' . $id . ']');
            return true;
        }

        return false;
    }

    public function get_type($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblcampaigntypes')->row();
        }

        return $this->db->get('tblcampaigntypes')->result_array();
    }

    public function add_type($data)
    {
        $this->db->insert('tblcampaigntypes', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Campaigns Type Added [TypeID: ' . $insert_id . ', Name: ' . $data['name'] . ']');
        }

        return $insert_id;
    }

    public function update_type($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblcampaigntypes', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Campaigns Type Updated [TypeID: ' . $id . ', Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete_type($id)
    {
        $current = $this->get_type($id);
        // Check if is already using in table
        if (is_reference_in_table('type_id', 'tblcampaigns', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $this->db->where('id', $id);
        $this->db->delete('tblcampaigntypes');
        if ($this->db->affected_rows() > 0) {
            logActivity('Campaigns Type Deleted [TypeID: ' . $id . ']');

            return true;
        }

        return false;
    }
}
