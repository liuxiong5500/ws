<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approvedlogs_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add($data)
    {
        unset($data['id']);
        $this->db->insert('tblapprovedlogs', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New ApprovedLogs Added [ID: ' . $insert_id . ']');
            return true;
        }

        return false;
    }
}