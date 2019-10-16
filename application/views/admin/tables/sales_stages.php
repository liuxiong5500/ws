<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'name',
    'color'
];
$sIndexColumn = 'id';
$sTable = 'tblsalesstages';
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], [
    'id'
]);
$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name') {
            $_data = '<span class="name"><a href="#" data-toggle="modal" data-target="#sales_stage_modal" data-id="' . $aRow['id'] . '">' . $_data . '</a></span>';
        }
        if ($aColumns[$i] == 'color') {
            $_data = '<span class="color" style="color: ' . $aRow['color'] . '">' . $_data . '</span>';
        }
        $row[] = $_data;
    }
    $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', [
        'data-toggle' => 'modal',
        'data-target' => '#sales_stage_modal',
        'data-id' => $aRow['id'],
    ]);
    $row[] = $options .= icon_btn('sales_stages/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    $output['aaData'][] = $row;
}
