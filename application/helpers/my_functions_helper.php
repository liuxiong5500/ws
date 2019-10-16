<?php
add_action('after_render_single_aside_menu', 'my_custom_menu_items');

function my_custom_menu_items($order)
{
    if ($order == 2) {
        echo '<li><a href="' . admin_url('opportunities') . '"><i class="fa fa-bars menu-icon"></i>' . _l('opportunities_menu') . '</a></li>';
    }
    if ($order == 3) {
        echo '<li><a href="' . admin_url('purchase_orders') . '"><i class="fa fa-bars menu-icon"></i>' . _l('purchase_orders_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('suppliers') . '"><i class="fa fa-user-o menu-icon"></i>' . _l('suppliers_menu') . '</a></li>';
        echo '<li>';
        echo '<a href="#" aria-expanded="false"><i class="fa fa-bars menu-icon"></i>' . _l('process') . '<span class="fa arrow"></span></a>';
        echo '<ul class="nav nav-second-level collapse" aria-expanded="false">';
        echo '<li><a href="' . admin_url('process') . '">' . _l('process_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('process/sub') . '">' . _l('process_sub') . '</a></li>';
        echo '</ul>';
        echo '</li>';
    }
    if ($order == 13) {
        echo '<li>';
        echo '<a href="#" aria-expanded="false"><i class="fa fa-cog menu-icon"></i>' . _l('ext_setup_menu') . '<span class="fa arrow"></span></a>';
        echo '<ul class="nav nav-second-level collapse" aria-expanded="false">';
        echo '<li><a href="' . admin_url('departments') . '">' . _l('departments') . '</a></li>';
        echo '<li><a href="' . admin_url('positions') . '">' . _l('positions_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('levels') . '">' . _l('levels_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('campaigns/types') . '">' . _l('campaigns_types_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('campaigns') . '">' . _l('campaigns_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('sales_stages') . '">' . _l('sales_stages_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('shipment_terms') . '">' . _l('shipment_terms_menu') . '</a></li>';
        echo '<li><a href="' . admin_url('payment_terms') . '">' . _l('payment_terms_menu') . '</a></li>';
        echo '</ul>';
        echo '</li>';
    }
}
