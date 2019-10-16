<?php
$table_data = array(
    _l('client_firstname'),
    _l('client_lastname'),
    _l('client_email'),
    _l('clients_list_company'),
    _l('client_phonenumber'),
    _l('contact_position'),
    _l('opportunity_contact_note'),
    _l('clients_list_last_login'),
);
$custom_fields = get_custom_fields('contacts', array('show_on_table' => 1));
foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = do_action('opportunity_contacts_table_columns', $table_data);

render_datatable($table_data, isset($class) ? $class : 'opportunity-contacts', [], [
    'id' => 'table-opportunity-contacts',
    'data-url' => $url,
    'data-last-order-identifier' => 'opportunity-contacts',
    'data-default-order' => get_table_last_order('opportunity-contacts'),
]);

add_action('after_js_scripts_render', 'init_opportunity_contacts_table_js');

function init_opportunity_contacts_table_js()
{
    ?>
    <script>
        $(function () {
            var opportunitycontactsServerParams = {};
            $.each($('._hidden_inputs._filters input'), function () {
                opportunitycontactsServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
            });
            var url = $('#table-opportunity-contacts').data('url');
            initDataTable('.table-opportunity-contacts', url, undefined, undefined, opportunitycontactsServerParams, <?php echo do_action('opportunity_contacts_table_default_order', json_encode(array(5, 'desc'))); ?>);
        });
    </script>
    <?php
}
?>
