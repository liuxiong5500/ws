<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_Autologin extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if autologin found
     * @param  mixed $user_id clientid/staffid
     * @param  string $key key from cookie to retrieve from database
     * @return mixed
     */
    public function get($user_id, $key)
    {
        // check if user is staff
        $this->db->where('user_id', $user_id);
        $this->db->where('key_id', $key);
        $user = $this->db->get('tblsupplierautologin')->row();
        if (!$user) {
            return null;
        }

        $table = 'tblsuppliercontacts';
        $this->db->select($table . '.id as id');
        $_id = 'id';

        $this->db->select($table . '.' . $_id);
        $this->db->from($table);
        $this->db->join('tblsupplierautologin', 'tblsupplierautologin.user_id = ' . $table . '.' . $_id);
        $this->db->where('tblsupplierautologin.user_id', $user_id);
        $this->db->where('tblsupplierautologin.key_id', $key);
        $query = $this->db->get();
        if ($query) {
            if ($query->num_rows() == 1) {
                $user = $query->row();
                return $user;
            }
        }

        return null;
    }

    /**
     * Set new autologin if user have clicked remember me
     * @param mixed $user_id clientid/userid
     * @param string $key cookie key
     * @param integer $staff is staff or client
     */
    public function set($user_id, $key)
    {
        return $this->db->insert('tblsupplierautologin', [
            'user_id' => $user_id,
            'key_id' => $key,
            'user_agent' => substr($this->input->user_agent(), 0, 149),
            'last_ip' => $this->input->ip_address()
        ]);
    }

    /**
     * Delete user autologin
     * @param  mixed $user_id clientid/userid
     * @param  string $key cookie key
     * @param integer $staff is staff or client
     */
    public function delete($user_id, $key)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('key_id', $key);
        $this->db->delete('tblsupplierautologin');
    }
}
