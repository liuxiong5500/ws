<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('Process_sub_model');
$statuses = $this->ci->Process_sub_model->getStatuses();

$aColumns = [
    'name',
    'remarks',
    'status',
    'created_at'
];
$sIndexColumn = 'id';
$sTable = 'tblprocesssub';
$where = [];
// Add blank where all filter can be stored
$filter = [];

$join = [

];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblprocesssub.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach($rResult as $aRow){
    $row = [];
    $order_number = '<a href="#" data-toggle="modal" data-target="#sub_process_modal" data-id="' . $aRow['id'] . '">' . $aRow['name'] . '</a>';
    $order_number .= '<div class="row-options">';
    $order_number .= '<a href="#" data-toggle="modal" data-target="#sub_process_modal" data-id="' . $aRow['id'] . '">' . _l('edit') . '</a>';
    $order_number .= ' | <a href="' . admin_url('process/sub_delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';;
    $order_number .= '</div>';
    $row[] = $order_number;
    $row[] = $aRow['remarks'];
    $row[] = date('Y-m-d H:i:s', $aRow['created_at']);
    $hook = do_action('customers_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook['output'];

    $output['aaData'][] = $row;
}

