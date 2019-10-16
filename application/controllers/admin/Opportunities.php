<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Opportunities extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('opportunities_model');
        $this->load->model('currencies_model');
        $this->load->model('sales_stages_model');
        $this->load->model('leads_model');
        $this->load->model('campaigns_model');
        $this->load->helper('date');
    }

    public function index()
    {
        close_setup_menu();
        $data['statuses'] = $this->opportunities_model->get_opportunity_statuses();
        $data['title'] = _l('opportunities');
        $this->load->view('admin/opportunities/manage', $data);
    }

    public function table()
    {
        $this->app->get_table_data('opportunities');
    }

    public function contacts_table()
    {
        $this->app->get_table_data('opportunity_contacts');
    }

    public function staff_opportunities()
    {
        $this->app->get_table_data('staff_opportunities');
    }

    public function expenses($id)
    {
        $this->load->model('expenses_model');
        $this->app->get_table_data('opportunity_expenses', [
            'opportunity_id' => $id,
        ]);
    }

    public function get_contact()
    {
        $opportunity_id = $this->input->get('opportunity_id');
        $id = $this->input->get('id');
        $data['title'] = _l('opportunity_contact_add_heading');
        $data['opportunity_id'] = $opportunity_id;
        if ($id) {
            $data['title'] = _l('opportunity_contact_edit_heading');
            $data['contact'] = $this->opportunities_model->get_contact($id);
        }
        $this->load->view('admin/opportunities/contact', $data);
    }

    public function contact()
    {
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $data = $this->input->post();
            if ($id == '') {
                $id = $this->opportunities_model->add_contact($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('opportunity_contact')));
                }
            } else {
                $success = $this->opportunities_model->update_contact($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('opportunity_contact')));
                }
            }
            redirect(admin_url('opportunities/view/' . $data['opportunity_id'] . '?group=opportunity_contacts'));
        }
    }

    public function delete_contact($id, $opportunity_id)
    {
        $this->opportunities_model->delete_contact($id);
        redirect(admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_contacts'));
    }

    public function add_expense()
    {
        if ($this->input->post()) {
            $this->load->model('expenses_model');
            $id = $this->expenses_model->add($this->input->post());
            if ($id) {
                set_alert('success', _l('added_successfully', _l('expense')));
                echo json_encode([
                    'url' => admin_url('opportunities/view/' . $this->input->post('opportunity_id') . '/?group=opportunity_expenses'),
                    'expenseid' => $id,
                ]);
                die;
            }
            echo json_encode([
                'url' => admin_url('opportunities/view/' . $this->input->post('opportunity_id') . '/?group=opportunity_expenses'),
            ]);
            die;
        }
    }

    public function opportunity($id = '')
    {
        if (!has_permission('opportunities', '', 'edit') && !has_permission('opportunities', '', 'create')) {
            access_denied('opportunities');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['description'] = $this->input->post('description', false);
            if ($id == '') {
                if (!has_permission('opportunities', '', 'create')) {
                    access_denied('opportunities');
                }
                $id = $this->opportunities_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('opportunity')));
                    redirect(admin_url('opportunities/view/' . $id));
                }
            } else {
                if (!has_permission('opportunities', '', 'edit')) {
                    access_denied('opportunities');
                }
                $success = $this->opportunities_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('opportunity')));
                }
                redirect(admin_url('opportunities/view/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('opportunity_lowercase'));
            $data['auto_select_billing_type'] = $this->opportunities_model->get_most_used_billing_type();
        } else {
            $data['opportunity'] = $this->opportunities_model->get($id);
            $data['opportunity']->settings->available_features = unserialize($data['opportunity']->settings->available_features);

            $data['opportunity_members'] = $this->opportunities_model->get_opportunity_members($id);
            $title = _l('edit', _l('opportunity_lowercase'));
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $data['last_opportunity_settings'] = $this->opportunities_model->get_last_opportunity_settings();

        if (count($data['last_opportunity_settings'])) {
            $key = array_search('available_features', array_column($data['last_opportunity_settings'], 'name'));
            $data['last_opportunity_settings'][$key]['value'] = unserialize($data['last_opportunity_settings'][$key]['value']);
        }

        $data['settings'] = $this->opportunities_model->get_settings();
        $data['statuses'] = $this->opportunities_model->get_opportunity_statuses();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['stages'] = $this->sales_stages_model->get();
        $data['types'] = [['id' => 'new', 'name' => _l('opportunity_new_business')], ['id' => 'exists', 'name' => _l('opportunity_existing_business')]];
        $data['sources'] = $this->leads_model->get_source();
        $data['campaigns'] = $this->campaigns_model->get();
        $data['title'] = $title;
        $this->load->view('admin/opportunities/opportunity', $data);
    }

    public function view($id)
    {
        if ($this->opportunities_model->is_member($id) || has_permission('opportunities', '', 'view')) {
            close_setup_menu();
            $opportunity = $this->opportunities_model->get($id);

            if (!$opportunity) {
                blank_page(_l('opportunity_not_found'));
            }

            $opportunity->settings->available_features = unserialize($opportunity->settings->available_features);
            $data['statuses'] = $this->opportunities_model->get_opportunity_statuses();

            if (!$this->input->get('group')) {
                $view = 'opportunity_overview';
            } else {
                $view = $this->input->get('group');
            }

            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

            $data['opportunity'] = $opportunity;
            $data['currency'] = $this->opportunities_model->get_currency($id);

            $data['opportunity_total_logged_time'] = $this->opportunities_model->total_logged_time($id);

            $data['staff'] = $this->staff_model->get('', ['active' => 1]);
            $percent = $this->opportunities_model->calc_progress($id);
            $data['bodyclass'] = '';
            if ($view == 'opportunity_overview') {
                $data['members'] = $this->opportunities_model->get_opportunity_members($id);
                $i = 0;
                foreach ($data['members'] as $member) {
                    $data['members'][$i]['total_logged_time'] = 0;
                    $member_timesheets = $this->tasks_model->get_unique_member_logged_task_ids($member['staff_id'], ' AND task_id IN (SELECT id FROM tblstafftasks WHERE rel_type="opportunity" AND rel_id="' . $id . '")');

                    foreach ($member_timesheets as $member_task) {
                        $data['members'][$i]['total_logged_time'] += $this->tasks_model->calc_task_total_time($member_task->task_id, ' AND staff_id=' . $member['staff_id']);
                    }

                    $i++;
                }

                $data['opportunity_total_days'] = round((human_to_unix($data['opportunity']->deadline . ' 00:00') - human_to_unix($data['opportunity']->start_date . ' 00:00')) / 3600 / 24);
                $data['opportunity_days_left'] = $data['opportunity_total_days'];
                $data['opportunity_time_left_percent'] = 100;
                if ($data['opportunity']->deadline) {
                    if (human_to_unix($data['opportunity']->start_date . ' 00:00') < time() && human_to_unix($data['opportunity']->deadline . ' 00:00') > time()) {
                        $data['opportunity_days_left'] = round((human_to_unix($data['opportunity']->deadline . ' 00:00') - time()) / 3600 / 24);
                        $data['opportunity_time_left_percent'] = $data['opportunity_days_left'] / $data['opportunity_total_days'] * 100;
                        $data['opportunity_time_left_percent'] = round($data['opportunity_time_left_percent'], 2);
                    }
                    if (human_to_unix($data['opportunity']->deadline . ' 00:00') < time()) {
                        $data['opportunity_days_left'] = 0;
                        $data['opportunity_time_left_percent'] = 0;
                    }
                }

                $__total_where_tasks = 'rel_type = "opportunity" AND rel_id=' . $id;
                if (!has_permission('tasks', '', 'view')) {
                    $__total_where_tasks .= ' AND tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid = ' . get_staff_user_id() . ')';

                    if (get_option('show_all_tasks_for_opportunity_member') == 1) {
                        $__total_where_tasks .= ' AND (rel_type="opportunity" AND rel_id IN (SELECT opportunity_id FROM tblopportunitymembers WHERE staff_id=' . get_staff_user_id() . '))';
                    }
                }
                $where = ($__total_where_tasks == '' ? '' : $__total_where_tasks . ' AND ') . 'status != 5';

                $data['tasks_not_completed'] = total_rows('tblstafftasks', $where);
                $total_tasks = total_rows('tblstafftasks', $__total_where_tasks);
                $data['total_tasks'] = $total_tasks;

                $where = ($__total_where_tasks == '' ? '' : $__total_where_tasks . ' AND ') . 'status = 5 AND rel_type="opportunity" AND rel_id="' . $id . '"';

                $data['tasks_completed'] = total_rows('tblstafftasks', $where);

                $data['tasks_not_completed_progress'] = ($total_tasks > 0 ? number_format(($data['tasks_completed'] * 100) / $total_tasks, 2) : 0);
                $data['tasks_not_completed_progress'] = round($data['tasks_not_completed_progress'], 2);

                @$percent_circle = $percent / 100;
                $data['percent_circle'] = $percent_circle;


                $data['opportunity_overview_chart'] = $this->opportunities_model->get_opportunity_overview_weekly_chart_data($id, ($this->input->get('overview_chart') ? $this->input->get('overview_chart') : 'this_week'));
            } elseif ($view == 'opportunity_invoices') {
                $this->load->model('invoices_model');

                $data['invoiceid'] = '';
                $data['status'] = '';
                $data['custom_view'] = '';

                $data['invoices_years'] = $this->invoices_model->get_invoices_years();
                $data['invoices_sale_agents'] = $this->invoices_model->get_sale_agents();
                $data['invoices_statuses'] = $this->invoices_model->get_statuses();
            } elseif ($view == 'opportunity_gantt') {
                $gantt_type = (!$this->input->get('gantt_type') ? 'milestones' : $this->input->get('gantt_type'));
                $taskStatus = (!$this->input->get('gantt_task_status') ? null : $this->input->get('gantt_task_status'));
                $data['gantt_data'] = $this->opportunities_model->get_gantt_data($id, $gantt_type, $taskStatus);
            } elseif ($view == 'opportunity_milestones') {
                $data['bodyclass'] .= 'opportunity-milestones ';
                $data['milestones_exclude_completed_tasks'] = $this->input->get('exclude_completed') && $this->input->get('exclude_completed') == 'yes' || !$this->input->get('exclude_completed');

                $data['total_milestones'] = total_rows('tblmilestones', ['opportunity_id' => $id]);
                $data['milestones_found'] = $data['total_milestones'] > 0 || (!$data['total_milestones'] && total_rows('tblstafftasks', ['rel_id' => $id, 'rel_type' => 'opportunity', 'milestone' => 0]) > 0);
            } elseif ($view == 'opportunity_files') {
                $data['files'] = $this->opportunities_model->get_files($id);
            } elseif ($view == 'opportunity_expenses') {
                $this->load->model('taxes_model');
                $this->load->model('expenses_model');
                $data['taxes'] = $this->taxes_model->get();
                $data['expense_categories'] = $this->expenses_model->get_category();
                $data['currencies'] = $this->currencies_model->get();
            } elseif ($view == 'opportunity_activity') {
                $data['activity'] = $this->opportunities_model->get_activity($id);
            } elseif ($view == 'opportunity_notes') {
                $data['staff_notes'] = $this->opportunities_model->get_staff_notes($id);
            } elseif ($view == 'opportunity_proposals') {

            } elseif ($view == 'opportunity_projects') {

            } elseif ($view == 'opportunity_contacts') {

            } elseif ($view == 'opportunity_estimates') {
                $this->load->model('estimates_model');
                $data['estimates_years'] = $this->estimates_model->get_estimates_years();
                $data['estimates_sale_agents'] = $this->estimates_model->get_sale_agents();
                $data['estimate_statuses'] = $this->estimates_model->get_statuses();
                $data['estimateid'] = '';
                $data['switch_pipeline'] = '';
            } elseif ($view == 'opportunity_tickets') {
                $data['chosen_ticket_status'] = '';
                $this->load->model('tickets_model');
                $data['ticket_assignees'] = $this->tickets_model->get_tickets_assignes_disctinct();

                $this->load->model('departments_model');
                $data['staff_deparments_ids'] = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                $data['default_tickets_list_statuses'] = do_action('default_tickets_list_statuses', [1, 2, 4]);
            } elseif ($view == 'opportunity_timesheets') {
                // Tasks are used in the timesheet dropdown
                // Completed tasks are excluded from this list because you can't add timesheet on completed task.
                $data['tasks'] = $this->opportunities_model->get_tasks($id, 'status != 5 AND billed=0');
                $data['timesheets_staff_ids'] = $this->opportunities_model->get_distinct_tasks_timesheets_staff($id);
            }

            // Discussions
            if ($this->input->get('discussion_id')) {
                $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
                $data['discussion'] = $this->opportunities_model->get_discussion($this->input->get('discussion_id'), $id);
                $data['current_user_is_admin'] = is_admin();
            }

            $data['percent'] = $percent;

            $data['opportunities_assets'] = true;
            $data['circle_progress_asset'] = true;

            $other_opportunities = [];
            $other_opportunities_where = 'id !=' . $id . ' and status = 2';

            if (!has_permission('opportunities', '', 'view')) {
                $other_opportunities_where .= ' AND tblopportunities.id IN (SELECT opportunity_id FROM tblopportunitymembers WHERE staff_id=' . get_staff_user_id() . ')';
            }

            $data['other_opportunities'] = $this->opportunities_model->get('', $other_opportunities_where);
            $data['title'] = $data['opportunity']->name;
            $data['bodyclass'] .= 'opportunity invoices-total-manual estimates-total-manual';
            $data['opportunity_status'] = get_opportunity_status_by_id($opportunity->status);

            $hook_data = do_action('opportunity_group_access_admin', [
                'id' => $opportunity->id,
                'view' => $view,
                'all_data' => $data,
            ]);

            $data = $hook_data['all_data'];
            $view = $hook_data['view'];

            // Unable to load the requested file: admin/opportunities/opportunity_tasks#.php - FIX
            if (strpos($view, '#') !== false) {
                $view = str_replace('#', '', $view);
            }

            $view = trim($view);
            $data['view'] = $view;
            $data['group_view'] = $this->load->view('admin/opportunities/' . $view, $data, true);

            $this->load->view('admin/opportunities/view', $data);
        } else {
            access_denied('opportunity View');
        }
    }

    public function mark_as()
    {
        $success = false;
        $message = '';
        if ($this->input->is_ajax_request()) {
            if (has_permission('opportunities', '', 'create') || has_permission('opportunities', '', 'edit')) {
                $status = get_opportunity_status_by_id($this->input->post('status_id'));

                $message = _l('opportunity_marked_as_failed', $status['name']);
                $success = $this->opportunities_model->mark_as($this->input->post());

                if ($success) {
                    $message = _l('opportunity_marked_as_success', $status['name']);
                }
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function file($id, $opportunity_id)
    {
        $data['discussion_user_profile_image_url'] = staff_profile_image_url(get_staff_user_id());
        $data['current_user_is_admin'] = is_admin();

        $data['file'] = $this->opportunities_model->get_file($id, $opportunity_id);
        if (!$data['file']) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
        $this->load->view('admin/opportunities/_file', $data);
    }

    public function update_file_data()
    {
        if ($this->input->post()) {
            $this->opportunities_model->update_file_data($this->input->post());
        }
    }

    public function add_external_file()
    {
        if ($this->input->post()) {
            $data = [];
            $data['opportunity_id'] = $this->input->post('opportunity_id');
            $data['files'] = $this->input->post('files');
            $data['external'] = $this->input->post('external');
            $data['visible_to_customer'] = ($this->input->post('visible_to_customer') == 'true' ? 1 : 0);
            $data['staffid'] = get_staff_user_id();
            $this->opportunities_model->add_external_file($data);
        }
    }

    public function download_all_files($id)
    {
        if ($this->opportunities_model->is_member($id) || has_permission('opportunities', '', 'view')) {
            $files = $this->opportunities_model->get_files($id);
            if (count($files) == 0) {
                set_alert('warning', _l('no_files_found'));
                redirect(admin_url('opportunities/view/' . $id . '?group=opportunity_files'));
            }
            $path = get_upload_path_by_type('opportunity') . $id;
            $this->load->library('zip');
            foreach ($files as $file) {
                $this->zip->read_file($path . '/' . $file['file_name']);
            }
            $this->zip->download(slug_it(get_opportunity_name_by_id($id)) . '-files.zip');
            $this->zip->clear_data();
        }
    }

    public function export_opportunity_data($id)
    {
        if (has_permission('opportunities', '', 'create')) {
            $opportunity = $this->opportunities_model->get($id);
            $this->load->library('pdf');
            $members = $this->opportunities_model->get_opportunity_members($id);
            $opportunity->currency_data = $this->opportunities_model->get_currency($id);

            // Add <br /> tag and wrap over div element every image to prevent overlaping over text
            $opportunity->description = preg_replace('/(<img[^>]+>(?:<\/img>)?)/i', '<br><br><div>$1</div><br><br>', $opportunity->description);

            $data['opportunity'] = $opportunity;
            $data['milestones'] = $this->opportunities_model->get_milestones($id);
            $data['timesheets'] = $this->opportunities_model->get_timesheets($id);

            $data['tasks'] = $this->opportunities_model->get_tasks($id, [], false);
            $data['total_logged_time'] = seconds_to_time_format($this->opportunities_model->total_logged_time($opportunity->id));
            if ($opportunity->deadline) {
                $data['total_days'] = round((human_to_unix($opportunity->deadline . ' 00:00') - human_to_unix($opportunity->start_date . ' 00:00')) / 3600 / 24);
            } else {
                $data['total_days'] = '/';
            }
            $data['total_members'] = count($members);
            $data['total_tickets'] = total_rows('tbltickets', [
                'opportunity_id' => $id,
            ]);
            $data['total_invoices'] = total_rows('tblinvoices', [
                'opportunity_id' => $id,
            ]);

            $this->load->model('invoices_model');

            $data['invoices_total_data'] = $this->invoices_model->get_invoices_total([
                'currency' => $opportunity->currency_data->id,
                'opportunity_id' => $opportunity->id,
            ]);

            $data['total_milestones'] = count($data['milestones']);
            $data['total_files_attached'] = total_rows('tblopportunityfiles', [
                'opportunity_id' => $opportunity->id,
            ]);
            $data['total_discussion'] = total_rows('tblopportunitydiscussions', [
                'opportunity_id' => $opportunity->id,
            ]);
            $data['members'] = $members;
            $this->load->view('admin/opportunities/export_data_pdf', $data);
        }
    }

    public function update_task_milestone()
    {
        if ($this->input->post()) {
            $this->opportunities_model->update_task_milestone($this->input->post());
        }
    }

    public function update_milestones_order()
    {
        if ($post_data = $this->input->post()) {
            $this->opportunities_model->update_milestones_order($post_data);
        }
    }

    public function pin_action($opportunity_id)
    {
        $this->opportunities_model->pin_action($opportunity_id);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function add_edit_members($opportunity_id)
    {
        if (has_permission('opportunities', '', 'edit') || has_permission('opportunities', '', 'create')) {
            $this->opportunities_model->add_edit_members($this->input->post(), $opportunity_id);
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function discussions($opportunity_id)
    {
        if ($this->opportunities_model->is_member($opportunity_id) || has_permission('opportunities', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('opportunity_discussions', [
                    'opportunity_id' => $opportunity_id,
                ]);
            }
        }
    }

    public function discussion($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            if (!$this->input->post('id')) {
                $id = $this->opportunities_model->add_discussion($this->input->post());
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('opportunity_discussion'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            } else {
                $data = $this->input->post();
                $id = $data['id'];
                unset($data['id']);
                $success = $this->opportunities_model->edit_discussion($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('opportunity_discussion'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
            die;
        }
    }

    public function get_discussion_comments($id, $type)
    {
        echo json_encode($this->opportunities_model->get_discussion_comments($id, $type));
    }

    public function add_discussion_comment($discussion_id, $type)
    {
        echo json_encode($this->opportunities_model->add_discussion_comment($this->input->post(), $discussion_id, $type));
    }

    public function update_discussion_comment()
    {
        echo json_encode($this->opportunities_model->update_discussion_comment($this->input->post()));
    }

    public function delete_discussion_comment($id)
    {
        echo json_encode($this->opportunities_model->delete_discussion_comment($id));
    }

    public function delete_discussion($id)
    {
        $success = false;
        if (has_permission('opportunities', '', 'delete')) {
            $success = $this->opportunities_model->delete_discussion($id);
        }
        $alert_type = 'warning';
        $message = _l('opportunity_discussion_failed_to_delete');
        if ($success) {
            $alert_type = 'success';
            $message = _l('opportunity_discussion_deleted');
        }
        echo json_encode([
            'alert_type' => $alert_type,
            'message' => $message,
        ]);
    }

    public function change_milestone_color()
    {
        if ($this->input->post()) {
            $this->opportunities_model->update_milestone_color($this->input->post());
        }
    }

    public function upload_file($opportunity_id)
    {
        handle_opportunity_file_uploads($opportunity_id);
    }

    public function change_file_visibility($id, $visible)
    {
        if ($this->input->is_ajax_request()) {
            $this->opportunities_model->change_file_visibility($id, $visible);
        }
    }

    public function change_activity_visibility($id, $visible)
    {
        if (has_permission('opportunities', '', 'create')) {
            if ($this->input->is_ajax_request()) {
                $this->opportunities_model->change_activity_visibility($id, $visible);
            }
        }
    }

    public function remove_file($opportunity_id, $id)
    {
        $this->opportunities_model->remove_file($id);
        redirect(admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_files'));
    }

    public function milestones_kanban()
    {
        $data['milestones_exclude_completed_tasks'] = $this->input->get('exclude_completed_tasks') && $this->input->get('exclude_completed_tasks') == 'yes';

        $data['opportunity_id'] = $this->input->get('opportunity_id');
        $data['milestones'] = [];

        $data['milestones'][] = [
            'name' => _l('milestones_uncategorized'),
            'id' => 0,
            'total_logged_time' => $this->opportunities_model->calc_milestone_logged_time($data['opportunity_id'], 0),
            'color' => null,
        ];

        $_milestones = $this->opportunities_model->get_milestones($data['opportunity_id']);

        foreach ($_milestones as $m) {
            $data['milestones'][] = $m;
        }

        echo $this->load->view('admin/opportunities/milestones_kan_ban', $data, true);
    }

    public function milestones_kanban_load_more()
    {
        $milestones_exclude_completed_tasks = $this->input->get('exclude_completed_tasks') && $this->input->get('exclude_completed_tasks') == 'yes';

        $status = $this->input->get('status');
        $page = $this->input->get('page');
        $opportunity_id = $this->input->get('opportunity_id');
        $where = [];
        if ($milestones_exclude_completed_tasks) {
            $where['status !='] = 5;
        }
        $tasks = $this->opportunities_model->do_milestones_kanban_query($status, $opportunity_id, $page, $where);
        foreach ($tasks as $task) {
            $this->load->view('admin/opportunities/_milestone_kanban_card', ['task' => $task, 'milestone' => $status]);
        }
    }

    public function milestones($opportunity_id)
    {
        if ($this->opportunities_model->is_member($opportunity_id) || has_permission('opportunities', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('milestones', [
                    'opportunity_id' => $opportunity_id,
                ]);
            }
        }
    }

    public function milestone($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            if (!$this->input->post('id')) {
                $id = $this->opportunities_model->add_milestone($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('opportunity_milestone')));
                }
            } else {
                $data = $this->input->post();
                $id = $data['id'];
                unset($data['id']);
                $success = $this->opportunities_model->update_milestone($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('opportunity_milestone')));
                }
            }
        }

        redirect(admin_url('opportunities/view/' . $this->input->post('opportunity_id') . '?group=opportunity_milestones'));
    }

    public function delete_milestone($opportunity_id, $id)
    {
        if (has_permission('opportunities', '', 'delete')) {
            if ($this->opportunities_model->delete_milestone($id)) {
                set_alert('deleted', 'opportunity_milestone');
            }
        }
        redirect(admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_milestones'));
    }

    public function bulk_action_files()
    {
        do_action('before_do_bulk_action_for_opportunity_files');
        $total_deleted = 0;
        $hasPermissionDelete = has_permission('opportunities', '', 'delete');
        // bulk action for opportunities currently only have delete button
        if ($this->input->post()) {
            $fVisibility = $this->input->post('visible_to_customer') == 'true' ? 1 : 0;
            $ids = $this->input->post('ids');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($hasPermissionDelete && $this->input->post('mass_delete') && $this->opportunities_model->remove_file($id)) {
                        $total_deleted++;
                    } else {
                        $this->opportunities_model->change_file_visibility($id, $fVisibility);
                    }
                }
            }
        }
        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_files_deleted', $total_deleted));
        }
    }

    public function timesheets($opportunity_id)
    {
        if ($this->opportunities_model->is_member($opportunity_id) || has_permission('opportunities', '', 'view')) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data('timesheets', [
                    'opportunity_id' => $opportunity_id,
                    'timesheets_type' => 'opportunity'
                ]);
            }
        }
    }

    public function timesheet()
    {
        if ($this->input->post()) {
            $message = '';
            $success = false;
            $success = $this->tasks_model->timesheet($this->input->post());
            if ($success === true) {
                $message = _l('added_successfully', _l('opportunity_timesheet'));
            } elseif (is_array($success) && isset($success['end_time_smaller'])) {
                $message = _l('failed_to_add_opportunity_timesheet_end_time_smaller');
            } else {
                $message = _l('opportunity_timesheet_not_updated');
            }
            echo json_encode([
                'success' => $success,
                'message' => $message,
            ]);
            die;
        }
    }

    public function timesheet_task_assignees($task_id, $opportunity_id, $staff_id = 'undefined')
    {
        $assignees = $this->tasks_model->get_task_assignees($task_id);
        $data = '';
        $has_permission_edit = has_permission('opportunities', '', 'edit');
        $has_permission_create = has_permission('opportunities', '', 'edit');
        // The second condition if staff member edit their own timesheet
        if ($staff_id == 'undefined' || $staff_id != 'undefined' && (!$has_permission_edit || !$has_permission_create)) {
            $staff_id = get_staff_user_id();
            $current_user = true;
        }
        foreach ($assignees as $staff) {
            $selected = '';
            // maybe is admin and not opportunity member
            if ($staff['assigneeid'] == $staff_id && $this->opportunities_model->is_member($opportunity_id, $staff_id)) {
                $selected = ' selected';
            }
            if ((!$has_permission_edit || !$has_permission_create) && isset($current_user)) {
                if ($staff['assigneeid'] != $staff_id) {
                    continue;
                }
            }
            $data .= '<option value="' . $staff['assigneeid'] . '"' . $selected . '>' . get_staff_full_name($staff['assigneeid']) . '</option>';
        }
        echo $data;
    }

    public function remove_team_member($opportunity_id, $staff_id)
    {
        if (has_permission('opportunities', '', 'edit') || has_permission('opportunities', '', 'create')) {
            if ($this->opportunities_model->remove_team_member($opportunity_id, $staff_id)) {
                set_alert('success', _l('opportunity_member_removed'));
            }
        }
        redirect(admin_url('opportunities/view/' . $opportunity_id));
    }

    public function save_note($opportunity_id)
    {
        if ($this->input->post()) {
            $success = $this->opportunities_model->save_note($this->input->post(null, false), $opportunity_id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('opportunity_note')));
            }
            redirect(admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_notes'));
        }
    }

    public function delete($opportunity_id)
    {
        if (has_permission('opportunities', '', 'delete')) {
            $opportunity = $this->opportunities_model->get($opportunity_id);
            $success = $this->opportunities_model->delete($opportunity_id);
            if ($success) {
                set_alert('success', _l('deleted', _l('opportunity')));
                if (strpos($_SERVER['HTTP_REFERER'], 'clients/') !== false) {
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    redirect(admin_url('opportunities'));
                }
            } else {
                set_alert('warning', _l('problem_deleting', _l('opportunity_lowercase')));
                redirect(admin_url('opportunities/view/' . $opportunity_id));
            }
        }
    }

    public function copy($opportunity_id)
    {
        if (has_permission('opportunities', '', 'create')) {
            $id = $this->opportunities_model->copy($opportunity_id, $this->input->post());
            if ($id) {
                set_alert('success', _l('opportunity_copied_successfully'));
                redirect(admin_url('opportunities/view/' . $id));
            } else {
                set_alert('danger', _l('failed_to_copy_opportunity'));
                redirect(admin_url('opportunities/view/' . $opportunity_id));
            }
        }
    }

    public function mass_stop_timers($opportunity_id, $billable = 'false')
    {
        if (has_permission('invoices', '', 'create')) {
            $where = [
                'billed' => 0,
                'startdate <=' => date('Y-m-d'),
            ];
            if ($billable == 'true') {
                $where['billable'] = true;
            }
            $tasks = $this->opportunities_model->get_tasks($opportunity_id, $where);
            $total_timers_stopped = 0;
            foreach ($tasks as $task) {
                $this->db->where('task_id', $task['id']);
                $this->db->where('end_time IS NULL');
                $this->db->update('tbltaskstimers', [
                    'end_time' => time(),
                ]);
                $total_timers_stopped += $this->db->affected_rows();
            }
            $message = _l('opportunity_tasks_total_timers_stopped', $total_timers_stopped);
            $type = 'success';
            if ($total_timers_stopped == 0) {
                $type = 'warning';
            }
            echo json_encode([
                'type' => $type,
                'message' => $message,
            ]);
        }
    }

    public function get_pre_invoice_opportunity_info($opportunity_id)
    {
        if (has_permission('invoices', '', 'create')) {
            $data['billable_tasks'] = $this->opportunities_model->get_tasks($opportunity_id, [
                'billable' => 1,
                'billed' => 0,
                'startdate <=' => date('Y-m-d'),
            ]);

            $data['not_billable_tasks'] = $this->opportunities_model->get_tasks($opportunity_id, [
                'billable' => 1,
                'billed' => 0,
                'startdate >' => date('Y-m-d'),
            ]);

            $data['opportunity_id'] = $opportunity_id;
            $data['billing_type'] = get_opportunity_billing_type($opportunity_id);

            $this->load->model('expenses_model');
            $this->db->where('invoiceid IS NULL');
            $data['expenses'] = $this->expenses_model->get('', [
                'opportunity_id' => $opportunity_id,
                'billable' => 1,
            ]);

            $this->load->view('admin/opportunities/opportunity_pre_invoice_settings', $data);
        }
    }

    public function get_invoice_opportunity_data()
    {
        if (has_permission('invoices', '', 'create')) {
            $type = $this->input->post('type');
            $opportunity_id = $this->input->post('opportunity_id');
            // Check for all cases
            if ($type == '') {
                $type == 'single_line';
            }
            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', [
                'expenses_only !=' => 1,
            ]);
            $this->load->model('taxes_model');
            $data['taxes'] = $this->taxes_model->get();
            $data['currencies'] = $this->currencies_model->get();
            $data['base_currency'] = $this->currencies_model->get_base_currency();
            $this->load->model('invoice_items_model');

            $data['ajaxItems'] = false;
            if (total_rows('tblitems') <= ajax_on_total_items()) {
                $data['items'] = $this->invoice_items_model->get_grouped();
            } else {
                $data['items'] = [];
                $data['ajaxItems'] = true;
            }

            $data['items_groups'] = $this->invoice_items_model->get_groups();
            $data['staff'] = $this->staff_model->get('', ['active' => 1]);
            $opportunity = $this->opportunities_model->get($opportunity_id);
            $data['opportunity'] = $opportunity;
            $items = [];

            $opportunity = $this->opportunities_model->get($opportunity_id);
            $item['id'] = 0;

            $default_tax = unserialize(get_option('default_tax'));
            $item['taxname'] = $default_tax;

            $tasks = $this->input->post('tasks');
            if ($tasks) {
                $item['long_description'] = '';
                $item['qty'] = 0;
                $item['task_id'] = [];
                if ($type == 'single_line') {
                    $item['description'] = $opportunity->name;
                    foreach ($tasks as $task_id) {
                        $task = $this->tasks_model->get($task_id);
                        $sec = $this->tasks_model->calc_task_total_time($task_id);
                        $item['long_description'] .= $task->name . ' - ' . seconds_to_time_format($sec) . ' ' . _l('hours') . "\r\n";
                        $item['task_id'][] = $task_id;
                        if ($opportunity->billing_type == 2) {
                            if ($sec < 60) {
                                $sec = 0;
                            }
                            $item['qty'] += sec2qty($sec);
                        }
                    }
                    if ($opportunity->billing_type == 1) {
                        $item['qty'] = 1;
                        $item['rate'] = $opportunity->opportunity_cost;
                    } elseif ($opportunity->billing_type == 2) {
                        $item['rate'] = $opportunity->opportunity_rate_per_hour;
                    }
                    $item['unit'] = '';
                    $items[] = $item;
                } elseif ($type == 'task_per_item') {
                    foreach ($tasks as $task_id) {
                        $task = $this->tasks_model->get($task_id);
                        $sec = $this->tasks_model->calc_task_total_time($task_id);
                        $item['description'] = $opportunity->name . ' - ' . $task->name;
                        $item['qty'] = floatVal(sec2qty($sec));
                        $item['long_description'] = seconds_to_time_format($sec) . ' ' . _l('hours');
                        if ($opportunity->billing_type == 2) {
                            $item['rate'] = $opportunity->opportunity_rate_per_hour;
                        } elseif ($opportunity->billing_type == 3) {
                            $item['rate'] = $task->hourly_rate;
                        }
                        $item['task_id'] = $task_id;
                        $item['unit'] = '';
                        $items[] = $item;
                    }
                } elseif ($type == 'timesheets_individualy') {
                    $timesheets = $this->opportunities_model->get_timesheets($opportunity_id, $tasks);
                    $added_task_ids = [];
                    foreach ($timesheets as $timesheet) {
                        if ($timesheet['task_data']->billed == 0 && $timesheet['task_data']->billable == 1) {
                            $item['description'] = $opportunity->name . ' - ' . $timesheet['task_data']->name;
                            if (!in_array($timesheet['task_id'], $added_task_ids)) {
                                $item['task_id'] = $timesheet['task_id'];
                            }

                            array_push($added_task_ids, $timesheet['task_id']);

                            $item['qty'] = floatVal(sec2qty($timesheet['total_spent']));
                            $item['long_description'] = _l('opportunity_invoice_timesheet_start_time', _dt($timesheet['start_time'], true)) . "\r\n" . _l('opportunity_invoice_timesheet_end_time', _dt($timesheet['end_time'], true)) . "\r\n" . _l('opportunity_invoice_timesheet_total_logged_time', seconds_to_time_format($timesheet['total_spent'])) . ' ' . _l('hours');

                            if ($this->input->post('timesheets_include_notes') && $timesheet['note']) {
                                $item['long_description'] .= "\r\n\r\n" . _l('note') . ': ' . $timesheet['note'];
                            }

                            if ($opportunity->billing_type == 2) {
                                $item['rate'] = $opportunity->opportunity_rate_per_hour;
                            } elseif ($opportunity->billing_type == 3) {
                                $item['rate'] = $timesheet['task_data']->hourly_rate;
                            }
                            $item['unit'] = '';
                            $items[] = $item;
                        }
                    }
                }
            }
            if ($opportunity->billing_type != 1) {
                $data['hours_quantity'] = true;
            }
            if ($this->input->post('expenses')) {
                if (isset($data['hours_quantity'])) {
                    unset($data['hours_quantity']);
                }
                if (count($tasks) > 0) {
                    $data['qty_hrs_quantity'] = true;
                }
                $expenses = $this->input->post('expenses');
                $addExpenseNote = $this->input->post('expenses_add_note');
                $addExpenseName = $this->input->post('expenses_add_name');

                if (!$addExpenseNote) {
                    $addExpenseNote = [];
                }

                if (!$addExpenseName) {
                    $addExpenseName = [];
                }

                $this->load->model('expenses_model');
                foreach ($expenses as $expense_id) {
                    // reset item array
                    $item = [];
                    $item['id'] = 0;
                    $expense = $this->expenses_model->get($expense_id);
                    $item['expense_id'] = $expense->expenseid;
                    $item['description'] = _l('item_as_expense') . ' ' . $expense->name;
                    $item['long_description'] = $expense->description;

                    if (in_array($expense_id, $addExpenseNote) && !empty($expense->note)) {
                        $item['long_description'] .= PHP_EOL . $expense->note;
                    }

                    if (in_array($expense_id, $addExpenseName) && !empty($expense->expense_name)) {
                        $item['long_description'] .= PHP_EOL . $expense->expense_name;
                    }

                    $item['qty'] = 1;

                    $item['taxname'] = [];
                    if ($expense->tax != 0) {
                        array_push($item['taxname'], $expense->tax_name . '|' . $expense->taxrate);
                    }
                    if ($expense->tax2 != 0) {
                        array_push($item['taxname'], $expense->tax_name2 . '|' . $expense->taxrate2);
                    }
                    $item['rate'] = $expense->amount;
                    $item['order'] = 1;
                    $item['unit'] = '';
                    $items[] = $item;
                }
            }
            $data['customer_id'] = $opportunity->clientid;
            $data['invoice_from_opportunity'] = true;
            $data['add_items'] = $items;
            $this->load->view('admin/opportunities/invoice_opportunity', $data);
        }
    }

    public function get_rel_opportunity_data($id, $task_id = '')
    {
        if ($this->input->is_ajax_request()) {
            $selected_milestone = '';
            if ($task_id != '' && $task_id != 'undefined') {
                $task = $this->tasks_model->get($task_id);
                $selected_milestone = $task->milestone;
            }

            $allow_to_view_tasks = 0;
            $this->db->where('opportunity_id', $id);
            $this->db->where('name', 'view_tasks');
            $opportunity_settings = $this->db->get('tblopportunitysettings')->row();
            if ($opportunity_settings) {
                $allow_to_view_tasks = $opportunity_settings->value;
            }

            echo json_encode([
                'allow_to_view_tasks' => $allow_to_view_tasks,
                'billing_type' => get_opportunity_billing_type($id),
                'milestones' => render_select('milestone', $this->opportunities_model->get_milestones($id), [
                    'id',
                    'name',
                ], 'task_milestone', $selected_milestone),
            ]);
        }
    }

    public function invoice_opportunity($opportunity_id)
    {
        if (has_permission('invoices', '', 'create')) {
            $this->load->model('invoices_model');
            $data = $this->input->post();
            $data['opportunity_id'] = $opportunity_id;
            $invoice_id = $this->invoices_model->add($data);
            if ($invoice_id) {
                $this->opportunities_model->log_activity($opportunity_id, 'opportunity_activity_invoiced_opportunity', format_invoice_number($invoice_id));
                set_alert('success', _l('opportunity_invoiced_successfully'));
            }
            redirect(admin_url('opportunities/view/' . $opportunity_id . '?group=opportunity_invoices'));
        }
    }

    public function view_opportunity_as_client($id, $clientid)
    {
        if (is_admin()) {
            login_as_client($clientid);
            redirect(site_url('clients/opportunity/' . $id));
        }
    }
}
