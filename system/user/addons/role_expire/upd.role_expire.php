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
    protected string $settings_table = 'role_expire';

    /**
     * @var string
     */
    protected string $expired_members_table = 'role_expire_members';

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

        ee()->load->dbforge();
        $this->addSettingsTable();
        $this->addExpiredMembersTable();
        $this->portLegacyData();

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

        ee()->load->dbforge();
        if (ee()->db->table_exists($this->settings_table)) {
            ee()->dbforge->drop_table($this->settings_table);
        }

        if (ee()->db->table_exists($this->expired_members_table)) {
            ee()->dbforge->drop_table($this->expired_members_table);
        }

        $this->disable_extension();

        return true;
    }

    /**
     * @return void
     */
    protected function addSettingsTable(): void
    {
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
                'type' => 'varchar',
                'constraint' => 10,
                'null' => false,
                'default' => '0'
            ],
            'ttl_custom' => [
                'type' => 'int',
                'constraint' => 1,
                'null' => false,
                'default' => '0'
            ],
            'expired_role' => [
                'type' => 'int',
                'constraint' => 10,
                'null' => true,
                'default' => '0'
            ],
            'notify_time'  => [
                'type' => 'int',
                'constraint' => 10,
                'null' => true,
                'default' => '0'
            ],
            'notify_to'  => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
                'default' => '0'
            ],
            'notify_subject'  => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
                'default' => '0'
            ],
            'notify_body'  => [
                'type' => 'longtext',
                'null' => true,
            ],
            'notify_enabled'  => [
                'type' => 'int',
                'constraint' => 1,
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

    /**
     * @return void
     */
    protected function addExpiredMembersTable(): void
    {
        $fields = [
            'id' => [
                'type' => 'int',
                'constraint' => 10,
                'unsigned' => true,
                'null' => false,
                'auto_increment'=> true
            ],
            'member_id'	=> [
                'type' => 'int',
                'constraint' => 10,
                'null' => false,
                'default' => '0'
            ],
            'date_registered'	=> [
                'type' => 'int',
                'constraint' => 10,
                'null' => false,
                'default' => '0'
            ],
            'date_activated'	=> [
                'type' => 'int',
                'constraint' => 10,
                'null' => false,
                'default' => '0'
            ],
        ];

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('id', true);
        ee()->dbforge->create_table($this->expired_members_table, true);
    }

    protected function portLegacyData()
    {
        $legacy_members = ee()->db->select()->from('securitee_members')->get();
        if ($legacy_members instanceof CI_DB_mysqli_result) {
            if($legacy_members->num_rows() >= 1) {
                foreach($legacy_members->result_array() AS $row)
                {
                    ee()->db->insert($this->expired_members_table, $row);
                }
            }
        }
    }
}
