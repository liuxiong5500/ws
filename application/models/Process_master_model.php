<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process_master_model extends CRM_Model
{
    protected $table = 'tblprocessmaster';

    protected $subtable = 'tblprocesssub';

    protected $relatetable = 'tblprocessmsrelate';

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
        return [ $this->db->get()->row(), $this->db->from($this->relatetable)->where('mid', $id)->join($this->subtable, 'tblprocessmsrelate.subid=tblprocesssub.id', 'left')->get()->result_object() ];
    }

    public function getStatuses()
    {
        return $this->statuses;
    }

    public function add(array $data)
    {
        $this->db->insert($this->table, [ 'name' => $data['name'], 'created_at' => $data['created_at'] ? strtotime($data['created_at']) : time(), 'remarks' => $data['remarks'], 'status' => $data['status'] ?? 0 ]);
        $id = $this->db->insert_id();
        if(isset($data['process_item']) && is_array($data['process_item']) && !empty($data['process_item'])){
            $count = count($data['process_item']['id']);
            $processItem = $data['process_item'];
            for($i = 0;$i < $count;$i++){
                $this->db->insert($this->relatetable, [ 'mid' => $id, 'subid' => $processItem['id'][$i] ]);
            }
        }
        return $id;
    }

    public function update($id, $data)
    {
        $this->db->update($this->table, [ 'name' => $data['name'], 'remarks' => $data['remarks'], 'status' => $data['status'] ], [ 'id' => $id ]);
        if(isset($data['process_item']) && is_array($data['process_item']) && !empty($data['process_item'])){
            $count = count($data['process_item']['id']);
            $processItem = $data['process_item'];
            $this->db->delete($this->relatetable, [ 'mid' => $id ]);
            for($i = 0;$i < $count;$i++){
                $this->db->insert($this->relatetable, [ 'mid' => $id, 'subid' => $processItem['id'][$i] ]);
            }
        }
        return $id;
    }

    public function delete(int $id)
    {
        $this->db->delete($this->table, [ 'id' => $id ]);
        $this->db->delete($this->relatetable, [ 'mid' => $id ]);
        return true;
    }

    public function get_terms()
    {
        $result = $this->db->get($this->table)->result_array();
        return $result;
    }

    public function get_item_process_sub_list(int $item_id = 0)
    {
        if(empty($item_id)){
            return [];
        }
        $this->db->from('tblitems');
        $this->db->join('tblprocessmaster', 'tblitems.process_id=tblprocessmaster.id', 'left');
        $this->db->join('tblprocessmsrelate', 'tblprocessmsrelate.mid=tblprocessmaster.id', 'left');
        $this->db->join('tblprocesssub', 'tblprocesssub.id=tblprocessmsrelate.subid', 'left');
        $this->db->where('tblitems.id', $item_id);
        return $this->db->get()->result_object();
    }
}
