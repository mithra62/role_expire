<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use ExpressionEngine\Service\Addon\Installer;

class Role_expire_upd extends Installer
{
    /**
     * @var string
     */
    public $has_cp_backend = 'y';

    /**
     * @var string
     */
    public $has_publish_fields = 'n';

    /**
     * @var string
     */
    protected $settings_table = 'role_expire';

    /**
     * @var string[][]
     */
    public $actions = [
        [
            'class' => 'Role_expire',
            'method' => 'ExampleAction'
        ]
    ];

    /**
     * @var string[]
     */
    public $methods = [
        [
            'method' => 'check_member_expire',
            'hook' => 'sessions_end',
            'priority' => 10,
        ]
    ];

    /**
     * @return bool
     */
    public function install()
    {
        parent::install();
        $this->activate_extension();

        $this->addSettingsTable();

        return true;
    }

    /**
     * @param $current
     * @return bool
     */
    public function update($current = '')
    {
        // Runs migrations
        parent::update($current);

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        parent::uninstall();
        if (ee()->db->table_exists($this->settings_table)) {
            ee()->load->dbforge();
            ee()->dbforge->drop_table($this->settings_table);
        }

        $this->disable_extension();

        return true;
    }

    /**
     * @return void
     */
    protected function addSettingsTable()
    {
        ee()->load->dbforge();
        $fields = [
            'id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'auto_increment'=> true
            ],
            'role_id'	=> [
                'type' => 'int',
                'constraint' => 10,
                'null' => false,
                'default' => '0'
            ],
            'ttl'  => [
                'type' => 'int',
                'constraint' => 10,
                'null' => false,
                'default' => '0'
            ],
            'enabled' => [
                'type' => 'int',
                'constraint' => 1,
                'null' => false,
                'default' => '0'
            ]
        ];

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('id', true);
        ee()->dbforge->create_table($this->settings_table, true);
    }
}
