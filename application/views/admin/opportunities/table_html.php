<?php

$table_data = [
    '#',
    _l('opportunity_name'),
    _l('opportunity_rel_to'),
    _l('tags'),
    _l('opportunity_start_date'),
    _l('opportunity_deadline'),
    _l('opportunity_members'),
    _l('opportunity_status'),
];

$custom_fields = get_custom_fields('opportunities', ['show_on_table' => 1]);
foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = do_action('opportunities_table_columns', $table_data);

render_datatable($table_data, isset($class) ? $class : 'opportunities', [], [
    'data-last-order-identifier' => 'opportunities',
    'data-default-order' => get_table_last_order('opportunities'),
]);
