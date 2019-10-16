<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'name'
];
$sIndexColumn = 'id';
$sTable = 'tblpositions';
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
            $_data = '<span class="name"><a href="#" data-toggle="modal" data-target="#position_modal" data-id="' . $aRow['id'] . '">' . $_data . '</a></span>';
        }
        $row[] = $_data;
    }
    $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', [
        'data-toggle' => 'modal',
        'data-target' => '#position_modal',
        'data-id' => $aRow['id'],
    ]);
    $row[] = $options .= icon_btn('positions/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    $output['aaData'][] = $row;
}
