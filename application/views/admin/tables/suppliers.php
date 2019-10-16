<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'tblsuppliers.id as id',
    'company',
    'CONCAT(firstname, " ", lastname) as contact_fullname',
    'email',
    'tblsuppliers.phonenumber as phonenumber',
    'tblsuppliers.active',
    '(SELECT GROUP_CONCAT(name ORDER BY name ASC) FROM tblsuppliersgroups JOIN tblsuppliergroups_in ON tblsuppliergroups_in.groupid = tblsuppliersgroups.id WHERE supplier_id = tblsuppliers.id LIMIT 1) as groups',
    'tblsuppliers.datecreated as datecreated',
];

$sIndexColumn = 'id';
$sTable = 'tblsuppliers';
$where = [];
// Add blank where all filter can be stored
$filter = [];

$join = ['LEFT JOIN tblsuppliercontacts ON tblsuppliercontacts.supplier_id=tblsuppliers.id AND tblsuppliercontacts.is_primary=1'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblsuppliercontacts.id as contact_id',
    'tblsuppliers.zip as zip',
    'registration_confirmed',
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    // Company
    $company = $aRow['company'];
    $isPerson = false;

    if ($company == '') {
        $company = _l('no_company_view_profile');
        $isPerson = true;
    }

    $url = admin_url('suppliers/supplier/' . $aRow['id']);

    if ($isPerson && $aRow['contact_id']) {
        $url .= '?contactid=' . $aRow['contact_id'];
    }

    $company = '<a href="' . $url . '">' . $company . '</a>';

    $company .= '<div class="row-options">';
    $company .= '<a href="' . $url . '">' . _l('view') . '</a>';

    if ($aRow['registration_confirmed'] == 0 && is_admin()) {
        $company .= ' | <a href="' . admin_url('suppliers/confirm_registration/' . $aRow['id']) . '" class="text-success bold">' . _l('confirm_registration') . '</a>';
    }
    if (!$isPerson) {
        $company .= ' | <a href="' . admin_url('suppliers/supplier/' . $aRow['id'] . '?group=contacts') . '">' . _l('customer_contacts') . '</a>';
    }
    $company .= ' | <a href="' . admin_url('suppliers/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';

    $company .= '</div>';

    $row[] = $company;

    // Primary contact
    $row[] = ($aRow['contact_id'] ? '<a href="' . admin_url('suppliers/supplier/' . $aRow['id'] . '?contactid=' . $aRow['contact_id']) . '" target="_blank">' . $aRow['contact_fullname'] . '</a>' : '');

    // Primary contact email
    $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    // Primary contact phone
    $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    // Toggle active/inactive customer
    $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('customer_active_inactive_help') . '">
        <input type="checkbox"' . ($aRow['registration_confirmed'] == 0 ? ' disabled' : '') . ' data-switch-url="' . admin_url() . 'suppliers/change_supplier_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . ($aRow['tblsuppliers.active'] == 1 ? 'checked' : '') . '>
        <label class="onoffswitch-label" for="' . $aRow['id'] . '"></label>
    </div>';

    // For exporting
    $toggleActive .= '<span class="hide">' . ($aRow['tblsuppliers.active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

    $row[] = $toggleActive;

    // Customer groups parsing
    $groupsRow = '';
    if ($aRow['groups']) {
        $groups = explode(',', $aRow['groups']);
        foreach ($groups as $group) {
            $groupsRow .= '<span class="label label-default mleft5 inline-block customer-group-list pointer">' . $group . '</span>';
        }
    }

    $row[] = $groupsRow;

    $row[] = _dt($aRow['datecreated']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('customers_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook['output'];

    $row['DT_RowClass'] = 'has-row-options';
    if ($aRow['registration_confirmed'] == 0) {
        $row['DT_RowClass'] .= ' alert-info requires-confirmation';
        $row['Data_Title'] = _l('customer_requires_registration_confirmation');
        $row['Data_Toggle'] = 'tooltip';
    }
    $output['aaData'][] = $row;
}
