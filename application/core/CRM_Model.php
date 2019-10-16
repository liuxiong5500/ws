<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class CRM_Model
 * @property  CI_DB_mysqli_driver $db
 * @description
 * @version 1.0.0
 */
class CRM_Model extends CI_Model
{


    public function __construct()
    {
        parent::__construct();
        $this->db->reconnect();
        $timezone = get_option('default_timezone');
        if ($timezone != '') {
            date_default_timezone_set($timezone);
        }
    }
}
