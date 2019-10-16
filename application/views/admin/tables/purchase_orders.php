<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('purchase_orders_model');

$aColumns = [
    'order_number',
    'suppliers.company as supplier_company',
    'client.company as client_company',
    'warehouse.name as warehouse_name',
    'pe_number',
    'po_date',
    'currencies.name as currency_name',
    'currency_rate',
    'status',
    'total',
    'created_at'
];
$sIndexColumn = 'id';
$sTable = 'tblpurchaseorders';
$where = [];
// Add blank where all filter can be stored
$filter = [];

$join = [
    'LEFT JOIN tblsuppliers AS suppliers ON suppliers.id=tblpurchaseorders.supplier',
    'LEFT JOIN tblclients AS client ON client.userid=tblpurchaseorders.clientid',
    'LEFT JOIN tblcustomerwarehouses AS warehouse ON warehouse.id=tblpurchaseorders.warehouse',
    'LEFT JOIN tblcurrencies AS currencies ON currencies.id=tblpurchaseorders.currency'
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblpurchaseorders.id'
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $url = admin_url('purchase_orders/view/' . $aRow['id']);
    $order_number = '<a href="' . $url . '">' . $aRow['order_number'] . '</a>';
    $order_number .= '<div class="row-options">';
    if ($aRow['status'] == 1) {
        $order_number .= '<a href="' . admin_url('purchase_orders/approve/' . $aRow['id']) . '">' . _l('approve') . '</a> | ';
    }
    $order_number .= '<a href="' . admin_url('purchase_orders/order/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    $order_number .= ' | <a href="' . admin_url('purchase_orders/delete/' . $aRow['id']) . '">' . _l('delete') . '</a>';
    $order_number .= '</div>';
    $row[] = $order_number;
    $row[] = $aRow['supplier_company'];
    $row[] = $aRow['client_company'];
    $row[] = $aRow['warehouse_name'];
    $row[] = $aRow['pe_number'];
    $row[] = _d(date('Y-m-d', strtotime($aRow['po_date'])));
    $row[] = $aRow['currency_name'];
    $row[] = $aRow['currency_rate'];
    $row[] = $this->ci->purchase_orders_model->get_status_name($aRow['status']);
    $row[] = $aRow['total'];
    $row[] = $aRow['created_at'];


    $hook = do_action('customers_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook['output'];

    $output['aaData'][] = $row;
}
