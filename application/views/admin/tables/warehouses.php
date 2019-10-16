<?php
defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'name',
    'address',
    'contact',
    'telephone'
];
$sIndexColumn = 'id';
$sTable = 'tblcustomerwarehouses';

$where = ['AND clientid=' . $clientid];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [
    'id',
    'clientid'
]);
$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name') {
            $_data = '<span class="name"><a href="#" onclick="warehouse(' . $clientid . ',' . $aRow['id'] . ');return false;">' . $_data . '</a></span>';
        }
        $row[] = $_data;
    }
    $options = icon_btn('#', 'pencil-square-o', 'btn-default', [
        'onclick' => 'warehouse(' . $clientid . ',' . $aRow['id'] . ');return false;'
    ]);
    $row[] = $options .= icon_btn('warehouses/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    $output['aaData'][] = $row;
}

