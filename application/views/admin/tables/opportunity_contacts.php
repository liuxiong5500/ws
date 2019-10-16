<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'firstname',
    'lastname',
    'email',
    'company',
    'tblcontacts.phonenumber as phonenumber',
    'title',
    'last_login',
    'note',
];

$sIndexColumn = 'id';
$sTable = 'tblopportunitycontacts';
$join = [
    'JOIN tblcontacts ON tblcontacts.id=tblopportunitycontacts.contact_id',
    'JOIN tblclients ON tblclients.userid=tblcontacts.userid'
];

$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblopportunitycontacts.id',
    'contact_id',
    'opportunity_id',
    'tblclients.userid',
    'is_primary',
    'registration_confirmed',
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $rowName = '<img src="' . contact_profile_image_url($aRow['contact_id']) . '" class="client-profile-image-small mright5"><a href="#" onclick="opportunity_contact_request(\'' . admin_url('opportunities/get_contact?opportunity_id=' . $aRow['opportunity_id'] . '&id=' . $aRow['id']) . '\')">' . $aRow['firstname'] . '</a>';

    $rowName .= '<div class="row-options">';

    $rowName .= '<a href="#" onclick="opportunity_contact_request(\'' . admin_url('opportunities/get_contact?opportunity_id=' . $aRow['opportunity_id'] . '&id=' . $aRow['id']) . '\')">' . _l('edit') . '</a>';

    $rowName .= ' | <a href="' . admin_url('opportunities/delete_contact/' . $aRow['id'] . '/' . $aRow['opportunity_id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';

    $rowName .= '</div>';

    $row[] = $rowName;

    $row[] = $aRow['lastname'];

    $row[] = '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>';

    if (!empty($aRow['company'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['userid']) . '">' . $aRow['company'] . '</a>';
    } else {
        $row[] = '';
    }

    $row[] = '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>';

    $row[] = $aRow['title'];
    $row[] = $aRow['note'];
    $row[] = (!empty($aRow['last_login']) ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($aRow['last_login']) . '">' . time_ago($aRow['last_login']) . '</span>' : '');
    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    if ($aRow['registration_confirmed'] == 0) {
        $row['DT_RowClass'] .= ' alert-info requires-confirmation';
        $row['Data_Title'] = _l('customer_requires_registration_confirmation');
        $row['Data_Toggle'] = 'tooltip';
    }
    $output['aaData'][] = $row;
}
