<?php

use Mithra62\RoleExpire\Services\RolesService;

const ROLE_EXPIRE_VERSION = '1.0.0';

return [
    'author'            => 'mithra62',
    'author_url'        => '',
    'name'              => 'Role Expire',
    'description'       => 'Allows for Member Roles to expire after a set number of seconds from creation',
    'version'           => ROLE_EXPIRE_VERSION,
    'namespace'         => 'Mithra62\RoleExpire',
    'settings_exist'    => true,
    // Advanced settings
    'models' => [
        'Settings' => 'Model\RoleExpire',
        'Member' => 'Model\Member',
    ],
    'services.singletons' => [
        'RolesService' => function ($addon) {
            return new RolesService;
        },
    ]
];
