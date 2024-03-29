<?php

namespace Mithra62\RoleExpire\Services;

use CI_DB_result;
use ExpressionEngine\Model\Member\Member as MemberModel;
use ExpressionEngine\Model\Role\Role as RoleModel;
use ExpressionEngine\Service\Model\Collection;
use ExpressionEngine\Service\Model\Query\Builder;
use Mithra62\RoleExpire\Model\RoleExpire as RoleExpireModel;

class RolesService
{
    /**
     * @var array
     */
    protected array $settings = [];

    /**
     * @var array|string[]
     */
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
        '5184000' => '60 Days',
        '15552000' => '6 Months',
        '31104000' => '1 Year',
        'custom' => 'Custom',
    ];

    /**
     * @var array|string[]
     */
    protected array $email_time_options = [
        '86400' => '1 Day',
        '172800' => '2 Days',
        '259200' => '3 Days',
        '345600' => '4 Days',
        '432000' => '5 Days',
        '518400' => '6 Days',
        '604800' => '7 Days',
        '1209600' => '14 Days',
        '2592000' => '30 Days',
    ];

    /**
     * @var array|string[]
     */
    protected array $email_format_options = [
        'html' => 'HTML',
        'text' => 'Text',
    ];

    /**
     * @return array|string[]
     */
    public function getTtlOptions(): array
    {
        return $this->ttl_options;
    }

    /**
     * @return array|string[]
     */
    public function getEmailFormatOptions(): array
    {
        return $this->email_format_options;
    }

    /**
     * @return array|string[]
     */
    public function getEmailTimeOptions(): array
    {
        return $this->email_time_options;
    }

    /**
     * @param RoleModel $role
     * @return string
     */
    public function checkTtl(RoleModel $role): string
    {
        $settings = $this->getSetting($role->role_id, 'ttl');
        if ($settings) {
            $settings = array_key_exists($settings, $this->ttl_options) ? $this->ttl_options[$settings] : $settings . ' (custom)';
            return $settings;
        }

        return lang('re.role.none');
    }

    /**
     * @param RoleModel $role
     * @return string
     */
    public function getStatusCss(RoleModel $role): string
    {
        $status_class = 'st-pending';
        if ($this->getSetting($role->role_id, 'enabled') === 1) {
            $status_class = 'st-open';
        }

        return $status_class;

    }

    /**
     * @param RoleModel $role
     * @return string
     */
    public function getEnabled(RoleModel $role): string
    {
        $settings = $this->getSetting($role->role_id, 'enabled');
        if ($settings === 1) {
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
        if ($settings instanceof RoleExpireModel) {
            $arr = $settings->toArray();
            if (array_key_exists($key, $arr)) {
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

            if ($settings->count() == 0) {
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
        $settings->expired_role = 0;
        $settings->notify_enabled = 0;
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
    public function processMemberRoleCheck(MemberModel $member): void
    {
        $roles = $member->Roles;
        $found = false;
        if ($roles instanceof Collection) {
            foreach ($roles as $role) {
                $found = $this->processRoleCheck($role->role_id, $member);
            }
        }

        if (!$found && $member->PrimaryRole->role_id) {
            $this->processRoleCheck($member->PrimaryRole->role_id, $member);
        }
    }

    /**
     * @param int $role_id
     * @param MemberModel $member
     * @return bool
     */
    protected function processRoleCheck(int $role_id, MemberModel $member): bool
    {
        $found = false;
        $expire_data = ee('Model')
            ->get('role_expire:Settings')
            ->filter('role_id', $role_id);

        if ($expire_data->count() == 1) {
            $settings = $expire_data->first();
            $ttl = $settings->ttl != 'custom' ? $settings->ttl : $settings->ttl_custom;
            if ($settings->enabled() &&
                $ttl != '0'
            ) {
                $found = true;
                $join_date = $this->getActivatedDate($member);
                $expire_date = $join_date + $ttl;
                if (time() >= $expire_date) {
                    $this->updateRole($member, $role_id, $settings->expired_role);
                }
            }
        }

        return $found;
    }

    /**
     * @param MemberModel $member
     * @return int
     */
    protected function getJoinDate(MemberModel $member): int
    {
        $join_data = ee('Model')
            ->get('role_expire:Member')
            ->filter('member_id', $member->member_id);

        if ($join_data->count() == 0) {
            $this->createJoinData($member);
            $join_data = ee('Model')
                ->get('role_expire:Member')
                ->filter('member_id', $member->member_id);
        }

        return $join_data->first()->date_registered;
    }

    /**
     * @param MemberModel $member
     * @return int
     */
    protected function getActivatedDate(MemberModel $member): int
    {
        $join_data = ee('Model')
            ->get('role_expire:Member')
            ->filter('member_id', $member->member_id);

        if ($join_data->count() == 0) {
            $this->createJoinData($member);
            $join_data = ee('Model')
                ->get('role_expire:Member')
                ->filter('member_id', $member->member_id);
        }

        return $join_data->first()->date_activated;
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
            'date_activated' => ee()->localize->now,
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
    protected function updateRole(MemberModel $member, $from, $to): void
    {
        ee()->db->delete('members_roles', ['role_id' => $from, 'member_id' => $member->member_id]);
        if ($member->PrimaryRole->role_id == $from) {
            ee()->db->update('members', ['role_id' => $to], ['member_id' => $member->member_id]);
        } else {
            ee()->db->insert('members_roles', ['role_id' => $to, 'member_id' => $member->member_id]);
        }

        //remove logged member setup
        ee()->db->delete('role_expire_members', ['member_id' => $member->member_id]);
    }

    /**
     * @param int $role_id
     * @param int $notify_ttl
     * @return array
     */
    public function getExpiringMembers(int $role_id, int $ttl, int $notify_ttl): array
    {
        $member_ids = $return = [];
        $query = ee()->db->select('member_id')->from('members')->where(['role_id' => $role_id])->get(); //primary
        if ($query instanceof CI_DB_result && $query->num_rows() >= 1) {
            foreach ($query->result_array() as $row) {
                $member_ids[$row['member_id']] = $row['member_id'];
            }
        }

        $query = ee()->db->select('member_id')->from('members_roles')->where(['role_id' => $role_id])->get(); //secondary
        foreach ($query->result_array() as $row) {
            $member_ids[$row['member_id']] = $row['member_id'];
        }

        if ($member_ids) {
            $date = time() + ($ttl - $notify_ttl);
            $join_data = ee('Model')
                ->get('role_expire:Member')
                ->filter('member_id', 'IN', $member_ids)
                ->filter('date_activated', '<=', $date);

            if ($join_data->count() >= 1) {
                foreach ($join_data->all() as $member) {
                    $return[$member->member_id] = $member->Member->toArray();
                    $return[$member->member_id]['activated_date'] = $member->date_activated;
                    $return[$member->member_id]['date_registered'] = $member->date_registered;
                    $return[$member->member_id]['member_expiration'] = $member->date_activated + $ttl;
                }
            }
        }

        return $return;
    }

    /**
     * @return Builder
     */
    public function getUsableRoles(): Builder
    {
        $settings = ee('Model')
            ->get('role_expire:Settings')
            ->filter('enabled', 1)
            ->filter('expired_role', '!=', 0);

        $avoid = [];
        if ($settings instanceof Builder && $settings->count() >= 1) {
            foreach ($settings->all() as $setting) {
                $avoid[] = $setting->expired_role;
            }
        }

        $roles = ee('Model')
            ->get('ee:Role')
            ->filter('role_id', '>=', 5);

        if ($avoid) {
            $roles->filter('role_id', 'NOT IN', $avoid);
        }

        return $roles;
    }
}