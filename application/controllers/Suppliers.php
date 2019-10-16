<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Suppliers
 * @property Purchase_orders_model $purchase_orders_model
 * @property Process_master_model $process_master_model
 * @description
 * @version 1.0.0
 */
class Suppliers extends Suppliers_controller
{
    public function __construct()
    {
        parent::__construct();
        do_action('after_suppliers_area_init', $this);
        $this->load->model([ 'purchase_orders_model', 'production_logs_model', 'process_master_model' ]);
    }

    public function order($id = '')
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }
        if($id == ''){
            redirect(site_url('suppliers'));
        }
        $order = $this->purchase_orders_model->get($id);
        $data['order'] = $order;
        $data['title'] = $order->order_number;
        $this->data = $data;
        $this->view = 'order';
        $this->layout();
    }

    public function item_logs($item_id = '', $process_item_id = 0)
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }
        if($item_id == ''){
            redirect(site_url('suppliers'));
        }
        $logs = $this->production_logs_model->get($item_id);
        $data['process_list'] = array_map(function($process){
            return [
                'id' => $process->id,
                'name' => $process->name
            ];
        }, $this->process_master_model->get_item_process_sub_list($process_item_id));
        $data['logs'] = $logs;
        $data['item_id'] = $item_id;
        $data['process_item_id'] = $process_item_id;
        $data['title'] = _l('order_item_logs');
        $process_count = $this->production_logs_model->get_process_sub_count($item_id);
        $data['process_count'] = array_combine(array_column($process_count, 'process_sub_id'), $process_count);
        $this->data = $data;
        $this->view = 'item_logs';
        $this->layout();
    }

    public function add_item()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }
        if($this->input->post()){
            $data = $this->input->post();
            if(isset($data['process_item_id'])){
                $process_item_id = $data['process_item_id'];
                unset($data['process_item_id']);
            }
            $this->production_logs_model->add($data);
            redirect(site_url('suppliers/item_logs/' . $data['item_id'] . '/' . $process_item_id));
        }else{
            redirect(site_url('suppliers'));
        }
    }

    public function order_status($id = '', $status = '')
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }
        if($id == '' || $status == ''){
            redirect(site_url('suppliers'));
        }
        $this->purchase_orders_model->update_status($id, $status);
        redirect(site_url('suppliers/order/' . $id));
    }

    public function index()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }
        $data['is_home'] = true;
        $where = 'supplier = ' . get_supplier_id();
        $where .= ' AND status>1';
        $orders = $this->purchase_orders_model->get('', $where);
        $data['orders'] = $orders;
        $data['title'] = get_supplier_company_name(get_supplier_id());
        $this->data = $data;
        $this->view = 'home';
        $this->layout();
    }

    public function company()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }

        if($this->input->post()){
            if(get_option('company_is_required') == 1){
                $this->form_validation->set_rules('company', _l('clients_company'), 'required');
            }

            if(active_clients_theme() == 'perfex'){
                // Fix for custom fields checkboxes validation
                $this->form_validation->set_rules('company_form', '', 'required');
            }

            $custom_fields = get_custom_fields('customers', [
                'show_on_client_portal' => 1,
                'required' => 1,
                'disalow_client_to_edit' => 0,
            ]);

            foreach($custom_fields as $field){
                $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
                if($field['type'] == 'checkbox' || $field['type'] == 'multiselect'){
                    $field_name .= '[]';
                }
                $this->form_validation->set_rules($field_name, $field['name'], 'required');
            }
            if($this->form_validation->run() !== false){
                $data = $this->input->post();

                if(isset($data['company_form'])){
                    unset($data['company_form']);
                }

                $success = $this->suppliers_model->update_company_details($data, get_supplier_id());
                if($success == true){
                    set_alert('success', _l('clients_profile_updated'));
                }
                redirect(site_url('suppliers/company'));
            }
        }
        $data['title'] = _l('client_company_info');
        $this->data = $data;
        $this->view = 'company_profile';
        $this->layout();
    }

    public function profile()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }
        if($this->input->post('profile')){
            $this->form_validation->set_rules('firstname', _l('client_firstname'), 'required');
            $this->form_validation->set_rules('lastname', _l('client_lastname'), 'required');

            $this->form_validation->set_message('contact_email_profile_unique', _l('form_validation_is_unique'));
            $this->form_validation->set_rules('email', _l('clients_email'), 'required|valid_email|callback_contact_email_profile_unique');

            $custom_fields = get_custom_fields('contacts', [
                'show_on_client_portal' => 1,
                'required' => 1,
                'disalow_client_to_edit' => 0,
            ]);
            foreach($custom_fields as $field){
                $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
                if($field['type'] == 'checkbox' || $field['type'] == 'multiselect'){
                    $field_name .= '[]';
                }
                $this->form_validation->set_rules($field_name, $field['name'], 'required');
            }
            if($this->form_validation->run() !== false){
                handle_supplier_contact_profile_image_upload();
                $data = $this->input->post();
                // Unset the form indicator so we wont send it to the model
                unset($data['profile']);

                // For all cases
                if(isset($data['password'])){
                    unset($data['password']);
                }
                $success = $this->suppliers_model->update_contact($data, get_supplier_id(), true);

                if($success == true){
                    set_alert('success', _l('clients_profile_updated'));
                }
                redirect(site_url('suppliers/profile'));
            }
        }elseif($this->input->post('change_password')){
            $this->form_validation->set_rules('oldpassword', _l('clients_edit_profile_old_password'), 'required');
            $this->form_validation->set_rules('newpassword', _l('clients_edit_profile_new_password'), 'required');
            $this->form_validation->set_rules('newpasswordr', _l('clients_edit_profile_new_password_repeat'), 'required|matches[newpassword]');
            if($this->form_validation->run() !== false){
                $success = $this->suppliers_model->change_contact_password($this->input->post(null, false));
                if(is_array($success) && isset($success['old_password_not_match'])){
                    set_alert('danger', _l('client_old_password_incorrect'));
                }elseif($success == true){
                    set_alert('success', _l('client_password_changed'));
                }
                redirect(site_url('suppliers/profile'));
            }
        }
        $data['title'] = _l('clients_profile_heading');
        $this->data = $data;
        $this->view = 'profile';
        $this->layout();
    }

    public function remove_profile_image()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }
        do_action('before_remove_supplier_contact_profile_image');
        if(file_exists(get_upload_path_by_type('supplier_contact_profile_images') . get_supplier_contact_user_id())){
            delete_dir(get_upload_path_by_type('supplier_contact_profile_images') . get_supplier_contact_user_id());
        }
        $this->db->where('id', get_supplier_contact_user_id());
        $this->db->update('tblsuppliercontacts', [
            'profile_image' => null,
        ]);
        if($this->db->affected_rows() > 0){
            redirect(site_url('suppliers/profile'));
        }
    }

    public function register()
    {
        if(get_option('allow_registration') != 1 || is_supplier_logged_in()){
            redirect(site_url());
        }
        if(get_option('company_is_required') == 1){
            $this->form_validation->set_rules('company', _l('client_company'), 'required');
        }

        if(is_gdpr() && get_option('gdpr_enable_terms_and_conditions') == 1){
            $this->form_validation->set_rules(
                'accept_terms_and_conditions',
                _l('terms_and_conditions'),
                'required',
                [ 'required' => _l('terms_and_conditions_validation') ]
            );
        }

        $this->form_validation->set_rules('firstname', _l('client_firstname'), 'required');
        $this->form_validation->set_rules('lastname', _l('client_lastname'), 'required');
        $this->form_validation->set_rules('email', _l('client_email'), 'trim|required|is_unique[tblcontacts.email]|valid_email');
        $this->form_validation->set_rules('password', _l('clients_register_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('clients_register_password_repeat'), 'required|matches[password]');

        if(get_option('use_recaptcha_customers_area') == 1 && get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != ''){
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }

        $custom_fields = get_custom_fields('customers', [
            'show_on_client_portal' => 1,
            'required' => 1,
        ]);

        $custom_fields_contacts = get_custom_fields('contacts', [
            'show_on_client_portal' => 1,
            'required' => 1,
        ]);

        foreach($custom_fields as $field){
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if($field['type'] == 'checkbox' || $field['type'] == 'multiselect'){
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }
        foreach($custom_fields_contacts as $field){
            $field_name = 'custom_fields[' . $field['fieldto'] . '][' . $field['id'] . ']';
            if($field['type'] == 'checkbox' || $field['type'] == 'multiselect'){
                $field_name .= '[]';
            }
            $this->form_validation->set_rules($field_name, $field['name'], 'required');
        }
        if($this->input->post()){
            if($this->form_validation->run() !== false){
                $data = $this->input->post();
                // Unset recaptchafield
                if(isset($data['g-recaptcha-response'])){
                    unset($data['g-recaptcha-response']);
                }

                // Auto add billing details
                $data['billing_street'] = $data['address'];
                $data['billing_city'] = $data['city'];
                $data['billing_state'] = $data['state'];
                $data['billing_zip'] = $data['zip'];
                $data['billing_country'] = is_numeric($data['country']) ? $data['country'] : 0;
                if(isset($data['accept_terms_and_conditions'])){
                    unset($data['accept_terms_and_conditions']);
                }
                $clientid = $this->clients_model->add($data, true);
                if($clientid){
                    do_action('after_client_register', $clientid);

                    if(get_option('customers_register_require_confirmation') == '1'){
                        send_customer_registered_email_to_administrators($clientid);

                        $this->clients_model->require_confirmation($clientid);
                        set_alert('success', _l('customer_register_account_confirmation_approval_notice'));
                        redirect(site_url('suppliers/login'));
                    }

                    $this->load->model('authentication_model');
                    $logged_in = $this->authentication_model->login($this->input->post('email'), $this->input->post('password', false), false, false);

                    $redUrl = site_url();

                    if($logged_in){
                        do_action('after_client_register_logged_in', $clientid);
                        set_alert('success', _l('clients_successfully_registered'));
                    }else{
                        set_alert('warning', _l('clients_account_created_but_not_logged_in'));
                        $redUrl = site_url('suppliers/login');
                    }

                    send_customer_registered_email_to_administrators($clientid);
                    redirect($redUrl);
                }
            }
        }

        $data['title'] = _l('clients_register_heading');
        $data['bodyclass'] = 'register';
        $this->data = $data;
        $this->view = 'register';
        $this->layout();
    }

    public function forgot_password()
    {
        if(is_supplier_logged_in()){
            redirect(site_url('suppliers'));
        }

        $this->form_validation->set_rules('email', _l('customer_forgot_password_email'), 'trim|required|valid_email|callback_contact_email_exists');

        if($this->input->post()){
            if($this->form_validation->run() !== false){
                $this->load->model('suppliers_authentication_model');
                $success = $this->suppliers_authentication_model->forgot_password($this->input->post('email'));
                if(is_array($success) && isset($success['memberinactive'])){
                    set_alert('danger', _l('inactive_account'));
                }elseif($success == true){
                    set_alert('success', _l('check_email_for_resetting_password'));
                }else{
                    set_alert('danger', _l('error_setting_new_password_key'));
                }
                redirect(site_url('suppliers/forgot_password'));
            }
        }
        $data['title'] = _l('customer_forgot_password');
        $this->data = $data;
        $this->view = 'forgot_password';

        $this->layout();
    }

    public function reset_password($staff, $userid, $new_pass_key)
    {
        $this->load->model('Authentication_model');
        if(!$this->Authentication_model->can_reset_password($staff, $userid, $new_pass_key)){
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(site_url('suppliers/login'));
        }

        $this->form_validation->set_rules('password', _l('customer_reset_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('customer_reset_password_repeat'), 'required|matches[password]');
        if($this->input->post()){
            if($this->form_validation->run() !== false){
                do_action('before_user_reset_password', [
                    'staff' => $staff,
                    'userid' => $userid,
                ]);
                $success = $this->Authentication_model->reset_password(0, $userid, $new_pass_key, $this->input->post('passwordr', false));
                if(is_array($success) && $success['expired'] == true){
                    set_alert('danger', _l('password_reset_key_expired'));
                }elseif($success == true){
                    do_action('after_user_reset_password', [
                        'staff' => $staff,
                        'userid' => $userid,
                    ]);
                    set_alert('success', _l('password_reset_message'));
                }else{
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                redirect(site_url('suppliers/login'));
            }
        }
        $data['title'] = _l('admin_auth_reset_password_heading');
        $this->data = $data;
        $this->view = 'reset_password';
        $this->layout();
    }

    public function login()
    {
        if(is_supplier_logged_in()){
            redirect(site_url('suppliers'));
        }
        $this->form_validation->set_rules('password', _l('clients_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('clients_login_email'), 'trim|required|valid_email');
        if(get_option('use_recaptcha_customers_area') == 1 && get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != ''){
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }
        if($this->form_validation->run() !== false){
            $this->load->model('suppliers_authentication_model');
            $success = $this->suppliers_authentication_model->login($this->input->post('email'), $this->input->post('password', false), $this->input->post('remember'));
            if(is_array($success) && isset($success['memberinactive'])){
                set_alert('danger', _l('inactive_account'));
                redirect(site_url('suppliers/login'));
            }elseif($success == false){
                set_alert('danger', _l('client_invalid_username_or_password'));
                redirect(site_url('suppliers/login'));
            }

            maybe_redirect_to_previous_url();

            do_action('after_supplier_contact_login');
            redirect(site_url('suppliers'));
        }
        if(get_option('allow_supplier_registration') == 1){
            $data['title'] = _l('supplier_login_heading_register');
        }else{
            $data['title'] = _l('supplier_login_heading_no_register');
        }
        $data['bodyclass'] = 'customers_login';

        $this->data = $data;
        $this->view = 'login';
        $this->layout();
    }

    public function credit_card()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }

        if(!is_primary_contact(get_contact_user_id()) || $this->stripe_gateway->getSetting('allow_primary_contact_to_update_credit_card') == 0){
            redirect(site_url());
        }

        $this->load->library('stripe_subscriptions');
        $client = $this->clients_model->get(get_client_user_id());

        if($this->input->post('stripeToken')){
            try{
                $this->stripe_subscriptions->update_customer_source($client->stripe_id, $this->input->post('stripeToken'));
                set_alert('success', _l('updated_successfully', _l('credit_card')));
            }catch(Exception $e){
                set_alert('success', $e->getMessage());
            }

            redirect(site_url('clients/credit_card'));
        }

        $data['stripe_customer'] = $this->stripe_subscriptions->get_customer_with_default_source($client->stripe_id);
        $data['stripe_pk'] = $this->stripe_subscriptions->get_publishable_key();

        $data['bodyclass'] = 'customer-credit-card';
        $data['title'] = _l('credit_card');

        $this->data = $data;
        $this->view = 'credit_card';
        $this->layout();
    }

    public function subscriptions()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }

        if(!is_primary_contact(get_contact_user_id()) || get_option('show_subscriptions_in_customers_area') != '1'){
            redirect(site_url('suppliers/login'));
        }

        $this->load->model('subscriptions_model');
        $data['subscriptions'] = $this->subscriptions_model->get([ 'clientid' => get_client_user_id() ]);

        $data['title'] = _l('subscriptions');
        $data['bodyclass'] = 'subscriptions';
        $this->data = $data;
        $this->view = 'subscriptions';
        $this->layout();
    }

    public function cancel_subscription($id)
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }

        if(!is_primary_contact(get_contact_user_id()) || get_option('show_subscriptions_in_customers_area') != '1'){
            redirect(site_url());
        }

        $this->load->model('subscriptions_model');
        $this->load->library('stripe_subscriptions');
        $subscription = $this->subscriptions_model->get_by_id($id, [ 'clientid' => get_client_user_id() ]);

        if(!$subscription){
            show_404();
        }

        try{
            $type = $this->input->get('type');
            $ends_at = time();
            if($type == 'immediately'){
                $this->stripe_subscriptions->cancel($subscription->stripe_subscription_id);
            }elseif($type == 'at_period_end'){
                $ends_at = $this->stripe_subscriptions->cancel_at_end_of_billing_period($subscription->stripe_subscription_id);
            }else{
                throw new Exception('Invalid Cancelation Type', 1);
            }

            $update = [ 'ends_at' => $ends_at ];
            if($type == 'immediately'){
                $update['status'] = 'canceled';
            }
            $this->subscriptions_model->update($id, $update);

            set_alert('success', _l('subscription_canceled'));
        }catch(Exception $e){
            set_alert('danger', $e->getMessage());
        }

        redirect(site_url('clients/subscriptions'));
    }

    public function resume_subscription($id)
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('suppliers/login'));
        }

        if(!is_primary_contact(get_contact_user_id()) || get_option('show_subscriptions_in_customers_area') != '1'){
            redirect(site_url());
        }

        $this->load->model('subscriptions_model');
        $this->load->library('stripe_subscriptions');
        $subscription = $this->subscriptions_model->get_by_id($id, [ 'clientid' => get_client_user_id() ]);

        if(!$subscription){
            show_404();
        }

        try{
            $this->stripe_subscriptions->resume($subscription->stripe_subscription_id, $subscription->stripe_plan_id);
            $this->subscriptions_model->update($id, [ 'ends_at' => null ]);
            set_alert('success', _l('subscription_resumed'));
        }catch(Exception $e){
            set_alert('danger', $e->getMessage());
        }

        redirect($_SERVER['HTTP_REFERER']);
    }

    public function privacy_policy()
    {
        $data['policy'] = get_option('privacy_policy');
        $data['title'] = _l('privacy_policy') . ' - ' . get_option('companyname');
        $this->data = $data;
        $this->view = 'privacy_policy';
        $this->layout();
    }

    public function terms_and_conditions()
    {
        $data['terms'] = get_option('terms_and_conditions');
        $data['title'] = _l('terms_and_conditions') . ' - ' . get_option('companyname');
        $this->data = $data;
        $this->view = 'terms_and_conditions';
        $this->layout();
    }

    public function gdpr()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('login'));
        }

        $this->load->model('gdpr_model');

        if(is_gdpr() && $this->input->post('removal_request') && get_option('gdpr_contact_enable_right_to_be_forgotten') == '1'){
            $success = $this->gdpr_model->add_removal_request([
                'description' => nl2br($this->input->post('removal_description')),
                'request_from' => get_contact_full_name(get_contact_user_id()),
                'contact_id' => get_contact_user_id(),
                'clientid' => get_client_user_id(),
            ]);
            if($success){
                send_gdpr_email_template('gdpr-removal-request', get_contact_user_id(), 'contact');
                set_alert('success', _l('data_removal_request_sent'));
            }
            redirect(site_url('clients/gdpr'));
        }

        $data['title'] = _l('gdpr');
        $this->data = $data;
        $this->view = 'gdpr';
        $this->layout();
    }

    public function logout()
    {
        $this->load->model('suppliers_authentication_model');
        $this->suppliers_authentication_model->logout(false);
        do_action('after_supplier_logout');
        redirect(site_url('suppliers/login'));
    }

    public function contact_email_exists($email = '')
    {
        if($email == ''){
            $email = $this->input->post('email');
        }

        $this->db->where('email', $email);
        $total_rows = $this->db->count_all_results('tblsuppliercontacts');
        if($this->input->post() && $this->input->is_ajax_request()){
            if($total_rows > 0){
                echo json_encode(false);
            }else{
                echo json_encode(true);
            }
            die();
        }elseif($this->input->post()){
            if($total_rows == 0){
                $this->form_validation->set_message('contact_email_exists', _l('auth_reset_pass_email_not_found'));

                return false;
            }

            return true;
        }
    }

    public function change_language($lang = '')
    {
        if(!is_supplier_logged_in() || !is_primary_contact()){
            redirect(site_url());
        }
        $lang = do_action('before_customer_change_language', $lang);
        $this->db->where('userid', get_client_user_id());
        $this->db->update('tblclients', [ 'default_language' => $lang ]);
        if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])){
            redirect($_SERVER['HTTP_REFERER']);
        }else{
            redirect(site_url());
        }
    }

    public function export()
    {
        if(!is_supplier_logged_in()){
            redirect(site_url('login'));
        }

        if(is_gdpr() && get_option('gdpr_data_portability_contacts') == '0' || !is_gdpr()){
            show_error('This page is currently disabled, check back later.');
        }

        export_contact_data(get_contact_user_id());
    }

    /**
     * Client home chart
     * @return mixed
     */
    public function client_home_chart()
    {
        if(is_supplier_logged_in()){
            $statuses = [
                1,
                2,
                4,
                3,
            ];
            $months = [];
            $months_original = [];
            for($m = 1;$m <= 12;$m++){
                array_push($months, _l(date('F', mktime(0, 0, 0, $m, 1))));
                array_push($months_original, date('F', mktime(0, 0, 0, $m, 1)));
            }
            $chart = [
                'labels' => $months,
                'datasets' => [],
            ];
            foreach($statuses as $status){
                $this->db->select('total as amount, date');
                $this->db->from('tblinvoices');
                $this->db->where('clientid', get_client_user_id());
                $this->db->where('status', $status);
                $by_currency = $this->input->post('report_currency');
                if($by_currency){
                    $this->db->where('currency', $by_currency);
                }
                if($this->input->post('year')){
                    $this->db->where('YEAR(tblinvoices.date)', $this->input->post('year'));
                }
                $payments = $this->db->get()->result_array();
                $data = [];
                $data['temp'] = $months_original;
                $data['total'] = [];
                $i = 0;
                foreach($months_original as $month){
                    $data['temp'][$i] = [];
                    foreach($payments as $payment){
                        $_month = date('F', strtotime($payment['date']));
                        if($_month == $month){
                            $data['temp'][$i][] = $payment['amount'];
                        }
                    }
                    $data['total'][] = array_sum($data['temp'][$i]);
                    $i++;
                }

                if($status == 1){
                    $borderColor = '#fc142b';
                }elseif($status == 2){
                    $borderColor = '#84c529';
                }elseif($status == 4 || $status == 3){
                    $borderColor = '#ff6f00';
                }

                $backgroundColor = 'rgba(' . implode(',', hex2rgb($borderColor)) . ',0.3)';

                array_push($chart['datasets'], [
                    'label' => format_invoice_status($status, '', false, true),
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'borderWidth' => 1,
                    'tension' => false,
                    'data' => $data['total'],
                ]);
            }
            echo json_encode($chart);
        }
    }

    public function contact_email_profile_unique($email)
    {
        return total_rows('tblsuppliercontacts', 'id !=' . get_supplier_contact_user_id() . ' AND email="' . $email . '"') > 0 ? false : true;
    }

    public function recaptcha($str = '')
    {
        return do_recaptcha_validation($str);
    }
}
