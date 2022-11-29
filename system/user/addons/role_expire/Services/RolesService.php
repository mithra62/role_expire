<?php
namespace RoleExpire\Services;

use ExpressionEngine\Model\Role\Role AS RoleModel;
use RoleExpire\Model\RoleExpire AS RoleExpireModel;

class RolesService
{
    protected array $settings = [];

    /**
     * @param RoleModel $role
     * @return string
     */
    public function checkTtl(RoleModel $role): string
    {
        $settings = $this->getSettings($role->role_id);

        return 're.role.none';
    }

    /**
     * @param RoleModel $role
     * @return string|void
     */
    public function getStatusCss(RoleModel $role)
    {
        if (isset($role->status)) {
            switch ($role->status) {
                case 'open':
                    $status_class = 'st-open';
                    break;
                case 'closed':
                    $status_class = 'st-error';
                    break;
                default:
                    $status_class = 'st-pending';
                    break;
            }

            return $status_class;
        }
    }

    public function getSetting($key)
    {

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
}