<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Levels_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        $this->db->select('tbllevels.*,tbldepartments.name as department,tblpositions.name as position');
        $this->db->join('tbldepartments', 'tbldepartments.departmentid = tbllevels.departmentid', 'left');
        $this->db->join('tblpositions', 'tblpositions.id = tbllevels.positionid', 'left');
        if (is_numeric($id)) {
            $this->db->where('tbllevels.id', $id);

            return $this->db-- > get('tbllevels')->row();
        }

        return $this->db->get('tbllevels')->result_array();
    }

    public function add($data)
    {
        unset($data['id']);
        $this->db->insert('tbllevels', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Level Added [ID: ' . $insert_id . ']');
            return true;
        }

        return false;
    }

    public function edit($data)
    {
        $levelid = $data['id'];
        unset($data['id']);
        $this->db->where('id', $levelid);
        $this->db->update('tbllevels', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Level Updated [' . $levelid . ']');

            return true;
        }

        return false;
    }

    public function delete($id)
    {
        // Check first if role is used in table
        if (is_reference_in_table('levelid', 'tblstafflevels', $id)) {
            return [
                'referenced' => true,
            ];
        }
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->delete('tbllevels');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('levelid', $id);
        $this->db->delete('tblstafflevels');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            logActivity('Level Deleted [ID: ' . $id);

            return true;
        }

        return false;
    }

    public function get_staff_levels($userid = false, $onlyids = false)
    {
        if ($userid == false) {
            $userid = get_staff_user_id();
        }
        if ($onlyids == false) {
            $this->db->select();
        } else {
            $this->db->select('tblstafflevels.levelid');
        }
        $this->db->from('tblstafflevels');
        $this->db->join('tbllevels', 'tblstafflevels.levelid = tbllevels.id', 'left');
        $this->db->where('staffid', $userid);
        $levels = $this->db->get()->result_array();
        if ($onlyids == true) {
            $levelsid = [];
            foreach ($levels as $level) {
                array_push($levelsid, $level['id']);
            }

            return $levelsid;
        }

        return $levels;
    }

    public function get_staff_level($userid = false)
    {
        if ($userid == false) {
            $userid = get_staff_user_id();
        }
        $this->db->select();
        $this->db->from('tblstafflevels');
        $this->db->join('tbllevels', 'tblstafflevels.levelid = tbllevels.id', 'left');
        $this->db->where('staffid', $userid);
        $this->db->order_by('level', 'asc');
        $levels = $this->db->get()->result_array();
        if ($levels) {
            return $levels[0];
        }
        return null;
    }

    public function get_staffs($level, $department)
    {
        $this->db->select();
        $this->db->from('tblstaff');
        $this->db->join('tblstafflevels', 'tblstafflevels.staffid = tblstaff.staffid', 'left');
        $this->db->join('tbllevels', 'tblstafflevels.levelid = tbllevels.id', 'left');
        $this->db->where('level', $level);
        $this->db->where('departmentid', $department);
        $this->db->where('active', 1);
        $this->db->order_by('level', 'asc');
        $staffs = $this->db->get()->result_array();
        return $staffs;
    }

    public function get_approved_level($departmentid, $total, $gp)
    {
        $this->db->select();
        $this->db->from('tbllevels');
        $this->db->where('departmentid = ' . $departmentid . ' AND ' . floatval($total) . ' < toamount AND gp <=' . floatval($gp));
        $this->db->order_by('level', 'desc');
        $levels = $this->db->get()->result_array();
        if ($levels) {
            return $levels[0];
        }
        return null;
    }
}