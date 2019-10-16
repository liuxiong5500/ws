<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'tblcampaigns.name',
    'tblcampaigntypes.name',
    'date_from',
    'date_to',
    'budget_cost',
    'expected_sale',
];
$sIndexColumn = 'id';
$sTable = 'tblcampaigns';
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [
    'LEFT JOIN tblcampaigntypes on tblcampaigntypes.id = tblcampaigns.type_id'
], [], [
    'tblcampaigns.id',
    'type_id',
    'date_from',
    'date_to',
    'description'
]);
$output = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'tblcampaigns.name') {
            $_data = '<span class="name" data-description="' . $aRow['description'] . '"><a href="#" data-toggle="modal" data-target="#campaign_modal" data-id="' . $aRow['id'] . '">' . $_data . '</a></span>';
        }
        if ($aColumns[$i] == 'tblcampaigntypes.name') {
            $_data = '<span class="name" data-id="' . $aRow['type_id'] . '">' . $_data . '</span>';
        }
        if ($aColumns[$i] == 'date_from' || $aColumns[$i] == 'date_to') {
            $_data = _dt($_data);
        }
        if ($aColumns[$i] == 'budget_cost') {
            $_data = $_data != '' ? $_data : '';
        }
        if ($aColumns[$i] == 'expected_sale') {
            $_data = $_data != '' ? $_data : '';
        }
        $row[] = $_data;
    }

    $options = icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', [
        'data-toggle' => 'modal',
        'data-target' => '#campaign_modal',
        'data-id' => $aRow['id'],
    ]);
    $row[] = $options .= icon_btn('campaigns/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    $output['aaData'][] = $row;
}
