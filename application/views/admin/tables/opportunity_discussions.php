<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'subject',
    'last_activity',
    '(SELECT COUNT(*) FROM tblopportunitydiscussioncomments WHERE discussion_id = tblopportunitydiscussions.id AND discussion_type="regular")',
    'show_to_customer',
    ];
$sIndexColumn = 'id';
$sTable       = 'tblopportunitydiscussions';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], ['AND opportunity_id=' . $opportunity_id], [
    'id',
    'description',
    ]);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_discussions&discussion_id=' . $aRow['id']) . '">' . $_data . '</a>';
            if (has_permission('opportunities', '', 'edit') || has_permission('opportunities', '', 'delete')) {
                $_data .= '<div class="row-options">';
                if (has_permission('opportunities', '', 'edit')) {
                    $_data .= '<a href="#" onclick="edit_discussion(this,' . $aRow['id'] . '); return false;" data-subject="'.$aRow['subject'].'" data-description="'.clear_textarea_breaks($aRow['description']).'" data-show-to-customer="'.$aRow['show_to_customer'].'">'._l('edit').'</a>';
                }
                if (has_permission('opportunities', '', 'delete')) {
                     $_data .= (has_permission('opportunities', '', 'edit') ? ' | ' : '') . '<a href="#" onclick="delete_opportunity_discussion(' . $aRow['id'] . '); return false;" class="text-danger">'._l('delete').'</a>';
                }
                $_data .= '</div>';
            }
        } elseif ($aColumns[$i] == 'show_to_customer') {
            if ($_data == 1) {
                $_data = _l('opportunity_discussion_visible_to_customer_yes');
            } else {
                $_data = _l('opportunity_discussion_visible_to_customer_no');
            }
        } elseif ($aColumns[$i] == 'last_activity') {
            if (!is_null($_data)) {
                $_data = '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($_data) . '">' . time_ago($_data) . '</span>';
            } else {
                $_data = _l('opportunity_discussion_no_activity');
            }
        }
        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
