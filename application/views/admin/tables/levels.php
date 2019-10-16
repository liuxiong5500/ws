<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'tbldepartments.name',
    'tblpositions.name',
    'level',
    'fromamount',
    'toamount',
    'gp'
];
$sIndexColumn = 'id';
$sTable = 'tbllevels';
$join = [
    'LEFT JOIN tbldepartments ON tbldepartments.departmentid = tbllevels.departmentid',
    'LEFT JOIN tblpositions on tblpositions.id = tbllevels.positionid'
];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [
    'tbllevels.id',
    'tbllevels.departmentid',
    'positionid'
]);
$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'tbldepartments.name') {
            $_data = '<span class="name" data-id="' . $aRow['departmentid'] . '"><a href="#" data-toggle="modal" data-target="#level_modal" data-id="' . $aRow['id'] . '">' . $_data . '</a></span>';
        }
        if ($aColumns[$i] == 'tblpositions.name') {
            $_data = '<span class="name" data-id="' . $aRow['positionid'] . '">' . $aRow['tblpositions.name'] . '</span>';
        }
        $row[] = $_data;
    }
    $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', [
        'data-toggle' => 'modal',
        'data-target' => '#level_modal',
        'data-id' => $aRow['id'],
    ]);
    $row[] = $options .= icon_btn('levels/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    $output['aaData'][] = $row;
}
