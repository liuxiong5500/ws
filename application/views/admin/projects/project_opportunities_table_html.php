<?php
$table_data = [
    '#',
    _l('project_name'),
    [
        'name' => _l('project_customer'),
        'th_attrs' => ['class' => isset($client) ? 'not_visible' : ''],
    ],
    _l('tags'),
    _l('project_opportunity'),
    _l('project_start_date'),
    _l('project_deadline'),
    _l('project_members'),
    _l('project_status'),
];

$custom_fields = get_custom_fields('projects', ['show_on_table' => 1]);
foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = do_action('projects_table_columns', $table_data);

render_datatable($table_data, isset($class) ? $class : 'projects', [], [
    'id' => 'table-projects',
    'data-url' => $url,
    'data-last-order-identifier' => 'projects',
    'data-default-order' => get_table_last_order('projects'),
]);

add_action('after_js_scripts_render', 'init_projects_table_js');

function init_projects_table_js()
{
    ?>
    <script>
        $(function () {
            var projectsServerParams = {};
            $.each($('._hidden_inputs._filters input'), function () {
                projectsServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
            });
            var url = $('#table-projects').data('url');
            initDataTable('.table-projects', url, undefined, undefined, projectsServerParams, <?php echo do_action('projects_table_default_order', json_encode(array(6, 'desc'))); ?>);
        });
    </script>
    <?php
}
