<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Default opportunity tabs
 * @param  mixed $opportunity_id opportunity id to format the url
 * @return array
 */
function get_opportunity_tabs_admin($opportunity_id, $rel_type)
{
    if ($rel_type == 'lead') {
        $visible_invoices = false;
        $visible_expenses = false;
    } else {
        $visible_invoices = (has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own') || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices()));
        $visible_expenses = has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own');
    }
    $opportunity_tabs = [
        [
            'name' => 'opportunity_overview',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_overview'),
            'icon' => 'fa fa-th',
            'lang' => _l('opportunity_overview'),
            'visible' => true,
            'order' => 1,
        ],
        [
            'name' => 'opportunity_tasks',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_tasks'),
            'icon' => 'fa fa-check-circle',
            'lang' => _l('tasks'),
            'visible' => true,
            'order' => 2,
            'linked_to_customer_option' => ['view_tasks'],
        ],
        [
            'name' => 'opportunity_timesheets',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_timesheets'),
            'icon' => 'fa fa-clock-o',
            'lang' => _l('opportunity_timesheets'),
            'visible' => true,
            'order' => 3,
            'linked_to_customer_option' => ['view_timesheets'],
        ],
        [
            'name' => 'opportunity_milestones',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_milestones'),
            'icon' => 'fa fa-rocket',
            'lang' => _l('opportunity_milestones'),
            'visible' => true,
            'order' => 4,
            'linked_to_customer_option' => ['view_milestones'],
        ],
        [
            'name' => 'opportunity_files',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_files'),
            'icon' => 'fa fa-files-o',
            'lang' => _l('opportunity_files'),
            'visible' => true,
            'order' => 5,
            'linked_to_customer_option' => ['upload_files'],
        ],
        [
            'name' => 'opportunity_discussions',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_discussions'),
            'icon' => 'fa fa-commenting',
            'lang' => _l('opportunity_discussions'),
            'visible' => true,
            'order' => 6,
            'linked_to_customer_option' => ['open_discussions'],
        ],
        [
            'name' => 'opportunity_gantt',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_gantt'),
            'icon' => 'fa fa-line-chart',
            'lang' => _l('opportunity_gant'),
            'visible' => true,
            'order' => 7,
            'linked_to_customer_option' => ['view_gantt'],
        ],
        [
            'name' => 'opportunity_tickets',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_tickets'),
            'icon' => 'fa fa-life-ring',
            'lang' => _l('opportunity_tickets'),
            'visible' => (get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member(),
            'order' => 8,
        ],
        [
            'name' => 'sales',
            'url' => '#',
            'icon' => '',
            'lang' => _l('sales_string'),
            'visible' => (has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own') || (get_option('allow_staff_view_estimates_assigned') == 1 && staff_has_assigned_estimates())) || (has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own') || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices())) || (has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own')),
            'order' => 9,
            'dropdown' => [
                [
                    'name' => 'opportunity_proposals',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_proposals'),
                    'icon' => 'fa fa-sun-o',
                    'lang' => _l('opportunity_proposals'),
                    'visible' => (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || (get_option('allow_staff_view_proposals_assigned') == 1 && staff_has_assigned_proposals())),
                    'order' => 1,
                ],
                [
                    'name' => 'opportunity_invoices',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_invoices'),
                    'icon' => 'fa fa-sun-o',
                    'lang' => _l('opportunity_invoices'),
                    'visible' => $visible_invoices,
                    'order' => 2,
                ],
                [
                    'name' => 'opportunity_estimates',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_estimates'),
                    'icon' => 'fa fa-sun-o',
                    'lang' => _l('estimates'),
                    'visible' => (has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own') || (get_option('allow_staff_view_estimates_assigned') == 1 && staff_has_assigned_estimates())),
                    'order' => 3,
                ],
                [
                    'name' => 'opportunity_projects',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_projects'),
                    'icon' => 'fa fa-sun-o',
                    'lang' => _l('opportunity_projects'),
                    'visible' => (has_permission('projects', '', 'view') || has_permission('projects', '', 'create')),
                    'order' => 4,
                ],
                [
                    'name' => 'opportunity_expenses',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_expenses'),
                    'icon' => 'fa fa-sort-amount-asc',
                    'lang' => _l('opportunity_expenses'),
                    'visible' => $visible_expenses,
                    'order' => 5,
                ],
                [
                    'name' => 'opportunity_credit_notes',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_credit_notes'),
                    'icon' => 'fa fa-sort-amount-asc',
                    'lang' => _l('credit_notes'),
                    'visible' => has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own'),
                    'order' => 6,
                ],
                [
                    'name' => 'opportunity_subscriptions',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_subscriptions'),
                    'icon' => 'fa fa-reload',
                    'lang' => _l('subscriptions'),
                    'visible' => has_permission('subscriptions', '', 'view') || has_permission('subscriptions', '', 'view_own'),
                    'order' => 7,
                ],
                [
                    'name' => 'opportunity_contacts',
                    'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_contacts'),
                    'icon' => 'fa fa-sun-o',
                    'lang' => _l('opportunity_contacts'),
                    'visible' => $visible_invoices,
                    'order' => 8,
                ]
            ],
        ],
        [
            'name' => 'opportunity_notes',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_notes'),
            'icon' => 'fa fa-clock-o',
            'lang' => _l('opportunity_notes'),
            'visible' => true,
            'order' => 10,
        ],
        [
            'name' => 'opportunity_activity',
            'url' => admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_activity'),
            'icon' => 'fa fa-exclamation',
            'lang' => _l('opportunity_activity'),
            'visible' => has_permission('opportunities', '', 'create'),
            'order' => 11,
            'linked_to_customer_option' => ['view_activity_log'],
        ],
    ];

    $opportunity_tabs = do_action('opportunity_tabs_admin', $opportunity_tabs);

    usort($opportunity_tabs, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $opportunity_tabs;
}

/**
 * Get opportunity status by passed opportunity id
 * @param  mixed $id opportunity id
 * @return array
 */
function get_opportunity_status_by_id($id)
{
    $CI = &get_instance();
    if (!class_exists('opportunities_model')) {
        $CI->load->model('opportunities_model');
    }

    $statuses = $CI->opportunities_model->get_opportunity_statuses();

    $status = [
        'id' => 0,
        'color' => '#333',
        'name' => '[Status Not Found]',
        'order' => 1,
    ];

    foreach ($statuses as $s) {
        if ($s['id'] == $id) {
            $status = $s;

            break;
        }
    }

    return $status;
}

/**
 * Return logged in user pinned opportunities
 * @return array
 */
function get_user_pinned_opportunities()
{
    $CI = &get_instance();
    $CI->db->select('tblopportunities.id, tblopportunities.name');
    $CI->db->join('tblopportunities', 'tblopportunities.id=tblpinnedopportunities.opportunity_id');
    $CI->db->where('tblpinnedopportunities.staff_id', get_staff_user_id());
    $opportunities = $CI->db->get('tblpinnedopportunities')->result_array();
    $CI->load->model('opportunities_model');
    $i = 0;
    foreach ($opportunities as $opportunity) {
        $opportunities[$i]['progress'] = $CI->opportunities_model->calc_progress($opportunity['id']);
        $i++;
    }

    return $opportunities;
}

/**
 * Get opportunity name by passed id
 * @param  mixed $id
 * @return string
 */
function get_opportunity_name_by_id($id)
{
    $CI = &get_instance();
    $opportunity = $CI->object_cache->get('opportunity-name-data-' . $id);

    if (!$opportunity) {
        $CI->db->select('name');
        $CI->db->where('id', $id);
        $opportunity = $CI->db->get('tblopportunities')->row();
        $CI->object_cache->add('opportunity-name-data-' . $id, $opportunity);
    }

    if ($opportunity) {
        return $opportunity->name;
    }

    return '';
}

/**
 * Return opportunity milestones
 * @param  mixed $opportunity_id opportunity id
 * @return array
 */
function get_opportunity_milestones($opportunity_id)
{
    $CI = &get_instance();
    $CI->db->where('opportunity_id', $opportunity_id);
    $CI->db->order_by('milestone_order', 'ASC');

    return $CI->db->get('tblmilestones')->result_array();
}

/**
 * Get opportunity client id by passed opportunity id
 * @param  mixed $id opportunity id
 * @return mixed
 */
function get_client_id_by_opportunity_id($id)
{
    $CI = &get_instance();
    $CI->db->select('rel_id');
    $CI->db->where('id', $id);
    $opportunity = $CI->db->get('tblopportunities')->row();
    if ($opportunity) {
        return $opportunity->rel_id;
    }

    return false;
}

/**
 * Check if customer has opportunity assigned
 * @param  mixed $customer_id customer id to check
 * @return boolean
 */
function customer_has_opportunities($customer_id)
{
    $totalCustomeropportunities = total_rows('tblopportunities');

    return ($totalCustomeropportunities > 0 ? true : false);
}

/**
 * Get projcet billing type
 * @param  mixed $opportunity_id
 * @return mixed
 */
function get_opportunity_billing_type($opportunity_id)
{
    $CI = &get_instance();
    $CI->db->where('id', $opportunity_id);
    $opportunity = $CI->db->get('tblopportunities')->row();
    if ($opportunity) {
        return $opportunity->billing_type;
    }

    return false;
}

/**
 * Translated jquery-comment language based on app languages
 * This feature is used on both admin and customer area
 * @return array
 */
function get_opportunity_discussions_language_array()
{
    $lang = [
        'discussion_add_comment' => _l('discussion_add_comment'),
        'discussion_newest' => _l('discussion_newest'),
        'discussion_oldest' => _l('discussion_oldest'),
        'discussion_attachments' => _l('discussion_attachments'),
        'discussion_send' => _l('discussion_send'),
        'discussion_reply' => _l('discussion_reply'),
        'discussion_edit' => _l('discussion_edit'),
        'discussion_edited' => _l('discussion_edited'),
        'discussion_you' => _l('discussion_you'),
        'discussion_save' => _l('discussion_save'),
        'discussion_delete' => _l('discussion_delete'),
        'discussion_view_all_replies' => _l('discussion_view_all_replies'),
        'discussion_hide_replies' => _l('discussion_hide_replies'),
        'discussion_no_comments' => _l('discussion_no_comments'),
        'discussion_no_attachments' => _l('discussion_no_attachments'),
        'discussion_attachments_drop' => _l('discussion_attachments_drop'),
    ];

    return $lang;
}

function prepare_opportunities_for_export($customer_id, $contact_id)
{
    $CI = &get_instance();

    if (!class_exists('opportunities_model')) {
        $CI->load->model('opportunities_model');
    }

    $valAllowed = get_option('gdpr_contact_data_portability_allowed');
    if (empty($valAllowed)) {
        $valAllowed = [];
    } else {
        $valAllowed = unserialize($valAllowed);
    }

    $CI->db->where('clientid', $customer_id);
    $opportunities = $CI->db->get('tblopportunities')->result_array();

    $CI->db->where('show_on_client_portal', 1);
    $CI->db->where('fieldto', 'opportunities');
    $CI->db->order_by('field_order', 'asc');
    $custom_fields = $CI->db->get('tblcustomfields')->result_array();

    foreach ($opportunities as $opportunitiesKey => $opportunity) {
        if (in_array('related_tasks', $valAllowed)) {
            $sql = 'SELECT * FROM tblstafftasks WHERE (rel_id="' . $opportunity['id'] . '" AND rel_type="opportunity"';
            $sql .= ' AND addedfrom=' . $contact_id . ' AND is_added_from_contact=1) OR (id IN (SELECT(taskid) FROM tblstafftaskcomments WHERE contact_id=' . $contact_id . '))';
            $tasks = $CI->db->query($sql)->result_array();

            foreach ($tasks as $taskKey => $task) {
                $CI->db->where('taskid', $task['id']);
                $CI->db->where('contact_id', $contact_id);
                $tasks[$taskKey]['comments'] = $CI->db->get('tblstafftaskcomments')->result_array();
            }
            $opportunities[$opportunitiesKey]['tasks'] = $tasks;
        }

        if (in_array('related_discussions', $valAllowed)) {
            $sql = 'SELECT * FROM tblopportunitydiscussions WHERE (opportunity_id="' . $opportunity['id'] . '"';
            $sql .= ' AND contact_id=' . $contact_id . ') OR (id IN (SELECT(discussion_id) FROM tblopportunitydiscussioncomments WHERE contact_id=' . $contact_id . ' AND discussion_type="regular"))';

            $discussions = $CI->db->query($sql)->result_array();

            foreach ($discussions as $discussionKey => $discussion) {
                $CI->db->where('discussion_id', $discussion['id']);
                $CI->db->where('discussion_type', 'regular');
                $CI->db->where('contact_id', $contact_id);
                $discussions[$discussionKey]['comments'] = $CI->db->get('tblopportunitydiscussioncomments')->result_array();
            }

            $opportunities[$opportunitiesKey]['discussions'] = $discussions;
        }

        if (in_array('opportunities_activity_log', $valAllowed)) {
            $CI->db->where('opportunity_id', $opportunity['id']);
            $CI->db->where('contact_id', $contact_id);
            $opportunities[$opportunitiesKey]['activity'] = $CI->db->get('tblopportunityactivity')->result_array();
        }

        $opportunities[$opportunitiesKey]['additional_fields'] = [];
        foreach ($custom_fields as $cf) {
            $opportunities[$opportunitiesKey]['additional_fields'][] = [
                'name' => $cf['name'],
                'value' => get_custom_field_value($opportunity['id'], $cf['id'], 'opportunities'),
            ];
        }
    }

    return $opportunities;
}
