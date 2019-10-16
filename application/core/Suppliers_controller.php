<?php

defined('BASEPATH') or exit('No direct script access allowed');

define('SUPPLIERS_AREA', true);

class Suppliers_controller extends CRM_Controller
{
    public $template = [];

    public $data = [];

    public $use_footer = true;

    public $use_head = true;

    public $add_scripts = true;

    public $use_submenu = true;

    public $use_navigation = true;

    public function __construct()
    {
        parent::__construct();

        $language = load_supplier_language();

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<p class="text-danger alert-validation">', '</p>');

        $this->form_validation->set_message('required', _l('form_validation_required'));
        $this->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->form_validation->set_message('matches', _l('form_validation_matches'));
        $this->form_validation->set_message('is_unique', _l('form_validation_is_unique'));

        $this->load->model('suppliers_authentication_model');
        $this->suppliers_authentication_model->autologin();

        $this->load->model('tickets_model');
        $this->load->model('departments_model');
        $this->load->model('currencies_model');
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('projects_model');
        $this->load->model('announcements_model');
        $this->load->model('contracts_model');

        $this->load->model('suppliers_model');

        $_auto_loaded_vars = [
            'currencies' => $this->currencies_model->get(),
            'locale' => get_locale_key($language),
            'language' => $language,
        ];

        if (get_option('services') == 1) {
            $_auto_loaded_vars['services'] = $this->tickets_model->get_service();
        }

        $this->load->model('knowledge_base_model');

        if (is_supplier_logged_in()) {
            $contact = $this->suppliers_model->get_contact(get_supplier_contact_user_id());

            if (!$contact || $contact->active == 0) {
                $this->load->model('authentication_model');
                $this->suppliers_authentication_model->logout(true);
                redirect(site_url('suppliers'));
            }

            $_auto_loaded_vars['supplier'] = $this->suppliers_model->get(get_supplier_id());
            $_auto_loaded_vars['contact'] = $contact;
        }

        $this->load->vars($_auto_loaded_vars);
    }

    public function layout($viewFromRoot = false)
    {
        $this->data['use_navigation'] = true;
        if ($this->use_navigation == false) {
            $this->data['use_navigation'] = false;
        }

        $this->data['use_submenu'] = true;
        if ($this->use_submenu == false) {
            $this->data['use_submenu'] = false;
        }

        $this->template['head'] = '';
        if ($this->use_head == true) {
            $this->template['head'] = $this->load->view('supplier/head', $this->data, true);
        }

        if (!$viewFromRoot) {
            $this->template['view'] = $this->load->view('supplier/views/' . $this->view, $this->data, true);
        } else {
            $this->template['view'] = $this->load->view($this->view, $this->data, true);
        }

        $this->template['footer'] = '';
        if ($this->use_footer == true) {
            $this->template['footer'] = $this->load->view('supplier/footer', $this->data, true);
        }

        $this->template['scripts'] = '';
        if ($this->add_scripts == true) {
            $this->template['scripts'] = $this->load->view('supplier/scripts', $this->data, true);
        }

        $this->load->view('supplier/index', $this->template);
    }
}
