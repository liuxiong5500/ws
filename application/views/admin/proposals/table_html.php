<?php
$table_data = array(
    _l('proposal') . ' #',
    _l('proposal_subject'),
    _l('proposal_to'),
    _l('proposal_opportunity'),
    _l('proposal_total'),
    _l('proposal_date'),
    _l('proposal_open_till'),
    _l('tags'),
    _l('proposal_date_created'),
    _l('proposal_status'),
);

$custom_fields = get_custom_fields('proposal', array('show_on_table' => 1));
foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = do_action('proposals_table_columns', $table_data);

render_datatable($table_data, 'proposals', [], [
    'id' => 'table-proposals',
    'data-url' => $url,
    'data-last-order-identifier' => 'proposals',
    'data-default-order' => get_table_last_order('proposals'),
]);

add_action('after_js_scripts_render', 'init_proposals_table_js');

function init_proposals_table_js()
{
    ?>
    <script>
        $(function () {
            var ProposalsServerParams = {};
            $.each($('._hidden_inputs._filters input'), function () {
                ProposalsServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
            });
            var url = $('#table-proposals').data('url');
            initDataTable('.table-proposals', url, undefined, undefined, ProposalsServerParams, <?php echo do_action('proposals_table_default_order', json_encode(array(6, 'desc'))); ?>);
        });
    </script>
    <?php
}
