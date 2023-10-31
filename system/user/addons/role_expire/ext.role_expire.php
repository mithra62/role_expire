<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Extension;

class Role_expire_ext extends Extension
{
    protected $addon_name = 'role_expire';

    public $settings = [];
    public $version = "1.0.0";

    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    public function activate_extension()
    {

    }

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');

        return true;
    }

    public function update_extension($current = '')
    {
        return true;
    }
}
