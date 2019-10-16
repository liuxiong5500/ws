<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('Process_master_model');
$statuses = $this->ci->Process_master_model->getStatuses();

$aColumns = [
    'name',
    'remarks',
    'status',
    'created_at'
];
$sIndexColumn = 'id';
$sTable = 'tblprocessmaster';
$where = [];
// Add blank where all filter can be stored
$filter = [];

$join = [

];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblprocessmaster.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach($rResult as $aRow){
    $row = [];
    $url = admin_url('process/view/' . $aRow['id']);
    $order_number = '<a href="' . $url . '">' . $aRow['name'] . '</a>';
    $order_number .= '<div class="row-options">';
    /*if ($aRow['status'] == 1) {
        $order_number .= '<a href="' . admin_url('purchase_orders/approve/' . $aRow['id']) . '">' . _l('approve') . '</a> | ';
    }*/
    $order_number .= '<a href="' . admin_url('process/edit/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    $order_number .= ' | <a href="' . admin_url('process/delete/' . $aRow['id']) . '">' . _l('delete') . '</a>';
    $order_number .= '</div>';
    $row[] = $order_number;
    $row[] = $aRow['remarks'];
    /*foreach($statuses as $status){
        if($aRow['status'] == $status['id']){
            $row[] = $status['name'];
            break 1;
        }
    }*/
    //$row[] = $aRow['status'];
    $row[] = date('Y-m-d H:i:s', $aRow['created_at']);

    $hook = do_action('customers_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook['output'];

    $output['aaData'][] = $row;
}

