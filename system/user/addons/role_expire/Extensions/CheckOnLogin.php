<?php
namespace RoleExpire\Extensions;

use ExpressionEngine\Model\Member\Member;
use ExpressionEngine\Service\Addon\Controllers\Extension\AbstractRoute;
use stdClass;

class CheckOnLogin extends AbstractRoute
{
    /**
     * @param stdClass $hook_data
     * @return void
     */
    public function process(stdClass $hook_data): void
    {
        if(isset($hook_data->member_id)) {
            $member = ee('Model')
                ->get('Member')
                ->filter('member_id', $hook_data->member_id);

            $member = $member->first();
            if ($member instanceof Member) {
                ee('role_expire:RolesService')->processMemberRoleCheck($member);
            }
        }
    }
}