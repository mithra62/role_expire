<?php

use RoleExpire\Services\RolesService;

return [
    'author'            => 'Eric Lamb',
    'author_url'        => '',
    'name'              => 'Role Expire',
    'description'       => 'Allows for Member Roles to expire after a set number of seconds from creation',
    'version'           => '1.0.0',
    'namespace'         => 'RoleExpire',
    'settings_exist'    => true,
    // Advanced settings
    'models' => [
        'Settings' => 'Model\RoleExpire',
    ],
    'services.singletons' => [
        'RolesService' => function ($addon) {
            return new RolesService;
        },
    ]
];
