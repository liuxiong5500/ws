<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionEdit = has_permission('opportunities', '', 'edit');
$hasPermissionDelete = has_permission('opportunities', '', 'delete');
$hasPermissionCreate = has_permission('opportunities', '', 'create');

$aColumns = [
    'tblopportunities.id as id',
    'name',
    'rel_to',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblopportunities.id and rel_type="opportunity" ORDER by tag_order ASC) as tags',
    'start_date',
    'deadline',
    '(SELECT GROUP_CONCAT(CONCAT(firstname, \' \', lastname) SEPARATOR ",") FROM tblopportunitymembers JOIN tblstaff on tblstaff.staffid = tblopportunitymembers.staff_id WHERE opportunity_id=tblopportunities.id ORDER BY staff_id) as members',
    'status',
];


$sIndexColumn = 'id';
$sTable = 'tblopportunities';

$join = [
];

$where = [];
$filter = [];


if (!has_permission('opportunities', '', 'view') || $this->ci->input->post('my_opportunities')) {
    array_push($where, ' AND tblopportunities.id IN (SELECT opportunity_id FROM tblopportunitymembers WHERE staff_id=' . get_staff_user_id() . ')');
}

$statusIds = [];

foreach ($this->ci->opportunities_model->get_opportunity_statuses() as $status) {
    if ($this->ci->input->post('opportunity_status_' . $status['id'])) {
        array_push($statusIds, $status['id']);
    }
}

if (count($statusIds) > 0) {
    array_push($filter, 'OR status IN (' . implode(', ', $statusIds) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

$custom_fields = get_table_custom_fields('opportunities');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblopportunities.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$aColumns = do_action('opportunities_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
//    'clientid',
    '(SELECT GROUP_CONCAT(staff_id SEPARATOR ",") FROM tblopportunitymembers WHERE opportunity_id=tblopportunities.id ORDER BY staff_id) as members_ids',
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('opportunities/view/' . $aRow['id']);

    $row[] = '<a href="' . $link . '">' . $aRow['id'] . '</a>';

    $name = '<a href="' . $link . '">' . $aRow['name'] . '</a>';

    $name .= '<div class="row-options">';

    $name .= '<a href="' . $link . '">' . _l('view') . '</a>';

    if ($hasPermissionCreate) {
        $name .= ' | <a href="#" onclick="copy_opportunity(' . $aRow['id'] . ');return false;">' . _l('copy_opportunity') . '</a>';
    }

    if ($hasPermissionEdit) {
        $name .= ' | <a href="' . admin_url('opportunities/opportunity/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }

    if ($hasPermissionDelete) {
        $name .= ' | <a href="' . admin_url('opportunities/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $name .= '</div>';

    $row[] = $name;

    $row[] = $aRow['rel_to'];

    $row[] = render_tags($aRow['tags']);

    $row[] = _d($aRow['start_date']);

    $row[] = _d($aRow['deadline']);

    $membersOutput = '';

    $members = explode(',', $aRow['members']);
    $exportMembers = '';
    foreach ($members as $key => $member) {
        if ($member != '') {
            $members_ids = explode(',', $aRow['members_ids']);
            $member_id = $members_ids[$key];
            $membersOutput .= '<a href="' . admin_url('profile/' . $member_id) . '">' .
                staff_profile_image($member_id, [
                    'staff-profile-image-small mright5',
                ], 'small', [
                    'data-toggle' => 'tooltip',
                    'data-title' => $member,
                ]) . '</a>';
            // For exporting
            $exportMembers .= $member . ', ';
        }
    }

    $membersOutput .= '<span class="hide">' . trim($exportMembers, ', ') . '</span>';
    $row[] = $membersOutput;

    $status = get_opportunity_status_by_id($aRow['status']);
    $row[] = '<span class="label label inline-block opportunity-status-' . $aRow['status'] . '" style="color:' . $status['color'] . ';border:1px solid ' . $status['color'] . '">' . $status['name'] . '</span>';

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('opportunities_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook['output'];
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
