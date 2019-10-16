<?php
defined('BASEPATH') or exit('No direct script access allowed');

function is_empty_supplier_company($id)
{
    $CI = &get_instance();
    $CI->db->select('company');
    $CI->db->from('tblsuppliers');
    $CI->db->where('id', $id);
    $row = $CI->db->get()->row();
    if ($row) {
        if ($row->company == '') {
            return true;
        }

        return false;
    }

    return true;
}

function is_supplier_admin($id, $staff_id = '')
{
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $CI = &get_instance();
    $cache = $CI->object_cache->get($id . '-is-supplier-admin-' . $staff_id);

    if ($cache) {
        return $cache['retval'];
    }

    $total = total_rows('tblsupplieradmins', [
        'supplier_id' => $id,
        'staff_id' => $staff_id,
    ]);

    $retval = $total > 0 ? true : false;
    $CI->object_cache->add($id . '-is-supplier-admin-' . $staff_id, ['retval' => $retval]);

    return $retval;
}

function get_supplier_profile_tabs($supplier_id)
{
    $supplier_tabs = [
        [
            'name' => 'profile',
            'url' => admin_url('suppliers/supplier/' . $supplier_id . '?group=profile'),
            'icon' => 'fa fa-user-circle',
            'lang' => _l('supplier_add_edit_profile'),
            'visible' => true,
            'order' => 1,
        ],
        [
            'name' => 'contacts',
            'url' => admin_url('suppliers/supplier/' . $supplier_id . '?group=contacts'),
            'icon' => 'fa fa-users',
            'lang' => !is_empty_supplier_company($supplier_id) || empty($supplier_id) ? _l('supplier_contacts') : _l('contact'),
            'visible' => true,
            'order' => 2,
        ]
    ];

    $hook_data = do_action('supplier_profile_tabs', ['tabs' => $supplier_tabs, 'supplier_id' => $supplier_id]);
    $supplier_tabs = $hook_data['tabs'];

    usort($supplier_tabs, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $supplier_tabs;
}

function supplier_have_transactions($id)
{
    $total_transactions = 0;

    if ($total_transactions > 0) {
        return true;
    }

    return false;
}

function get_supplier_company_name($userid, $prevent_empty_company = false)
{
    $_userid = get_client_user_id();
    if ($userid !== '') {
        $_userid = $userid;
    }
    $CI = &get_instance();

    $select = ($prevent_empty_company == false ? get_sql_select_supplier_company() : 'company');

    $supplier = $CI->db->select($select)
        ->where('id', $_userid)
        ->from('tblsuppliers')
        ->get()
        ->row();
    if ($supplier) {
        return $supplier->company;
    }

    return '';
}

function supplier_contact_profile_image_url($contact_id, $type = 'small')
{
    $url = base_url('assets/images/user-placeholder.jpg');
    $CI = &get_instance();
    $path = $CI->object_cache->get('supplier-contact-profile-image-path-' . $contact_id);

    if (!$path) {
        $CI->object_cache->add('supplier-contact-profile-image-path-' . $contact_id, $url);

        $CI->db->select('profile_image');
        $CI->db->from('tblsuppliercontacts');
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->get()->row();

        if ($contact && !empty($contact->profile_image)) {
            $path = 'uploads/supplier_profile_images/' . $contact_id . '/' . $type . '_' . $contact->profile_image;
            $CI->object_cache->set('supplier-contact-profile-image-path-' . $contact_id, $path);
        }
    }

    if ($path && file_exists($path)) {
        $url = base_url($path);
    }

    return $url;
}
