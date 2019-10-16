<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process_sub_model extends CRM_Model
{
    protected $table = 'tblprocesssub';

    protected $statuses;

    public function __construct()
    {
        parent::__construct();
        $this->statuses = do_action('before_set_process_statuses', [
            [
                'id' => 1,
                'name' => _l('process_status_created')
            ],
            [
                'id' => 2,
                'name' => _l('process_status_processing')
            ],
            [
                'id' => 3,
                'name' => _l('process_status_completed')
            ]
        ]);
    }

    public function get($id)
    {
        $this->db->from($this->table)->where('id', intval($id));
        return $this->db->get()->row_array();
    }

    public function getStatuses()
    {
        return $this->statuses;
    }

    public function add(array $data)
    {
        $this->db->insert($this->table, [ 'name' => $data['name'], 'created_at' => $data['created_at'] ? strtotime($data['created_at']) : time(), 'remarks' => $data['remarks'], 'status' => $data['status'] ?? 0 ]);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->update($this->table, [ 'name' => $data['name'], 'remarks' => $data['remarks'], 'status' => $data['status'] ], [ 'id' => $id ]);

        return $id;
    }

    public function delete(int $id)
    {
        $this->db->delete($this->table, [ 'id' => $id ]);
        return true;
    }

    public function get_terms()
    {
        $result = $this->db->get($this->table)->result_array();
        return $result;
    }

    public function get_sub_process_list()
    {
        $this->db->from($this->table);
        $list = $this->db->get()->result_array();
        return $list;
    }
}
