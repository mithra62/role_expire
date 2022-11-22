<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Mcp;

class Role_expire_mcp extends Mcp
{
    protected $addon_name = 'role_expire';
}
