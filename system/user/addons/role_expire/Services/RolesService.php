<?php
namespace RoleExpire\Services;

use ExpressionEngine\Model\Role\Role AS RoleModel;
use ExpressionEngine\Service\Model\Collection;
use RoleExpire\Model\Member;
use RoleExpire\Model\RoleExpire AS RoleExpireModel;
use ExpressionEngine\Model\Member\Member AS MemberModel;

class RolesService
{
    protected array $settings = [];

    protected array $ttl_options = [
        '0' => 'Never',
        '1800' => '30 Minutes',
        '3600' => '1 Hour',
        '7200' => '2 Hours',
        '43200' => '12 Hours',
        '86400' => '1 Day',
        '172800' => '2 Days',
        '432000' => '5 Days',
        '604800' => '1 Week',
        '2592000' => '30 Days',
        '15552000' => '6 Months',
        '31104000' => '1 Year',
        'custom' => 'Custom'
    ];

    /**
     * @return array|string[]
     */
    public function getTtlOptions(): array
    {
        return $this->ttl_options;
    }

    /**
     * @param RoleModel $role
     * @return string
     */
    public function checkTtl(RoleModel $role): string
    {
        $settings = $this->getSetting($role->role_id, 'ttl');
        if($settings) {
            $settings = array_key_exists($settings, $this->ttl_options) ? $this->ttl_options[$settings] : $settings . ' (custom)';
            return $settings;
        }

        return lang('re.role.none');
    }

    /**
     * @param RoleModel $role
     * @return string|void
     */
    public function getStatusCss(RoleModel $role)
    {
        $status_class = 'st-pending';
        if ($this->getSetting($role->role_id, 'enabled') === 1) {
            $status_class = 'st-open';
        }

        return $status_class;

    }

    public function getEnabled(RoleModel $role)
    {
        $settings = $this->getSetting($role->role_id, 'enabled');
        if($settings === 1) {
            return lang('re.role.enabled');
        }

        return lang('re.role.disabled');
    }

    /**
     * @param int $role_id
     * @param string $key
     * @param $default
     * @return false|mixed
     */
    public function getSetting(int $role_id, string $key, $default = false)
    {
        $settings = $this->getSettings($role_id);
        if($settings instanceof RoleExpireModel) {
            $arr = $settings->toArray();
            if(array_key_exists($key, $arr)) {
                return $arr[$key];
            }
        }

        return $default;
    }

    /**
     * @param int $role_id
     * @return RoleExpireModel|null
     */
    public function getSettings(int $role_id): ?RoleExpireModel
    {
        $settings = $this->settings[$role_id] ?? null;
        if (is_null($settings)) {
            $settings = ee('Model')
                ->get('role_expire:Settings')
                ->filter('role_id', $role_id);

            if($settings->count() == 0) {
                $settings = $this->addSettings($role_id);
            } else {
                $settings = $settings->first();
            }

        }

        return $settings;
    }

    /**
     * @param $role_id
     * @return RoleExpireModel|null
     */
    protected function addSettings($role_id): ?RoleExpireModel
    {
        $settings = ee('Model')
            ->make('role_expire:Settings');
        $settings->role_id = $role_id;
        $settings->ttl = 0;
        $settings->enabled = 0;
        $result = $settings->validate();
        if ($result->isValid()) {
            return $settings->save();
        }

        return null;
    }

    /**
     * @param MemberModel $member
     * @return void
     */
    public function processMemberRoleCheck(MemberModel $member)
    {
        $roles = $member->Roles;
        if ($roles instanceof Collection) {
            foreach($roles AS $role)
            {
                $this->processRoleCheck($role->role_id, $member);
            }
        }

        if ($member->PrimaryRole->role_id) {
            $this->processRoleCheck($member->PrimaryRole->role_id, $member);
        }
    }

    /**
     * @param int $role_id
     * @param MemberModel $member
     * @return void
     */
    protected function processRoleCheck(int $role_id, MemberModel $member): void
    {
        $expire_data = ee('Model')
            ->get('role_expire:Settings')
            ->filter('role_id', $role_id);

        if ($expire_data->count() == 1) {
            $settings = $expire_data->first();
            $ttl = $settings->ttl != 'custom' ? $settings->ttl  : $settings->ttl_custom;
            if($settings->enabled() &&
                $ttl != '0'
            ) {
                $join_date = $this->getJoinDate($member);
                $expire_date = $join_date + $ttl ;
                if(time() >= $expire_date) {
                    $this->updateRole($member, $role_id, $settings->expired_role);
                }
            }
        }
    }

    /**
     * @param MemberModel $member
     * @return mixed
     */
    protected function getJoinDate(MemberModel $member)
    {
        $join_data = ee('Model')
            ->get('role_expire:Member')
            ->filter('member_id', $member->member_id);

        if($join_data->count() == 0) {
            $this->createJoinData($member);
            $join_data = ee('Model')
                ->get('role_expire:Member')
                ->filter('member_id', $member->member_id);
        }

        return $join_data->first()->date_registered;
    }

    /**
     * @param MemberModel $member
     * @return mixed
     */
    protected function createJoinData(MemberModel $member)
    {
        $join_data = ee('Model')
            ->make('role_expire:Member');

        $data = [
            'member_id' => $member->member_id,
            'date_registered' => $member->join_date,
            'date_activated' => $member->join_date,
        ];

        $join_data->set($data);
        return $join_data->save();
    }

    /**
     * @param MemberModel $member
     * @param $from
     * @param $to
     * @return void
     */
    protected function updateRole(MemberModel $member, $from, $to)
    {
        if($member->PrimaryRole->role_id == $from) {
            ee()->db->update('members', ['role_id' => $to], ['member_id'=> $member->member_id]);
        } else {
            ee()->db->delete('members_roles', ['role_id' => $from, 'member_id' => $member->member_id]);
            ee()->db->insert('members_roles', ['role_id' => $to, 'member_id' => $member->member_id]);
        }

    }
}