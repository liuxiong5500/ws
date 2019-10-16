<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Opportunities_model extends CRM_Model
{
    private $opportunity_settings;

    public function __construct()
    {
        parent::__construct();

        $opportunity_settings = [
            'available_features',
            'view_tasks',
            'create_tasks',
            'edit_tasks',
            'comment_on_tasks',
            'view_task_comments',
            'view_task_attachments',
            'view_task_checklist_items',
            'upload_on_tasks',
            'view_task_total_logged_time',
            'view_finance_overview',
            'upload_files',
            'open_discussions',
            'view_milestones',
            'view_gantt',
            'view_timesheets',
            'view_activity_log',
            'view_team_members',
            'hide_opportunity_tasks_on_main_tasks_table',
        ];

        $this->opportunity_settings = do_action('opportunity_settings', $opportunity_settings);
    }

    public function get_contact($id)
    {
        $this->db->select('tblopportunitycontacts.*,firstname,lastname');
        $this->db->join('tblcontacts', 'tblcontacts.id = tblopportunitycontacts.contact_id', 'left');
        $this->db->where('tblopportunitycontacts.id', $id);
        $contact = $this->db->get('tblopportunitycontacts')->row();
        return $contact;
    }

    public function add_contact($data)
    {
        $this->db->insert('tblopportunitycontacts', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    public function update_contact($data, $id)
    {
        $affectedRows = 0;
        unset($data['id']);
        $this->db->where('id', $id);
        $this->db->update('tblopportunitycontacts', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    public function delete_contact($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblopportunitycontacts');

        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_opportunity_statuses()
    {
        $statuses = do_action('before_get_opportunity_statuses', [
            [
                'id' => 1,
                'color' => '#989898',
                'name' => _l('opportunity_status_1'),
                'order' => 1,
                'filter_default' => true,
            ],
            [
                'id' => 2,
                'color' => '#03a9f4',
                'name' => _l('opportunity_status_2'),
                'order' => 2,
                'filter_default' => true,
            ],
            [
                'id' => 3,
                'color' => '#ff6f00',
                'name' => _l('opportunity_status_3'),
                'order' => 3,
                'filter_default' => true,
            ],
            [
                'id' => 4,
                'color' => '#84c529',
                'name' => _l('opportunity_status_4'),
                'order' => 100,
                'filter_default' => false,
            ],
            [
                'id' => 5,
                'color' => '#989898',
                'name' => _l('opportunity_status_5'),
                'order' => 4,
                'filter_default' => false,
            ],
        ]);

        usort($statuses, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $statuses;
    }

    public function get_distinct_tasks_timesheets_staff($opportunity_id)
    {
        return $this->db->query('SELECT DISTINCT staff_id FROM tbltaskstimers LEFT JOIN tblstafftasks ON tblstafftasks.id = tbltaskstimers.task_id WHERE rel_type="opportunity" AND rel_id=' . $opportunity_id)->result_array();
    }

    public function get_most_used_billing_type()
    {
        return $this->db->query('SELECT billing_type, COUNT(*) AS total_usage
                FROM tblopportunities
                GROUP BY billing_type
                ORDER BY total_usage DESC
                LIMIT 1')->row();
    }

    public function timers_started_for_opportunity($opportunity_id, $where = [], $task_timers_where = [])
    {
        $this->db->where($where);
        $this->db->where('end_time IS NULL');
        $this->db->where('tblstafftasks.rel_id', $opportunity_id);
        $this->db->where('tblstafftasks.rel_type', 'opportunity');
        $this->db->join('tblstafftasks', 'tblstafftasks.id=tbltaskstimers.task_id');
        $total = $this->db->count_all_results('tbltaskstimers');

        return $total > 0 ? true : false;
    }

    public function pin_action($id)
    {
        if (total_rows('tblpinnedopportunities', [
                'staff_id' => get_staff_user_id(),
                'opportunity_id' => $id,
            ]) == 0) {
            $this->db->insert('tblpinnedopportunities', [
                'staff_id' => get_staff_user_id(),
                'opportunity_id' => $id,
            ]);

            return true;
        }
        $this->db->where('opportunity_id', $id);
        $this->db->where('staff_id', get_staff_user_id());
        $this->db->delete('tblpinnedopportunities');

        return true;
    }

    public function get_currency($id)
    {
        $this->load->model('currencies_model');
        $customer_currency = $this->clients_model->get_customer_default_currency(get_client_id_by_opportunity_id($id));
        if ($customer_currency != 0) {
            $currency = $this->currencies_model->get($customer_currency);
        } else {
            $currency = $this->currencies_model->get_base_currency();
        }

        return $currency;
    }

    public function calc_progress($id)
    {
        $this->db->select('progress_from_tasks,progress,status');
        $this->db->where('id', $id);
        $opportunity = $this->db->get('tblopportunities')->row();

        if ($opportunity->status == 4) {
            return 100;
        }

        if ($opportunity->progress_from_tasks == 1) {
            return $this->calc_progress_by_tasks($id);
        }

        return $opportunity->progress;
    }

    public function calc_progress_by_tasks($id)
    {
        $total_opportunity_tasks = total_rows('tblstafftasks', [
            'rel_type' => 'opportunity',
            'rel_id' => $id,
        ]);
        $total_finished_tasks = total_rows('tblstafftasks', [
            'rel_type' => 'opportunity',
            'rel_id' => $id,
            'status' => 5,
        ]);
        $percent = 0;
        if ($total_finished_tasks >= floatval($total_opportunity_tasks)) {
            $percent = 100;
        } else {
            if ($total_opportunity_tasks !== 0) {
                $percent = number_format(($total_finished_tasks * 100) / $total_opportunity_tasks, 2);
            }
        }

        return $percent;
    }

    public function get_last_opportunity_settings()
    {
        $this->db->select('id');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last_opportunity = $this->db->get('tblopportunities')->row();
        if ($last_opportunity) {
            return $this->get_opportunity_settings($last_opportunity->id);
        }

        return [];
    }

    public function get_settings()
    {
        return $this->opportunity_settings;
    }

    public function get($id = '', $where = [])
    {
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $opportunity = $this->db->get('tblopportunities')->row();
            if ($opportunity) {
                $settings = $this->get_opportunity_settings($id);

                // SYNC NEW TABS
                $tabs = get_opportunity_tabs_admin(null, $opportunity->rel_type);
                $tabs_flatten = [];
                $settings_available_features = [];

                $available_features_index = false;
                foreach ($settings as $key => $setting) {
                    if ($setting['name'] == 'available_features') {
                        $available_features_index = $key;
                        $available_features = unserialize($setting['value']);
                        if (is_array($available_features)) {
                            foreach ($available_features as $name => $avf) {
                                $settings_available_features[] = $name;
                            }
                        }
                    }
                }
                foreach ($tabs as $tab) {
                    if (isset($tab['dropdown'])) {
                        foreach ($tab['dropdown'] as $d) {
                            $tabs_flatten[] = $d['name'];
                        }
                    } else {
                        $tabs_flatten[] = $tab['name'];
                    }
                }
                if (count($settings_available_features) != $tabs_flatten) {
                    foreach ($tabs_flatten as $tab) {
                        if (!in_array($tab, $settings_available_features)) {
                            if ($available_features_index) {
                                $current_available_features_settings = $settings[$available_features_index];
                                $tmp = unserialize($current_available_features_settings['value']);
                                $tmp[$tab] = 1;
                                $this->db->where('id', $current_available_features_settings['id']);
                                $this->db->update('tblopportunitysettings', ['value' => serialize($tmp)]);
                            }
                        }
                    }
                }
                $opportunity->settings = new StdClass();
                foreach ($settings as $setting) {
                    $opportunity->settings->{$setting['name']} = $setting['value'];
                }

                // In case any settings missing add them and set default 0 to prevent errors
                foreach ($this->opportunity_settings as $setting) {
                    if (!isset($opportunity->settings->{$setting})) {
                        $this->db->insert('tblopportunitysettings', [
                            'opportunity_id' => $id,
                            'name' => $setting,
                            'value' => 0,
                        ]);
                        $opportunity->settings->{$setting} = 0;
                    }
                }
                $opportunity->client_data = new StdClass();
                if ($opportunity->rel_type == 'customer') {
                    $opportunity->client_data = $this->clients_model->get($opportunity->rel_id);
                } else {
                    $opportunity->client_data->active = 0;
                }

                return do_action('opportunity_get', $opportunity);
            }

            return null;
        }

        $this->db->order_by('id', 'desc');

        return $this->db->get('tblopportunities')->result_array();
    }

    public function calculate_total_by_opportunity_hourly_rate($seconds, $hourly_rate)
    {
        $hours = seconds_to_time_format($seconds);
        $decimal = sec2qty($seconds);
        $total_money = 0;
        $total_money += ($decimal * $hourly_rate);

        return [
            'hours' => $hours,
            'total_money' => $total_money,
        ];
    }

    public function calculate_total_by_task_hourly_rate($tasks)
    {
        $total_money = 0;
        $_total_seconds = 0;

        foreach ($tasks as $task) {
            $seconds = $task['total_logged_time'];
            $_total_seconds += $seconds;
            $total_money += sec2qty($seconds) * $task['hourly_rate'];
        }

        return [
            'total_money' => $total_money,
            'total_seconds' => $_total_seconds,
        ];
    }

    public function get_tasks($id, $where = [], $apply_restrictions = false, $count = false)
    {
        $has_permission = has_permission('tasks', '', 'view');
        $show_all_tasks_for_opportunity_member = get_option('show_all_tasks_for_opportunity_member');

        if (is_client_logged_in()) {
            $this->db->where('visible_to_client', 1);
        }

        $select = implode(', ', prefixed_table_fields_array('tblstafftasks')) . ',tblmilestones.name as milestone_name,
        (SELECT SUM(CASE
            WHEN end_time is NULL THEN ' . time() . '-start_time
            ELSE end_time-start_time
            END) FROM tbltaskstimers WHERE task_id=tblstafftasks.id) as total_logged_time,
           ' . get_sql_select_task_assignees_ids() . ' as assignees_ids
        ';

        if (!is_client_logged_in() && is_staff_logged_in()) {
            $select .= ',(SELECT staffid FROM tblstafftaskassignees WHERE taskid=tblstafftasks.id AND staffid=' . get_staff_user_id() . ') as current_user_is_assigned';
        }
        $this->db->select($select);

        $this->db->join('tblmilestones', 'tblmilestones.id = tblstafftasks.milestone', 'left');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'opportunity');
        if ($apply_restrictions == true) {
            if (!is_client_logged_in() && !$has_permission && $show_all_tasks_for_opportunity_member == 0) {
                $this->db->where('(
                    tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid=' . get_staff_user_id() . ')
                    OR tblstafftasks.id IN(SELECT taskid FROM tblstafftasksfollowers WHERE staffid=' . get_staff_user_id() . ')
                    OR is_public = 1
                    OR (addedfrom =' . get_staff_user_id() . ' AND is_added_from_contact = 0)
                    )');
            }
        }
        $this->db->order_by('milestone_order', 'asc');
        $this->db->where($where);

        if ($count == false) {
            $tasks = $this->db->get('tblstafftasks')->result_array();
        } else {
            $tasks = $this->db->count_all_results('tblstafftasks');
        }

        return $tasks;
    }

    public function do_milestones_kanban_query($milestone_id, $opportunity_id, $page = 1, $where = [], $count = false)
    {
        $where['milestone'] = $milestone_id;

        if ($count == false) {
            if ($page > 1) {
                $page--;
                $position = ($page * get_option('tasks_kanban_limit'));
                $this->db->limit(get_option('tasks_kanban_limit'), $position);
            } else {
                $this->db->limit(get_option('tasks_kanban_limit'));
            }
        }

        return $this->get_tasks($opportunity_id, $where, true, $count);
    }

    public function get_files($opportunity_id)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('opportunity_id', $opportunity_id);

        return $this->db->get('tblopportunityfiles')->result_array();
    }

    public function get_file($id, $opportunity_id = false)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('id', $id);
        $file = $this->db->get('tblopportunityfiles')->row();

        if ($file && $opportunity_id) {
            if ($file->opportunity_id != $opportunity_id) {
                return false;
            }
        }

        return $file;
    }

    public function update_file_data($data)
    {
        $this->db->where('id', $data['id']);
        unset($data['id']);
        $this->db->update('tblopportunityfiles', $data);
    }

    public function change_file_visibility($id, $visible)
    {
        $this->db->where('id', $id);
        $this->db->update('tblopportunityfiles', [
            'visible_to_customer' => $visible,
        ]);
    }

    public function change_activity_visibility($id, $visible)
    {
        $this->db->where('id', $id);
        $this->db->update('tblopportunityactivity', [
            'visible_to_customer' => $visible,
        ]);
    }

    public function remove_file($id, $logActivity = true)
    {
        $id = do_action('before_remove_opportunity_file', $id);

        $this->db->where('id', $id);
        $file = $this->db->get('tblopportunityfiles')->row();
        if ($file) {
            if (empty($file->external)) {
                $path = get_upload_path_by_type('opportunity') . $file->opportunity_id . '/';
                $fullPath = $path . $file->file_name;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    $fname = pathinfo($fullPath, PATHINFO_FILENAME);
                    $fext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $thumbPath = $path . $fname . '_thumb.' . $fext;

                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                    }
                }
            }

            $this->db->where('id', $id);
            $this->db->delete('tblopportunityfiles');
            if ($logActivity) {
                $this->log_activity($file->opportunity_id, 'opportunity_activity_opportunity_file_removed', $file->file_name, $file->visible_to_customer);
            }

            // Delete discussion comments
            $this->_delete_discussion_comments($id, 'file');

            if (is_dir(get_upload_path_by_type('opportunity') . $file->opportunity_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('opportunity') . $file->opportunity_id);
                if (count($other_attachments) == 0) {
                    delete_dir(get_upload_path_by_type('opportunity') . $file->opportunity_id);
                }
            }

            return true;
        }

        return false;
    }

    public function get_opportunity_overview_weekly_chart_data($id, $type = 'this_week')
    {
        $billing_type = get_opportunity_billing_type($id);
        $chart = [];

        $has_permission_create = has_permission('opportunities', '', 'create');
        // If don't have permission for opportunities create show only bileld time
        if (!$has_permission_create) {
            $timesheets_type = 'total_logged_time_only';
        } else {
            if ($billing_type == 2 || $billing_type == 3) {
                $timesheets_type = 'billable_unbilled';
            } else {
                $timesheets_type = 'total_logged_time_only';
            }
        }

        $chart['data'] = [];
        $chart['data']['labels'] = [];
        $chart['data']['datasets'] = [];

        $chart['data']['datasets'][] = [
            'label' => ($timesheets_type == 'billable_unbilled' ? str_replace(':', '', _l('opportunity_overview_billable_hours')) : str_replace(':', '', _l('opportunity_overview_logged_hours'))),
            'data' => [],
            'backgroundColor' => [],
            'borderColor' => [],
            'borderWidth' => 1,
        ];

        if ($timesheets_type == 'billable_unbilled') {
            $chart['data']['datasets'][] = [
                'label' => str_replace(':', '', _l('opportunity_overview_unbilled_hours')),
                'data' => [],
                'backgroundColor' => [],
                'borderColor' => [],
                'borderWidth' => 1,
            ];
        }

        $temp_weekdays_data = [];
        $weeks = [];
        $where_time = '';

        if ($type == 'this_month') {
            $beginThisMonth = date('Y-m-01');
            $endThisMonth = date('Y-m-t 23:59:59');

            $weeks_split_start = date('Y-m-d', strtotime($beginThisMonth));
            $weeks_split_end = date('Y-m-d', strtotime($endThisMonth));

            $where_time = 'start_time BETWEEN ' . strtotime($beginThisMonth) . ' AND ' . strtotime($endThisMonth);
        } elseif ($type == 'last_month') {
            $beginLastMonth = date('Y-m-01', strtotime('-1 MONTH'));
            $endLastMonth = date('Y-m-t 23:59:59', strtotime('-1 MONTH'));

            $weeks_split_start = date('Y-m-d', strtotime($beginLastMonth));
            $weeks_split_end = date('Y-m-d', strtotime($endLastMonth));

            $where_time = 'start_time BETWEEN ' . strtotime($beginLastMonth) . ' AND ' . strtotime($endLastMonth);
        } elseif ($type == 'last_week') {
            $beginLastWeek = date('Y-m-d', strtotime('monday last week'));
            $endLastWeek = date('Y-m-d 23:59:59', strtotime('sunday last week'));
            $where_time = 'start_time BETWEEN ' . strtotime($beginLastWeek) . ' AND ' . strtotime($endLastWeek);
        } else {
            $beginThisWeek = date('Y-m-d', strtotime('monday this week'));
            $endThisWeek = date('Y-m-d 23:59:59', strtotime('sunday this week'));
            $where_time = 'start_time BETWEEN ' . strtotime($beginThisWeek) . ' AND ' . strtotime($endThisWeek);
        }

        if ($type == 'this_week' || $type == 'last_week') {
            foreach (get_weekdays() as $day) {
                array_push($chart['data']['labels'], $day);
            }
            $weekDay = date('w', strtotime(date('Y-m-d H:i:s')));
            $i = 0;
            foreach (get_weekdays_original() as $day) {
                if ($weekDay != '0') {
                    $chart['data']['labels'][$i] = date('d', strtotime($day . ' ' . str_replace('_', ' ', $type))) . ' - ' . $chart['data']['labels'][$i];
                } else {
                    if ($type == 'this_week') {
                        $strtotime = 'last ' . $day;
                        if ($day == 'Sunday') {
                            $strtotime = 'sunday this week';
                        }
                        $chart['data']['labels'][$i] = date('d', strtotime($strtotime)) . ' - ' . $chart['data']['labels'][$i];
                    } else {
                        $strtotime = $day . ' last week';
                        $chart['data']['labels'][$i] = date('d', strtotime($strtotime)) . ' - ' . $chart['data']['labels'][$i];
                    }
                }
                $i++;
            }
        } elseif ($type == 'this_month' || $type == 'last_month') {
            $weeks_split_start = new DateTime($weeks_split_start);
            $weeks_split_end = new DateTime($weeks_split_end);
            $weeks = get_weekdays_between_dates($weeks_split_start, $weeks_split_end);
            $total_weeks = count($weeks);
            for ($i = 1; $i <= $total_weeks; $i++) {
                array_push($chart['data']['labels'], split_weeks_chart_label($weeks, $i));
            }
        }

        $loop_break = ($timesheets_type == 'billable_unbilled') ? 2 : 1;

        for ($i = 0; $i < $loop_break; $i++) {
            $temp_weekdays_data = [];
            // Store the weeks in new variable for each loop to prevent duplicating
            $tmp_weeks = $weeks;


            $color = '3, 169, 244';

            $where = 'task_id IN (SELECT id FROM tblstafftasks WHERE rel_type = "opportunity" AND rel_id = "' . $id . '"';

            if ($timesheets_type != 'total_logged_time_only') {
                $where .= ' AND billable=1';
                if ($i == 1) {
                    $color = '252, 45, 66';
                    $where .= ' AND billed = 0';
                }
            }

            $where .= ')';
            $this->db->where($where_time);
            $this->db->where($where);
            if (!$has_permission_create) {
                $this->db->where('staff_id', get_staff_user_id());
            }
            $timesheets = $this->db->get('tbltaskstimers')->result_array();

            foreach ($timesheets as $t) {
                $total_logged_time = 0;
                if ($t['end_time'] == null) {
                    $total_logged_time = time() - $t['start_time'];
                } else {
                    $total_logged_time = $t['end_time'] - $t['start_time'];
                }

                if ($type == 'this_week' || $type == 'last_week') {
                    $weekday = date('N', $t['start_time']);
                    if (!isset($temp_weekdays_data[$weekday])) {
                        $temp_weekdays_data[$weekday] = 0;
                    }
                    $temp_weekdays_data[$weekday] += $total_logged_time;
                } else {
                    // months - this and last
                    $w = 1;
                    foreach ($tmp_weeks as $week) {
                        $start_time_date = strftime('%Y-%m-%d', $t['start_time']);
                        if (!isset($tmp_weeks[$w]['total'])) {
                            $tmp_weeks[$w]['total'] = 0;
                        }
                        if (in_array($start_time_date, $week)) {
                            $tmp_weeks[$w]['total'] += $total_logged_time;
                        }
                        $w++;
                    }
                }
            }

            if ($type == 'this_week' || $type == 'last_week') {
                ksort($temp_weekdays_data);
                for ($w = 1; $w <= 7; $w++) {
                    $total_logged_time = 0;
                    if (isset($temp_weekdays_data[$w])) {
                        $total_logged_time = $temp_weekdays_data[$w];
                    }
                    array_push($chart['data']['datasets'][$i]['data'], sec2qty($total_logged_time));
                    array_push($chart['data']['datasets'][$i]['backgroundColor'], 'rgba(' . $color . ',0.8)');
                    array_push($chart['data']['datasets'][$i]['borderColor'], 'rgba(' . $color . ',1)');
                }
            } else {
                // loop over $tmp_weeks because the unbilled is shown twice because we auto increment twice
                // months - this and last
                foreach ($tmp_weeks as $week) {
                    $total = 0;
                    if (isset($week['total'])) {
                        $total = $week['total'];
                    }
                    $total_logged_time = $total;
                    array_push($chart['data']['datasets'][$i]['data'], sec2qty($total_logged_time));
                    array_push($chart['data']['datasets'][$i]['backgroundColor'], 'rgba(' . $color . ',0.8)');
                    array_push($chart['data']['datasets'][$i]['borderColor'], 'rgba(' . $color . ',1)');
                }
            }
        }

        return $chart;
    }

    public function get_gantt_data($opportunity_id, $type = 'milestones', $taskStatus = null)
    {
        $type_data = [];
        if ($type == 'milestones') {
            $type_data[] = [
                'name' => _l('milestones_uncategorized'),
                'id' => 0,
            ];
            $_milestones = $this->get_milestones($opportunity_id);
            foreach ($_milestones as $m) {
                $type_data[] = $m;
            }
        } elseif ($type == 'members') {
            $type_data[] = [
                'name' => _l('task_list_not_assigned'),
                'staff_id' => 0,
            ];
            $_members = $this->get_opportunity_members($opportunity_id);
            foreach ($_members as $m) {
                $type_data[] = $m;
            }
        } else {
            if (!$taskStatus) {
                $statuses = $this->tasks_model->get_statuses();
                foreach ($statuses as $status) {
                    $type_data[] = $status['id'];
                }
            } else {
                $type_data[] = $taskStatus;
            }
        }

        $gantt_data = [];
        $has_permission = has_permission('tasks', '', 'view');
        foreach ($type_data as $data) {
            if ($type == 'milestones') {
                $tasks = $this->get_tasks($opportunity_id, 'milestone=' . $data['id'] . ($taskStatus ? ' AND tblstafftasks.status=' . $taskStatus : ''), true);
                $name = $data['name'];
            } elseif ($type == 'members') {
                if ($data['staff_id'] != 0) {
                    $tasks = $this->get_tasks($opportunity_id, 'tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid=' . $data['staff_id'] . ')' . ($taskStatus ? ' AND tblstafftasks.status=' . $taskStatus : ''), true);
                    $name = get_staff_full_name($data['staff_id']);
                } else {
                    $tasks = $this->get_tasks($opportunity_id, 'tblstafftasks.id NOT IN (SELECT taskid FROM tblstafftaskassignees)' . ($taskStatus ? ' AND tblstafftasks.status=' . $taskStatus : ''), true);
                    $name = $data['name'];
                }
            } else {
                $tasks = $this->get_tasks($opportunity_id, [
                    'status' => $data,
                ], true);

                $name = format_task_status($data, false, true);
            }

            if (count($tasks) > 0) {
                $data = [];
                $data['values'] = [];
                $values = [];
                $data['desc'] = $tasks[0]['name'];
                $data['name'] = $name;
                $class = '';
                if ($tasks[0]['status'] == 5) {
                    $class = 'line-throught';
                }

                $values['from'] = strftime('%Y/%m/%d', strtotime($tasks[0]['startdate']));
                $values['to'] = strftime('%Y/%m/%d', strtotime($tasks[0]['duedate']));
                $values['desc'] = $tasks[0]['name'] . ' - ' . _l('task_total_logged_time') . ' ' . seconds_to_time_format($tasks[0]['total_logged_time']);
                $values['label'] = $tasks[0]['name'];
                if ($tasks[0]['duedate'] && date('Y-m-d') > $tasks[0]['duedate'] && $tasks[0]['status'] != 5) {
                    $values['customClass'] = 'ganttRed';
                } elseif ($tasks[0]['status'] == 5) {
                    $values['label'] = ' <i class="fa fa-check"></i> ' . $values['label'];
                    $values['customClass'] = 'ganttGreen';
                }
                $values['dataObj'] = [
                    'task_id' => $tasks[0]['id'],
                ];
                $data['values'][] = $values;
                $gantt_data[] = $data;
                unset($tasks[0]);
                foreach ($tasks as $task) {
                    $data = [];
                    $data['values'] = [];
                    $values = [];
                    $class = '';
                    if ($task['status'] == 5) {
                        $class = 'line-throught';
                    }
                    $data['desc'] = $task['name'];
                    $data['name'] = '';

                    $values['from'] = strftime('%Y/%m/%d', strtotime($task['startdate']));
                    $values['to'] = strftime('%Y/%m/%d', strtotime($task['duedate']));
                    $values['desc'] = $task['name'] . ' - ' . _l('task_total_logged_time') . ' ' . seconds_to_time_format($task['total_logged_time']);
                    $values['label'] = $task['name'];
                    if ($task['duedate'] && date('Y-m-d') > $task['duedate'] && $task['status'] != 5) {
                        $values['customClass'] = 'ganttRed';
                    } elseif ($task['status'] == 5) {
                        $values['label'] = ' <i class="fa fa-check"></i> ' . $values['label'];
                        $values['customClass'] = 'ganttGreen';
                    }

                    $values['dataObj'] = [
                        'task_id' => $task['id'],
                    ];
                    $data['values'][] = $values;
                    $gantt_data[] = $data;
                }
            }
        }

        return $gantt_data;
    }

    public function calc_milestone_logged_time($opportunity_id, $id)
    {
        $total = [];
        $tasks = $this->get_tasks($opportunity_id, [
            'milestone' => $id,
        ]);

        foreach ($tasks as $task) {
            $total[] = $task['total_logged_time'];
        }

        return array_sum($total);
    }

    public function total_logged_time($id)
    {
        $q = $this->db->query('
            SELECT SUM(CASE
                WHEN end_time is NULL THEN ' . time() . '-start_time
                ELSE end_time-start_time
                END) as total_logged_time
            FROM tbltaskstimers
            WHERE task_id IN (SELECT id FROM tblstafftasks WHERE rel_type="opportunity" AND rel_id=' . $id . ')')
            ->row();

        return $q->total_logged_time;
    }

    public function get_milestones($opportunity_id)
    {
        $this->db->where('opportunity_id', $opportunity_id);
        $this->db->order_by('milestone_order', 'ASC');
        $milestones = $this->db->get('tblmilestones')->result_array();
        $i = 0;
        foreach ($milestones as $milestone) {
            $milestones[$i]['total_logged_time'] = $this->calc_milestone_logged_time($opportunity_id, $milestone['id']);
            $i++;
        }

        return $milestones;
    }

    public function add_milestone($data)
    {
        $data['due_date'] = to_sql_date($data['due_date']);
        $data['datecreated'] = date('Y-m-d');
        $data['description'] = nl2br($data['description']);

        if (isset($data['description_visible_to_customer'])) {
            $data['description_visible_to_customer'] = 1;
        } else {
            $data['description_visible_to_customer'] = 0;
        }
        $this->db->insert('tblmilestones', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->db->where('id', $insert_id);
            $milestone = $this->db->get('tblmilestones')->row();
            $opportunity = $this->get($milestone->opportunity_id);
            if ($opportunity->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->opportunity_id, 'opportunity_activity_created_milestone', $milestone->name, $show_to_customer);
            logActivity('opportunity Milestone Created [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_milestone($data, $id)
    {
        $this->db->where('id', $id);
        $milestone = $this->db->get('tblmilestones')->row();
        $data['due_date'] = to_sql_date($data['due_date']);
        $data['description'] = nl2br($data['description']);

        if (isset($data['description_visible_to_customer'])) {
            $data['description_visible_to_customer'] = 1;
        } else {
            $data['description_visible_to_customer'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update('tblmilestones', $data);
        if ($this->db->affected_rows() > 0) {
            $opportunity = $this->get($milestone->opportunity_id);
            if ($opportunity->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->opportunity_id, 'opportunity_activity_updated_milestone', $milestone->name, $show_to_customer);
            logActivity('opportunity Milestone Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function update_task_milestone($data)
    {
        $this->db->where('id', $data['task_id']);
        $this->db->update('tblstafftasks', [
            'milestone' => $data['milestone_id'],
        ]);

        foreach ($data['order'] as $order) {
            $this->db->where('id', $order[0]);
            $this->db->update('tblstafftasks', [
                'milestone_order' => $order[1],
            ]);
        }
    }

    public function update_milestones_order($data)
    {
        foreach ($data['order'] as $status) {
            $this->db->where('id', $status[0]);
            $this->db->update('tblmilestones', [
                'milestone_order' => $status[1],
            ]);
        }
    }

    public function update_milestone_color($data)
    {
        $this->db->where('id', $data['milestone_id']);
        $this->db->update('tblmilestones', [
            'color' => $data['color'],
        ]);
    }

    public function delete_milestone($id)
    {
        $this->db->where('id', $id);
        $milestone = $this->db->get('tblmilestones')->row();
        $this->db->where('id', $id);
        $this->db->delete('tblmilestones');
        if ($this->db->affected_rows() > 0) {
            $opportunity = $this->get($milestone->opportunity_id);
            if ($opportunity->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->opportunity_id, 'opportunity_activity_deleted_milestone', $milestone->name, $show_to_customer);
            $this->db->where('milestone', $id);
            $this->db->update('tblstafftasks', [
                'milestone' => 0,
            ]);
            logActivity('opportunity Milestone Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function add($data)
    {
        if (isset($data['notify_opportunity_members_status_change'])) {
            unset($data['notify_opportunity_members_status_change']);
        }
        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }

        $send_opportunity_marked_as_finished_email_to_contacts = false;
        if (isset($data['opportunity_marked_as_finished_email_to_contacts'])) {
            unset($data['opportunity_marked_as_finished_email_to_contacts']);
            $send_opportunity_marked_as_finished_email_to_contacts = true;
        }

        if (isset($data['settings'])) {
            $opportunity_settings = $data['settings'];
            unset($data['settings']);
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }


        $data['start_date'] = to_sql_date($data['start_date']);

        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            unset($data['deadline']);
        }

        $data['opportunity_created'] = date('Y-m-d');
        if (isset($data['opportunity_members'])) {
            $opportunity_members = $data['opportunity_members'];
            unset($data['opportunity_members']);
        }
        if ($data['billing_type'] == 1) {
            $data['opportunity_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['opportunity_cost'] = 0;
        } else {
            $data['opportunity_rate_per_hour'] = 0;
            $data['opportunity_cost'] = 0;
        }

        $data['addedfrom'] = get_staff_user_id();

        $data = do_action('before_add_opportunity', $data);

        $tags = '';
        if (isset($data['tags'])) {
            $tags = $data['tags'];
            unset($data['tags']);
        }

        $this->db->insert('tblopportunities', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            handle_tags_save($tags, $insert_id, 'opportunity');

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            if (isset($opportunity_members)) {
                $_pm['opportunity_members'] = $opportunity_members;
                $this->add_edit_members($_pm, $insert_id);
            }

            $original_settings = $this->get_settings();
            if (isset($opportunity_settings)) {
                $_settings = [];
                $_values = [];
                foreach ($opportunity_settings as $name => $val) {
                    array_push($_settings, $name);
                    $_values[$name] = $val;
                }
                foreach ($original_settings as $setting) {
                    if ($setting != 'available_features') {
                        if (in_array($setting, $_settings)) {
                            $value_setting = 1;
                        } else {
                            $value_setting = 0;
                        }
                    } else {
                        $tabs = get_opportunity_tabs_admin(null, $data['rel_type']);
                        $tab_settings = [];
                        foreach ($_values[$setting] as $tab) {
                            $tab_settings[$tab] = 1;
                        }
                        foreach ($tabs as $tab) {
                            if (!isset($tab['dropdown'])) {
                                if (!in_array($tab['name'], $_values[$setting])) {
                                    $tab_settings[$tab['name']] = 0;
                                }
                            } else {
                                foreach ($tab['dropdown'] as $tab_dropdown) {
                                    if (!in_array($tab_dropdown['name'], $_values[$setting])) {
                                        $tab_settings[$tab_dropdown['name']] = 0;
                                    }
                                }
                            }
                        }
                        $value_setting = serialize($tab_settings);
                    }
                    $this->db->insert('tblopportunitysettings', [
                        'opportunity_id' => $insert_id,
                        'name' => $setting,
                        'value' => $value_setting,
                    ]);
                }
            } else {
                foreach ($original_settings as $setting) {
                    $value_setting = 0;
                    $this->db->insert('tblopportunitysettings', [
                        'opportunity_id' => $insert_id,
                        'name' => $setting,
                        'value' => $value_setting,
                    ]);
                }
            }
            $this->log_activity($insert_id, 'opportunity_activity_created');

            if ($send_created_email == true) {
                $this->send_opportunity_customer_email($insert_id, 'assigned-to-opportunity');
            }

            if ($send_opportunity_marked_as_finished_email_to_contacts == true) {
                $this->send_opportunity_customer_email($insert_id, 'opportunity-finished-to-customer');
            }

            do_action('after_add_opportunity', $insert_id);
            logActivity('New opportunity Created [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update($data, $id)
    {
        $this->db->select('status');
        $this->db->where('id', $id);
        $old_status = $this->db->get('tblopportunities')->row()->status;

        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }

        $send_opportunity_marked_as_finished_email_to_contacts = false;
        if (isset($data['opportunity_marked_as_finished_email_to_contacts'])) {
            unset($data['opportunity_marked_as_finished_email_to_contacts']);
            $send_opportunity_marked_as_finished_email_to_contacts = true;
        }

        $original_opportunity = $this->get($id);

        if (isset($data['notify_opportunity_members_status_change'])) {
            $notify_opportunity_members_status_change = true;
            unset($data['notify_opportunity_members_status_change']);
        }
        $affectedRows = 0;
        if (!isset($data['settings'])) {
            $this->db->where('opportunity_id', $id);
            $this->db->update('tblopportunitysettings', [
                'value' => 0,
            ]);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $_settings = [];
            $_values = [];

            foreach ($data['settings'] as $name => $val) {
                array_push($_settings, $name);
                $_values[$name] = $val;
            }

            unset($data['settings']);
            $original_settings = $this->get_opportunity_settings($id);

            foreach ($original_settings as $setting) {
                if ($setting['name'] != 'available_features') {
                    if (in_array($setting['name'], $_settings)) {
                        $value_setting = 1;
                    } else {
                        $value_setting = 0;
                    }
                } else {
                    $tabs = get_opportunity_tabs_admin(null, $data['rel_type']);
                    $tab_settings = [];
                    foreach ($_values[$setting['name']] as $tab) {
                        $tab_settings[$tab] = 1;
                    }
                    foreach ($tabs as $tab) {
                        if (!isset($tab['dropdown'])) {
                            if (!in_array($tab['name'], $_values[$setting['name']])) {
                                $tab_settings[$tab['name']] = 0;
                            }
                        } else {
                            foreach ($tab['dropdown'] as $tab_dropdown) {
                                if (!in_array($tab_dropdown['name'], $_values[$setting['name']])) {
                                    $tab_settings[$tab_dropdown['name']] = 0;
                                }
                            }
                        }
                    }
                    $value_setting = serialize($tab_settings);
                }


                $this->db->where('opportunity_id', $id);
                $this->db->where('name', $setting['name']);
                $this->db->update('tblopportunitysettings', [
                    'value' => $value_setting,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }

        if ($old_status == 4 && $data['status'] != 4) {
            $data['date_finished'] = null;
        } elseif (isset($data['date_finished'])) {
            $data['date_finished'] = to_sql_date($data['date_finished'], true);
        }

        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $data['deadline'] = null;
        }

        $data['start_date'] = to_sql_date($data['start_date']);
        if ($data['billing_type'] == 1) {
            $data['opportunity_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['opportunity_cost'] = 0;
        } else {
            $data['opportunity_rate_per_hour'] = 0;
            $data['opportunity_cost'] = 0;
        }
        if (isset($data['opportunity_members'])) {
            $opportunity_members = $data['opportunity_members'];
            unset($data['opportunity_members']);
        }
        $_pm = [];
        if (isset($opportunity_members)) {
            $_pm['opportunity_members'] = $opportunity_members;
        }
        if ($this->add_edit_members($_pm, $id)) {
            $affectedRows++;
        }
        if (isset($data['mark_all_tasks_as_completed'])) {
            $mark_all_tasks_as_completed = true;
            unset($data['mark_all_tasks_as_completed']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'opportunity')) {
                $affectedRows++;
            }
            unset($data['tags']);
        }

        $_data['data'] = $data;
        $_data['id'] = $id;

        $_data = do_action('before_update_opportunity', $_data);

        $data = $_data['data'];

        $this->db->where('id', $id);
        $this->db->update('tblopportunities', $data);

        if ($this->db->affected_rows() > 0) {
            if (isset($mark_all_tasks_as_completed)) {
                $this->_mark_all_opportunity_tasks_as_completed($id);
            }
            $affectedRows++;
        }

        if ($send_created_email == true) {
            if ($this->send_opportunity_customer_email($id, 'assigned-to-opportunity')) {
                $affectedRows++;
            }
        }

        if ($send_opportunity_marked_as_finished_email_to_contacts == true) {
            if ($this->send_opportunity_customer_email($id, 'opportunity-finished-to-customer')) {
                $affectedRows++;
            }
        }
        if ($affectedRows > 0) {
            $this->log_activity($id, 'opportunity_activity_updated');
            logActivity('opportunity Updated [ID: ' . $id . ']');

            if ($original_opportunity->status != $data['status']) {
                do_action('opportunity_status_changed', [
                    'status' => $data['status'],
                    'opportunity_id' => $id,
                ]);
                // Give space this log to be on top
                sleep(1);
                if ($data['status'] == 4) {
                    $this->log_activity($id, 'opportunity_marked_as_finished');
                    $this->db->where('id', $id);
                    $this->db->update('tblopportunities', ['date_finished' => date('Y-m-d H:i:s')]);
                } else {
                    $this->log_activity($id, 'opportunity_status_updated', '<b><lang>opportunity_status_' . $data['status'] . '</lang></b>');
                }

                if (isset($notify_opportunity_members_status_change)) {
                    $this->_notify_opportunity_members_status_change($id, $original_opportunity->status, $data['status']);
                }
            }
            do_action('after_update_opportunity', $id);

            return true;
        }

        return false;
    }

    /**
     * Simplified function to send non complicated email templates for opportunity contacts
     * @param  mixed $id opportunity id
     * @return boolean
     */
    public function send_opportunity_customer_email($id, $template)
    {
        $this->db->select('clientid');
        $this->db->where('id', $id);
        $clientid = $this->db->get('tblopportunities')->row()->clientid;

        $sent = false;
        $contacts = $this->clients_model->get_contacts($clientid, ['active' => 1, 'opportunity_emails' => 1]);
        $this->load->model('emails_model');
        foreach ($contacts as $contact) {
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($clientid, $contact['id']));
            $merge_fields = array_merge($merge_fields, get_opportunity_merge_fields($id, [
                'customer_template' => true,
            ]));
            if ($this->emails_model->send_email_template($template, $contact['email'], $merge_fields)) {
                $send = true;
            }
        }

        return $sent;
    }

    public function mark_as($data)
    {
        $this->db->select('status');
        $this->db->where('id', $data['opportunity_id']);
        $old_status = $this->db->get('tblopportunities')->row()->status;

        $this->db->where('id', $data['opportunity_id']);
        $this->db->update('tblopportunities', [
            'status' => $data['status_id'],
        ]);
        if ($this->db->affected_rows() > 0) {
            do_action('opportunity_status_changed', [
                'status' => $data['status_id'],
                'opportunity_id' => $data['opportunity_id'],
            ]);

            if ($data['status_id'] == 4) {
                $this->log_activity($data['opportunity_id'], 'opportunity_marked_as_finished');
                $this->db->where('id', $data['opportunity_id']);
                $this->db->update('tblopportunities', ['date_finished' => date('Y-m-d H:i:s')]);
            } else {
                $this->log_activity($data['opportunity_id'], 'opportunity_status_updated', '<b><lang>opportunity_status_' . $data['status_id'] . '</lang></b>');
                if ($old_status == 4) {
                    $this->db->update('tblopportunities', ['date_finished' => null]);
                }
            }

            if ($data['notify_opportunity_members_status_change'] == 1) {
                $this->_notify_opportunity_members_status_change($data['opportunity_id'], $old_status, $data['status_id']);
            }
            if ($data['mark_all_tasks_as_completed'] == 1) {
                $this->_mark_all_opportunity_tasks_as_completed($data['opportunity_id']);
            }

            if (isset($data['send_opportunity_marked_as_finished_email_to_contacts']) && $data['send_opportunity_marked_as_finished_email_to_contacts'] == 1) {
                $this->send_opportunity_customer_email($data['opportunity_id'], 'opportunity-finished-to-customer');
            }

            return true;
        }


        return false;
    }

    private function _notify_opportunity_members_status_change($id, $old_status, $new_status)
    {
        $members = $this->get_opportunity_members($id);
        $notifiedUsers = [];
        foreach ($members as $member) {
            if ($member['staff_id'] != get_staff_user_id()) {
                $notified = add_notification([
                    'fromuserid' => get_staff_user_id(),
                    'description' => 'not_opportunity_status_updated',
                    'link' => 'opportunities/view/' . $id,
                    'touserid' => $member['staff_id'],
                    'additional_data' => serialize([
                        '<lang>opportunity_status_' . $old_status . '</lang>',
                        '<lang>opportunity_status_' . $new_status . '</lang>',
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
        }
        pusher_trigger_notification($notifiedUsers);
    }

    private function _mark_all_opportunity_tasks_as_completed($id)
    {
        $this->db->where('rel_type', 'opportunity');
        $this->db->where('rel_id', $id);
        $this->db->update('tblstafftasks', [
            'status' => 5,
            'datefinished' => date('Y-m-d H:i:s'),
        ]);
        $tasks = $this->get_tasks($id);
        foreach ($tasks as $task) {
            $this->db->where('task_id', $task['id']);
            $this->db->where('end_time IS NULL');
            $this->db->update('tbltaskstimers', [
                'end_time' => time(),
            ]);
        }
        $this->log_activity($id, 'opportunity_activity_marked_all_tasks_as_complete');
    }

    public function add_edit_members($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['opportunity_members'])) {
            $opportunity_members = $data['opportunity_members'];
        }

        $new_opportunity_members_to_receive_email = [];
        $this->db->select('name,rel_id,rel_type');
        $this->db->where('id', $id);
        $opportunity = $this->db->get('tblopportunities')->row();
        $opportunity_name = $opportunity->name;
        $rel_type = $opportunity->rel_type;
        $client_id = $opportunity->rel_id;

        $opportunity_members_in = $this->get_opportunity_members($id);
        if (sizeof($opportunity_members_in) > 0) {
            foreach ($opportunity_members_in as $opportunity_member) {
                if (isset($opportunity_members)) {
                    if (!in_array($opportunity_member['staff_id'], $opportunity_members)) {
                        $this->db->where('opportunity_id', $id);
                        $this->db->where('staff_id', $opportunity_member['staff_id']);
                        $this->db->delete('tblopportunitymembers');
                        if ($this->db->affected_rows() > 0) {
                            $this->db->where('staff_id', $opportunity_member['staff_id']);
                            $this->db->where('opportunity_id', $id);
                            $this->db->delete('tblpinnedopportunities');

                            $this->log_activity($id, 'opportunity_activity_removed_team_member', get_staff_full_name($opportunity_member['staff_id']));
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('opportunity_id', $id);
                    $this->db->delete('tblopportunitymembers');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($opportunity_members)) {
                $notifiedUsers = [];
                foreach ($opportunity_members as $staff_id) {
                    $this->db->where('opportunity_id', $id);
                    $this->db->where('staff_id', $staff_id);
                    $_exists = $this->db->get('tblopportunitymembers')->row();
                    if (!$_exists) {
                        if (empty($staff_id)) {
                            continue;
                        }
                        $this->db->insert('tblopportunitymembers', [
                            'opportunity_id' => $id,
                            'staff_id' => $staff_id,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            if ($staff_id != get_staff_user_id()) {
                                $notified = add_notification([
                                    'fromuserid' => get_staff_user_id(),
                                    'description' => 'not_staff_added_as_opportunity_member',
                                    'link' => 'opportunities/view/' . $id,
                                    'touserid' => $staff_id,
                                    'additional_data' => serialize([
                                        $opportunity_name,
                                    ]),
                                ]);
                                array_push($new_opportunity_members_to_receive_email, $staff_id);
                                if ($notified) {
                                    array_push($notifiedUsers, $staff_id);
                                }
                            }


                            $this->log_activity($id, 'opportunity_activity_added_team_member', get_staff_full_name($staff_id));
                            $affectedRows++;
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        } else {
            if (isset($opportunity_members)) {
                $notifiedUsers = [];
                foreach ($opportunity_members as $staff_id) {
                    if (empty($staff_id)) {
                        continue;
                    }
                    $this->db->insert('tblopportunitymembers', [
                        'opportunity_id' => $id,
                        'staff_id' => $staff_id,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        if ($staff_id != get_staff_user_id()) {
                            $notified = add_notification([
                                'fromuserid' => get_staff_user_id(),
                                'description' => 'not_staff_added_as_opportunity_member',
                                'link' => 'opportunities/view/' . $id,
                                'touserid' => $staff_id,
                                'additional_data' => serialize([
                                    $opportunity_name,
                                ]),
                            ]);
                            array_push($new_opportunity_members_to_receive_email, $staff_id);
                            if ($notifiedUsers) {
                                array_push($notifiedUsers, $staff_id);
                            }
                        }
                        $this->log_activity($id, 'opportunity_activity_added_team_member', get_staff_full_name($staff_id));
                        $affectedRows++;
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        }

        if (count($new_opportunity_members_to_receive_email) > 0) {
            $this->load->model('emails_model');
            $all_members = $this->get_opportunity_members($id);
            foreach ($all_members as $data) {
                if (in_array($data['staff_id'], $new_opportunity_members_to_receive_email)) {
                    $merge_fields = [];
                    if ($rel_type == 'customer') {
                        $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($client_id));
                    }
                    $merge_fields = array_merge($merge_fields, get_staff_merge_fields($data['staff_id']));
                    $merge_fields = array_merge($merge_fields, get_opportunity_merge_fields($id));
                    $this->emails_model->send_email_template('staff-added-as-opportunity-member', $data['email'], $merge_fields);
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    public function is_member($opportunity_id, $staff_id = '')
    {
        if (!is_numeric($staff_id)) {
            $staff_id = get_staff_user_id();
        }
        $member = total_rows('tblopportunitymembers', [
            'staff_id' => $staff_id,
            'opportunity_id' => $opportunity_id,
        ]);
        if ($member > 0) {
            return true;
        }

        return false;
    }

    public function get_opportunities_for_ticket($client_id)
    {
        return $this->get('', [
            'clientid' => $client_id,
        ]);
    }

    public function get_opportunity_settings($opportunity_id)
    {
        $this->db->where('opportunity_id', $opportunity_id);

        return $this->db->get('tblopportunitysettings')->result_array();
    }

    public function get_opportunity_members($id)
    {
        $this->db->select('email,opportunity_id,staff_id');
        $this->db->join('tblstaff', 'tblstaff.staffid=tblopportunitymembers.staff_id');
        $this->db->where('opportunity_id', $id);

        return $this->db->get('tblopportunitymembers')->result_array();
    }

    public function remove_team_member($opportunity_id, $staff_id)
    {
        $this->db->where('opportunity_id', $opportunity_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete('tblopportunitymembers');
        if ($this->db->affected_rows() > 0) {

            // Remove member from tasks where is assigned
            $this->db->where('staffid', $staff_id);
            $this->db->where('taskid IN (SELECT id FROM tblstafftasks WHERE rel_type="opportunity" AND rel_id="' . $opportunity_id . '")');
            $this->db->delete('tblstafftaskassignees');

            $this->log_activity($opportunity_id, 'opportunity_activity_removed_team_member', get_staff_full_name($staff_id));

            return true;
        }

        return false;
    }

    public function get_timesheets($opportunity_id, $tasks_ids = [])
    {
        if (count($tasks_ids) == 0) {
            $tasks = $this->get_tasks($opportunity_id);
            $tasks_ids = [];
            foreach ($tasks as $task) {
                array_push($tasks_ids, $task['id']);
            }
        }
        if (count($tasks_ids) > 0) {
            $this->db->where('task_id IN(' . implode(', ', $tasks_ids) . ')');
            $timesheets = $this->db->get('tbltaskstimers')->result_array();
            $i = 0;
            foreach ($timesheets as $t) {
                $task = $this->tasks_model->get($t['task_id']);
                $timesheets[$i]['task_data'] = $task;
                $timesheets[$i]['staff_name'] = get_staff_full_name($t['staff_id']);
                if (!is_null($t['end_time'])) {
                    $timesheets[$i]['total_spent'] = $t['end_time'] - $t['start_time'];
                } else {
                    $timesheets[$i]['total_spent'] = time() - $t['start_time'];
                }
                $i++;
            }

            return $timesheets;
        }

        return [];
    }

    public function get_discussion($id, $opportunity_id = '')
    {
        if ($opportunity_id != '') {
            $this->db->where('opportunity_id', $opportunity_id);
        }
        $this->db->where('id', $id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
            $this->db->where('opportunity_id IN (SELECT id FROM tblopportunities WHERE clientid=' . get_client_user_id() . ')');
        }
        $discussion = $this->db->get('tblopportunitydiscussions')->row();
        if ($discussion) {
            return $discussion;
        }

        return false;
    }

    public function get_discussion_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get('tblopportunitydiscussioncomments')->row();
        if ($comment->contact_id != 0) {
            if (is_client_logged_in()) {
                if ($comment->contact_id == get_contact_user_id()) {
                    $comment->created_by_current_user = true;
                } else {
                    $comment->created_by_current_user = false;
                }
            } else {
                $comment->created_by_current_user = false;
            }
            $comment->profile_picture_url = contact_profile_image_url($comment->contact_id);
        } else {
            if (is_client_logged_in()) {
                $comment->created_by_current_user = false;
            } else {
                if (is_staff_logged_in()) {
                    if ($comment->staff_id == get_staff_user_id()) {
                        $comment->created_by_current_user = true;
                    } else {
                        $comment->created_by_current_user = false;
                    }
                } else {
                    $comment->created_by_current_user = false;
                }
            }
            if (is_admin($comment->staff_id)) {
                $comment->created_by_admin = true;
            } else {
                $comment->created_by_admin = false;
            }
            $comment->profile_picture_url = staff_profile_image_url($comment->staff_id);
        }
        $comment->created = (strtotime($comment->created) * 1000);
        if (!empty($comment->modified)) {
            $comment->modified = (strtotime($comment->modified) * 1000);
        }
        if (!is_null($comment->file_name)) {
            $comment->file_url = site_url('uploads/discussions/' . $comment->discussion_id . '/' . $comment->file_name);
        }

        return $comment;
    }

    public function get_discussion_comments($id, $type)
    {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments = $this->db->get('tblopportunitydiscussioncomments')->result_array();
        $i = 0;
        $allCommentsIDS = [];
        $allCommentsParentIDS = [];
        foreach ($comments as $comment) {
            $allCommentsIDS[] = $comment['id'];
            if (!empty($comment['parent'])) {
                $allCommentsParentIDS[] = $comment['parent'];
            }

            if ($comment['contact_id'] != 0) {
                if (is_client_logged_in()) {
                    if ($comment['contact_id'] == get_contact_user_id()) {
                        $comments[$i]['created_by_current_user'] = true;
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                } else {
                    $comments[$i]['created_by_current_user'] = false;
                }
                $comments[$i]['profile_picture_url'] = contact_profile_image_url($comment['contact_id']);
            } else {
                if (is_client_logged_in()) {
                    $comments[$i]['created_by_current_user'] = false;
                } else {
                    if (is_staff_logged_in()) {
                        if ($comment['staff_id'] == get_staff_user_id()) {
                            $comments[$i]['created_by_current_user'] = true;
                        } else {
                            $comments[$i]['created_by_current_user'] = false;
                        }
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                }
                if (is_admin($comment['staff_id'])) {
                    $comments[$i]['created_by_admin'] = true;
                } else {
                    $comments[$i]['created_by_admin'] = false;
                }
                $comments[$i]['profile_picture_url'] = staff_profile_image_url($comment['staff_id']);
            }
            if (!is_null($comment['file_name'])) {
                $comments[$i]['file_url'] = site_url('uploads/discussions/' . $id . '/' . $comment['file_name']);
            }
            $comments[$i]['created'] = (strtotime($comment['created']) * 1000);
            if (!empty($comment['modified'])) {
                $comments[$i]['modified'] = (strtotime($comment['modified']) * 1000);
            }
            $i++;
        }

        // Ticket #5471
        foreach ($allCommentsParentIDS as $parent_id) {
            if (!in_array($parent_id, $allCommentsIDS)) {
                foreach ($comments as $key => $comment) {
                    if ($comment['parent'] == $parent_id) {
                        $comments[$key]['parent'] = null;
                    }
                }
            }
        }

        return $comments;
    }

    public function get_discussions($opportunity_id)
    {
        $this->db->where('opportunity_id', $opportunity_id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
        }
        $discussions = $this->db->get('tblopportunitydiscussions')->result_array();
        $i = 0;
        foreach ($discussions as $discussion) {
            $discussions[$i]['total_comments'] = total_rows('tblopportunitydiscussioncomments', [
                'discussion_id' => $discussion['id'],
                'discussion_type' => 'regular',
            ]);
            $i++;
        }

        return $discussions;
    }

    public function add_discussion_comment($data, $discussion_id, $type)
    {
        $discussion = $this->get_discussion($discussion_id);
        $_data['discussion_id'] = $discussion_id;
        $_data['discussion_type'] = $type;
        if (isset($data['content'])) {
            $_data['content'] = $data['content'];
        }
        if (isset($data['parent']) && $data['parent'] != null) {
            $_data['parent'] = $data['parent'];
        }
        if (is_client_logged_in()) {
            $_data['contact_id'] = get_contact_user_id();
            $_data['fullname'] = get_contact_full_name($_data['contact_id']);
            $_data['staff_id'] = 0;
        } else {
            $_data['contact_id'] = 0;
            $_data['staff_id'] = get_staff_user_id();
            $_data['fullname'] = get_staff_full_name($_data['staff_id']);
        }
        $_data = handle_opportunity_discussion_comment_attachments($discussion_id, $data, $_data);
        $_data['created'] = date('Y-m-d H:i:s');
        $this->db->insert('tblopportunitydiscussioncomments', $_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if ($type == 'regular') {
                $discussion = $this->get_discussion($discussion_id);
                $not_link = 'opportunities/view/' . $discussion->opportunity_id . '?group=opportunity_discussions&discussion_id=' . $discussion_id;
            } else {
                $discussion = $this->get_file($discussion_id);
                $not_link = 'opportunities/view/' . $discussion->opportunity_id . '?group=opportunity_files&file_id=' . $discussion_id;
                $discussion->show_to_customer = $discussion->visible_to_customer;
            }

            $this->send_opportunity_email_template($discussion->opportunity_id, 'new-opportunity-discussion-comment-to-staff', 'new-opportunity-discussion-comment-to-customer', $discussion->show_to_customer, [
                'staff' => [
                    'discussion_id' => $discussion_id,
                    'discussion_comment_id' => $insert_id,
                    'discussion_type' => $type,
                ],
                'customers' => [
                    'customer_template' => true,
                    'discussion_id' => $discussion_id,
                    'discussion_comment_id' => $insert_id,
                    'discussion_type' => $type,
                ],
            ]);


            $this->log_activity($discussion->opportunity_id, 'opportunity_activity_commented_on_discussion', $discussion->subject, $discussion->show_to_customer);

            $notification_data = [
                'description' => 'not_commented_on_opportunity_discussion',
                'link' => $not_link,
            ];

            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }

            $members = $this->get_opportunity_members($discussion->opportunity_id);
            $notifiedUsers = [];
            foreach ($members as $member) {
                if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                    continue;
                }
                $notification_data['touserid'] = $member['staff_id'];
                if (add_notification($notification_data)) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
            pusher_trigger_notification($notifiedUsers);

            $this->_update_discussion_last_activity($discussion_id, $type);

            return $this->get_discussion_comment($insert_id);
        }

        return false;
    }

    public function update_discussion_comment($data)
    {
        $comment = $this->get_discussion_comment($data['id']);
        $this->db->where('id', $data['id']);
        $this->db->update('tblopportunitydiscussioncomments', [
            'modified' => date('Y-m-d H:i:s'),
            'content' => $data['content'],
        ]);
        if ($this->db->affected_rows() > 0) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type);
        }

        return $this->get_discussion_comment($data['id']);
    }

    public function delete_discussion_comment($id, $logActivity = true)
    {
        $comment = $this->get_discussion_comment($id);
        $this->db->where('id', $id);
        $this->db->delete('tblopportunitydiscussioncomments');
        if ($this->db->affected_rows() > 0) {
            $this->delete_discussion_comment_attachment($comment->file_name, $comment->discussion_id);
            if ($logActivity) {
                $additional_data = '';
                if ($comment->discussion_type == 'regular') {
                    $discussion = $this->get_discussion($comment->discussion_id);
                    $not = 'opportunity_activity_deleted_discussion_comment';
                    $additional_data .= $discussion->subject . '<br />' . $comment->content;
                } else {
                    $discussion = $this->get_file($comment->discussion_id);
                    $not = 'opportunity_activity_deleted_file_discussion_comment';
                    $additional_data .= $discussion->subject . '<br />' . $comment->content;
                }

                if (!is_null($comment->file_name)) {
                    $additional_data .= $comment->file_name;
                }

                $this->log_activity($discussion->opportunity_id, $not, $additional_data);
            }
        }

        $this->db->where('parent', $id);
        $this->db->update('tblopportunitydiscussioncomments', [
            'parent' => null,
        ]);

        if ($this->db->affected_rows() > 0 && $logActivity) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type);
        }

        return true;
    }

    public function delete_discussion_comment_attachment($file_name, $discussion_id)
    {
        $path = opportunity_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id;
        if (!is_null($file_name)) {
            if (file_exists($path . '/' . $file_name)) {
                unlink($path . '/' . $file_name);
            }
        }
        if (is_dir($path)) {
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files($path);
            if (count($other_attachments) == 0) {
                delete_dir($path);
            }
        }
    }

    public function add_discussion($data)
    {
        if (is_client_logged_in()) {
            $data['contact_id'] = get_contact_user_id();
            $data['staff_id'] = 0;
            $data['show_to_customer'] = 1;
        } else {
            $data['staff_id'] = get_staff_user_id();
            $data['contact_id'] = 0;
            if (isset($data['show_to_customer'])) {
                $data['show_to_customer'] = 1;
            } else {
                $data['show_to_customer'] = 0;
            }
        }
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['description'] = nl2br($data['description']);
        $this->db->insert('tblopportunitydiscussions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $members = $this->get_opportunity_members($data['opportunity_id']);
            $notification_data = [
                'description' => 'not_created_new_opportunity_discussion',
                'link' => 'opportunities/view/' . $data['opportunity_id'] . '?group=opportunity_discussions&discussion_id=' . $insert_id,
            ];

            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }

            $notifiedUsers = [];
            foreach ($members as $member) {
                if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                    continue;
                }
                $notification_data['touserid'] = $member['staff_id'];
                if (add_notification($notification_data)) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
            pusher_trigger_notification($notifiedUsers);
            $this->send_opportunity_email_template($data['opportunity_id'], 'new-opportunity-discussion-created-to-staff', 'new-opportunity-discussion-created-to-customer', $data['show_to_customer'], [
                'staff' => [
                    'discussion_id' => $insert_id,
                    'discussion_type' => 'regular',
                ],
                'customers' => [
                    'customer_template' => true,
                    'discussion_id' => $insert_id,
                    'discussion_type' => 'regular',
                ],
            ]);
            $this->log_activity($data['opportunity_id'], 'opportunity_activity_created_discussion', $data['subject'], $data['show_to_customer']);

            return $insert_id;
        }

        return false;
    }

    public function edit_discussion($data, $id)
    {
        $this->db->where('id', $id);
        if (isset($data['show_to_customer'])) {
            $data['show_to_customer'] = 1;
        } else {
            $data['show_to_customer'] = 0;
        }
        $data['description'] = nl2br($data['description']);
        $this->db->update('tblopportunitydiscussions', $data);
        if ($this->db->affected_rows() > 0) {
            $this->log_activity($data['opportunity_id'], 'opportunity_activity_updated_discussion', $data['subject'], $data['show_to_customer']);

            return true;
        }

        return false;
    }

    public function delete_discussion($id, $logActivity = true)
    {
        $discussion = $this->get_discussion($id);
        $this->db->where('id', $id);
        $this->db->delete('tblopportunitydiscussions');
        if ($this->db->affected_rows() > 0) {
            if ($logActivity) {
                $this->log_activity($discussion->opportunity_id, 'opportunity_activity_deleted_discussion', $discussion->subject, $discussion->show_to_customer);
            }
            $this->_delete_discussion_comments($id, 'regular');

            return true;
        }

        return false;
    }

    public function copy($opportunity_id, $data)
    {
        $opportunity = $this->get($opportunity_id);
        $settings = $this->get_opportunity_settings($opportunity_id);
        $_new_data = [];
        $fields = $this->db->list_fields('tblopportunities');
        foreach ($fields as $field) {
            if (isset($opportunity->$field)) {
                $_new_data[$field] = $opportunity->$field;
            }
        }

        unset($_new_data['id']);
        $_new_data['clientid'] = $data['clientid_copy_opportunity'];
        unset($_new_data['clientid_copy_opportunity']);

        $_new_data['start_date'] = to_sql_date($data['start_date']);

        if ($_new_data['start_date'] > date('Y-m-d')) {
            $_new_data['status'] = 1;
        } else {
            $_new_data['status'] = 2;
        }
        if ($data['deadline']) {
            $_new_data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $_new_data['deadline'] = null;
        }

        $_new_data['opportunity_created'] = date('Y-m-d H:i:s');
        $_new_data['addedfrom'] = get_staff_user_id();

        $_new_data['date_finished'] = null;

        $this->db->insert('tblopportunities', $_new_data);
        $id = $this->db->insert_id();
        if ($id) {
            $tags = get_tags_in($opportunity_id, 'opportunity');
            handle_tags_save($tags, $id, 'opportunity');

            foreach ($settings as $setting) {
                $this->db->insert('tblopportunitysettings', [
                    'opportunity_id' => $id,
                    'name' => $setting['name'],
                    'value' => $setting['value'],
                ]);
            }
            $added_tasks = [];
            $tasks = $this->get_tasks($opportunity_id);
            if (isset($data['tasks'])) {
                foreach ($tasks as $task) {
                    if (isset($data['task_include_followers'])) {
                        $copy_task_data['copy_task_followers'] = 'true';
                    }
                    if (isset($data['task_include_assignees'])) {
                        $copy_task_data['copy_task_assignees'] = 'true';
                    }
                    if (isset($data['tasks_include_checklist_items'])) {
                        $copy_task_data['copy_task_checklist_items'] = 'true';
                    }
                    $copy_task_data['copy_from'] = $task['id'];
                    $task_id = $this->tasks_model->copy($copy_task_data, [
                        'rel_id' => $id,
                        'rel_type' => 'opportunity',
                        'last_recurring_date' => null,
                        'status' => $data['copy_opportunity_task_status'],
                    ]);
                    if ($task_id) {
                        array_push($added_tasks, $task_id);
                    }
                }
            }
            if (isset($data['milestones'])) {
                $milestones = $this->get_milestones($opportunity_id);
                $_added_milestones = [];
                foreach ($milestones as $milestone) {
                    $dCreated = new DateTime($milestone['datecreated']);
                    $dDuedate = new DateTime($milestone['due_date']);
                    $dDiff = $dCreated->diff($dDuedate);
                    $due_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY'))));

                    $this->db->insert('tblmilestones', [
                        'name' => $milestone['name'],
                        'opportunity_id' => $id,
                        'milestone_order' => $milestone['milestone_order'],
                        'description_visible_to_customer' => $milestone['description_visible_to_customer'],
                        'description' => $milestone['description'],
                        'due_date' => $due_date,
                        'datecreated' => date('Y-m-d'),
                        'color' => $milestone['color'],
                    ]);

                    $milestone_id = $this->db->insert_id();
                    if ($milestone_id) {
                        $_added_milestone_data = [];
                        $_added_milestone_data['id'] = $milestone_id;
                        $_added_milestone_data['name'] = $milestone['name'];
                        $_added_milestones[] = $_added_milestone_data;
                    }
                }
                if (isset($data['tasks'])) {
                    if (count($added_tasks) > 0) {
                        // Original opportunity tasks
                        foreach ($tasks as $task) {
                            if ($task['milestone'] != 0) {
                                $this->db->where('id', $task['milestone']);
                                $milestone = $this->db->get('tblmilestones')->row();
                                if ($milestone) {
                                    $name = $milestone->name;
                                    foreach ($_added_milestones as $added_milestone) {
                                        if ($name == $added_milestone['name']) {
                                            $this->db->where('id IN (' . implode(', ', $added_tasks) . ')');
                                            $this->db->where('milestone', $task['milestone']);
                                            $this->db->update('tblstafftasks', [
                                                'milestone' => $added_milestone['id'],
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // milestones not set
                if (count($added_tasks)) {
                    foreach ($added_tasks as $task) {
                        $this->db->where('id', $task['id']);
                        $this->db->update('tblstafftasks', [
                            'milestone' => 0,
                        ]);
                    }
                }
            }
            if (isset($data['members'])) {
                $members = $this->get_opportunity_members($opportunity_id);
                $_members = [];
                foreach ($members as $member) {
                    array_push($_members, $member['staff_id']);
                }
                $this->add_edit_members([
                    'opportunity_members' => $_members,
                ], $id);
            }

            $custom_fields = get_custom_fields('opportunities');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($opportunity_id, $field['id'], 'opportunities', false);
                if ($value != '') {
                    $this->db->insert('tblcustomfieldsvalues', [
                        'relid' => $id,
                        'fieldid' => $field['id'],
                        'fieldto' => 'opportunities',
                        'value' => $value,
                    ]);
                }
            }

            $this->log_activity($id, 'opportunity_activity_created');
            logActivity('opportunity Copied [ID: ' . $opportunity_id . ', NewID: ' . $id . ']');

            return $id;
        }

        return false;
    }

    public function get_staff_notes($opportunity_id)
    {
        $this->db->where('opportunity_id', $opportunity_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get('tblopportunitynotes')->row();
        if ($notes) {
            return $notes->content;
        }

        return '';
    }

    public function save_note($data, $opportunity_id)
    {
        // Check if the note exists for this opportunity;
        $this->db->where('opportunity_id', $opportunity_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get('tblopportunitynotes')->row();
        if ($notes) {
            $this->db->where('id', $notes->id);
            $this->db->update('tblopportunitynotes', [
                'content' => $data['content'],
            ]);
            if ($this->db->affected_rows() > 0) {
                return true;
            }

            return false;
        }
        $this->db->insert('tblopportunitynotes', [
            'staff_id' => get_staff_user_id(),
            'content' => $data['content'],
            'opportunity_id' => $opportunity_id,
        ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return true;
        }

        return false;


        return false;
    }

    public function delete($opportunity_id)
    {
        $opportunity_name = get_opportunity_name_by_id($opportunity_id);

        $this->db->where('id', $opportunity_id);
        $this->db->delete('tblopportunities');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->delete('tblopportunitymembers');

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->delete('tblopportunitynotes');

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->delete('tblmilestones');

            // Delete the custom field values
            $this->db->where('relid', $opportunity_id);
            $this->db->where('fieldto', 'opportunities');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('rel_id', $opportunity_id);
            $this->db->where('rel_type', 'opportunity');
            $this->db->delete('tbltags_in');


            $this->db->where('opportunity_id', $opportunity_id);
            $discussions = $this->db->get('tblopportunitydiscussions')->result_array();
            foreach ($discussions as $discussion) {
                $discussion_comments = $this->get_discussion_comments($discussion['id'], 'regular');
                foreach ($discussion_comments as $comment) {
                    $this->delete_discussion_comment_attachment($comment['file_name'], $discussion['id']);
                }
                $this->db->where('discussion_id', $discussion['id']);
                $this->db->delete('tblopportunitydiscussioncomments');
            }
            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->delete('tblopportunitydiscussions');

            $files = $this->get_files($opportunity_id);
            foreach ($files as $file) {
                $this->remove_file($file['id']);
            }

            $tasks = $this->get_tasks($opportunity_id);
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false);
            }

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->delete('tblopportunitysettings');

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->delete('tblopportunityactivity');

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->update('tblexpenses', [
                'opportunity_id' => 0,
            ]);

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->update('tblinvoices', [
                'opportunity_id' => 0,
            ]);

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->update('tblcreditnotes', [
                'opportunity_id' => 0,
            ]);

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->update('tblestimates', [
                'opportunity_id' => 0,
            ]);

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->update('tbltickets', [
                'opportunity_id' => 0,
            ]);

            $this->db->where('opportunity_id', $opportunity_id);
            $this->db->delete('tblpinnedopportunities');

            logActivity('opportunity Deleted [ID: ' . $opportunity_id . ', Name: ' . $opportunity_name . ']');

            return true;
        }

        return false;
    }

    public function get_activity($id = '', $limit = '', $only_opportunity_members_activity = false)
    {
        if (!is_client_logged_in()) {
            $has_permission = has_permission('opportunities', '', 'view');
            if (!$has_permission) {
                $this->db->where('opportunity_id IN (SELECT opportunity_id FROM tblopportunitymembers WHERE staff_id=' . get_staff_user_id() . ')');
            }
        }
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        if (is_numeric($id)) {
            $this->db->where('opportunity_id', $id);
        }
        if (is_numeric($limit)) {
            $this->db->limit($limit);
        }
        $this->db->order_by('dateadded', 'desc');
        $activities = $this->db->get('tblopportunityactivity')->result_array();
        $i = 0;
        foreach ($activities as $activity) {
            $seconds = get_string_between($activity['additional_data'], '<seconds>', '</seconds>');
            $other_lang_keys = get_string_between($activity['additional_data'], '<lang>', '</lang>');
            $_additional_data = $activity['additional_data'];
            if ($seconds != '') {
                $_additional_data = str_replace('<seconds>' . $seconds . '</seconds>', seconds_to_time_format($seconds), $_additional_data);
            }
            if ($other_lang_keys != '') {
                $_additional_data = str_replace('<lang>' . $other_lang_keys . '</lang>', _l($other_lang_keys), $_additional_data);
            }
            if (strpos($_additional_data, 'opportunity_status_') !== false) {
                $_additional_data = get_opportunity_status_by_id(strafter($_additional_data, 'opportunity_status_'));
            }
            $activities[$i]['description'] = _l($activities[$i]['description_key']);
            $activities[$i]['additional_data'] = $_additional_data;
            $activities[$i]['opportunity_name'] = get_opportunity_name_by_id($activity['opportunity_id']);
            unset($activities[$i]['description_key']);
            $i++;
        }

        return $activities;
    }

    public function log_activity($opportunity_id, $description_key, $additional_data = '', $visible_to_customer = 1)
    {
        if (!DEFINED('CRON')) {
            if (is_client_logged_in()) {
                $data['contact_id'] = get_contact_user_id();
                $data['staff_id'] = 0;
                $data['fullname'] = get_contact_full_name(get_contact_user_id());
            } elseif (is_staff_logged_in()) {
                $data['contact_id'] = 0;
                $data['staff_id'] = get_staff_user_id();
                $data['fullname'] = get_staff_full_name(get_staff_user_id());
            }
        } else {
            $data['contact_id'] = 0;
            $data['staff_id'] = 0;
            $data['fullname'] = '[CRON]';
        }
        $data['description_key'] = $description_key;
        $data['additional_data'] = $additional_data;
        $data['visible_to_customer'] = $visible_to_customer;
        $data['opportunity_id'] = $opportunity_id;
        $data['dateadded'] = date('Y-m-d H:i:s');

        $data = do_action('before_log_opportunity_activity', $data);

        $this->db->insert('tblopportunityactivity', $data);
    }

    public function new_opportunity_file_notification($file_id, $opportunity_id)
    {
        $file = $this->get_file($file_id);

        $additional_data = $file->file_name;
        $this->log_activity($opportunity_id, 'opportunity_activity_uploaded_file', $additional_data, $file->visible_to_customer);

        $members = $this->get_opportunity_members($opportunity_id);
        $notification_data = [
            'description' => 'not_opportunity_file_uploaded',
            'link' => 'opportunities/view/' . $opportunity_id . '?group=opportunity_files&file_id=' . $file_id,
        ];

        if (is_client_logged_in()) {
            $notification_data['fromclientid'] = get_contact_user_id();
        } else {
            $notification_data['fromuserid'] = get_staff_user_id();
        }

        $notifiedUsers = [];
        foreach ($members as $member) {
            if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                continue;
            }
            $notification_data['touserid'] = $member['staff_id'];
            if (add_notification($notification_data)) {
                array_push($notifiedUsers, $member['staff_id']);
            }
        }
        pusher_trigger_notification($notifiedUsers);

        $this->send_opportunity_email_template(
            $opportunity_id,
            'new-opportunity-file-uploaded-to-staff',
            'new-opportunity-file-uploaded-to-customer',
            $file->visible_to_customer,
            [
                'staff' => ['discussion_id' => $file_id, 'discussion_type' => 'file'],
                'customers' => ['customer_template' => true, 'discussion_id' => $file_id, 'discussion_type' => 'file'],
            ]
        );
    }

    public function add_external_file($data)
    {
        $insert['dateadded'] = date('Y-m-d H:i:s');
        $insert['opportunity_id'] = $data['opportunity_id'];
        $insert['external'] = $data['external'];
        $insert['visible_to_customer'] = $data['visible_to_customer'];
        $insert['file_name'] = $data['files'][0]['name'];
        $insert['subject'] = $data['files'][0]['name'];
        $insert['external_link'] = $data['files'][0]['link'];

        $path_parts = pathinfo($data['files'][0]['name']);
        $insert['filetype'] = get_mime_by_extension('.' . $path_parts['extension']);

        if (isset($data['files'][0]['thumbnailLink'])) {
            $insert['thumbnail_link'] = $data['files'][0]['thumbnailLink'];
        }

        if (isset($data['staffid'])) {
            $insert['staffid'] = $data['staffid'];
        } elseif (isset($data['contact_id'])) {
            $insert['contact_id'] = $data['contact_id'];
        }

        $this->db->insert('tblopportunityfiles', $insert);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->new_opportunity_file_notification($insert_id, $data['opportunity_id']);

            return $insert_id;
        }

        return false;
    }

    public function send_opportunity_email_template($opportunity_id, $staff_template, $customer_template, $action_visible_to_customer, $additional_data = [])
    {
        if (count($additional_data) == 0) {
            $additional_data['customers'] = [];
            $additional_data['staff'] = [];
        } elseif (count($additional_data) == 1) {
            if (!isset($additional_data['staff'])) {
                $additional_data['staff'] = [];
            } else {
                $additional_data['customers'] = [];
            }
        }

        $opportunity = $this->get($opportunity_id);
        $members = $this->get_opportunity_members($opportunity_id);

        $this->load->model('emails_model');
        foreach ($members as $member) {
            if (is_staff_logged_in() && $member['staff_id'] == get_staff_user_id()) {
                continue;
            }
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($opportunity->clientid));
            $merge_fields = array_merge($merge_fields, get_staff_merge_fields($member['staff_id']));
            $merge_fields = array_merge($merge_fields, get_opportunity_merge_fields($opportunity->id, $additional_data['staff']));
            $this->emails_model->send_email_template($staff_template, $member['email'], $merge_fields);
        }
    }

    private function _get_opportunity_billing_data($id)
    {
        $this->db->select('billing_type,opportunity_rate_per_hour');
        $this->db->where('id', $id);

        return $this->db->get('tblopportunities')->row();
    }

    public function total_logged_time_by_billing_type($id, $conditions = [])
    {
        $opportunity_data = $this->_get_opportunity_billing_data($id);
        $data = [];
        if ($opportunity_data->billing_type == 2) {
            $seconds = $this->total_logged_time($id);
            $data = $this->opportunities_model->calculate_total_by_opportunity_hourly_rate($seconds, $opportunity_data->opportunity_rate_per_hour);
            $data['logged_time'] = $data['hours'];
        } elseif ($opportunity_data->billing_type == 3) {
            $data = $this->_get_data_total_logged_time($id);
        }

        return $data;
    }

    public function data_billable_time($id)
    {
        return $this->_get_data_total_logged_time($id, [
            'billable' => 1,
        ]);
    }

    public function data_billed_time($id)
    {
        return $this->_get_data_total_logged_time($id, [
            'billable' => 1,
            'billed' => 1,
        ]);
    }

    public function data_unbilled_time($id)
    {
        return $this->_get_data_total_logged_time($id, [
            'billable' => 1,
            'billed' => 0,
        ]);
    }

    private function _delete_discussion_comments($id, $type)
    {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments = $this->db->get('tblopportunitydiscussioncomments')->result_array();
        foreach ($comments as $comment) {
            $this->delete_discussion_comment_attachment($comment['file_name'], $id);
        }
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $this->db->delete('tblopportunitydiscussioncomments');
    }

    private function _get_data_total_logged_time($id, $conditions = [])
    {
        $opportunity_data = $this->_get_opportunity_billing_data($id);
        $tasks = $this->get_tasks($id, $conditions);

        if ($opportunity_data->billing_type == 3) {
            $data = $this->calculate_total_by_task_hourly_rate($tasks);
            $data['logged_time'] = seconds_to_time_format($data['total_seconds']);
        } elseif ($opportunity_data->billing_type == 2) {
            $seconds = 0;
            foreach ($tasks as $task) {
                $seconds += $task['total_logged_time'];
            }
            $data = $this->calculate_total_by_opportunity_hourly_rate($seconds, $opportunity_data->opportunity_rate_per_hour);
            $data['logged_time'] = $data['hours'];
        }

        return $data;
    }

    private function _update_discussion_last_activity($id, $type)
    {
        if ($type == 'file') {
            $table = 'tblopportunityfiles';
        } elseif ($type == 'regular') {
            $table = 'tblopportunitydiscussions';
        }
        $this->db->where('id', $id);
        $this->db->update($table, [
            'last_activity' => date('Y-m-d H:i:s'),
        ]);
    }
}
