<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process_msrelate_model extends CRM_Model
{
    protected $table = 'tblprocessmsrelate';

    public function get($id)
    {
        $this->db->from($this->table)->where('id', intval($id));
        return [ $this->db->get()->row(), $this->db->from($this->subtable)->where('mid', $id)->get()->result_object() ];
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
            $count = count($data['process_item']['name']);
            $processItem = $data['process_item'];
            for($i = 0;$i < $count;$i++){
                $this->db->insert($this->subtable, [ 'name' => $processItem['name'][$i], 'created_at' => strtotime($processItem['created_at'][$i]), 'remarks' => $processItem['remarks'][$i], 'seq' => $processItem['seq'][$i], 'status' => $processItem['status'][$i], 'mid' => $id
                ]);
            }
        }
        return $id;
    }

    public function update($id, $data)
    {
        $this->db->update($this->table, [ 'name' => $data['name'], 'created_at' => strtotime($data['created_at']), 'remarks' => $data['remarks'], 'status' => $data['status'] ], [ 'id' => $id ]);
        if(isset($data['process_item']) && is_array($data['process_item']) && !empty($data['process_item'])){
            $count = count($data['process_item']['name']);
            $processItem = $data['process_item'];
            for($i = 0;$i < $count;$i++){
                if(isset($processItem['id'][$i]) && !empty($processItem['id'][$i])){
                    $itemId = intval($processItem['id'][$i]);
                    $this->db->update($this->subtable, [ 'name' => $processItem['name'][$i], 'created_at' => strtotime($processItem['created_at'][$i]), 'remarks' => $processItem['remarks'][$i], 'seq' => $processItem['seq'][$i], 'status' => $processItem['status'][$i], 'mid' => $id
                    ], [ 'id' => $itemId ]);
                }else{
                    $this->db->insert($this->subtable, [ 'name' => $processItem['name'][$i], 'created_at' => strtotime($processItem['created_at'][$i]), 'remarks' => $processItem['remarks'][$i], 'seq' => $processItem['seq'][$i], 'status' => $processItem['status'][$i], 'mid' => $id
                    ]);
                }
            }
        }
        return $id;
    }

    public function delete(int $id)
    {
        $this->db->delete($this->table, [ 'id' => $id ]);
        $this->db->delete($this->subtable, [ 'mid' => $id ]);
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
        $this->db->join('tblprocesssub', 'tblprocessmaster.id=tblprocesssub.mid', 'left');
        $this->db->where('tblitems.id', $item_id);
        return $this->db->get()->result_object();
    }
}
