<?php

namespace RoleExpire\Module\Actions;

use ExpressionEngine\Service\Addon\Controllers\Action\AbstractRoute;

class MemberNotificationAction extends AbstractRoute
{
    /**
     * @return void
     */
    public function process()
    {
        $roles = ee('Model')
            ->get('ee:Role')
            ->filter('role_id', '!=', 1);

        if($roles->count() >= 1) {
            foreach ($roles->all() as $role)
            {
                $settings = ee('role_expire:RolesService')->getSettings($role->role_id);
                $ttl = $settings->ttl != 'custom' ? $settings->ttl  : $settings->ttl_custom;
                if($settings->enabled() && $settings->notifyEnabled() && $ttl) {
                    $member_ids = ee('role_expire:RolesService')
                        ->getExpiringMemberIds($settings->role_id, $ttl, $settings->notify_ttl);
                    if($member_ids) {
                        $members = ee('role_expire:RolesService')->getMembers($member_ids);

                        print_r($members);
                        exit;
                    }
                }
            }
        }

        exit;
    }
}
