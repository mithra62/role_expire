<?php

namespace Mithra62\RoleExpire\Extensions;

use ExpressionEngine\Model\Member\Member;
use ExpressionEngine\Service\Addon\Controllers\Extension\AbstractRoute;

class CheckMemberExpire extends AbstractRoute
{
    /**
     * @param \EE_Session $session
     * @return void
     */
    public function process(\EE_Session $session)
    {
        $session = (ee()->extensions->last_call != '' ? ee()->extensions->last_call : $session);
        $member_id = $session->userdata('member_id');
        if ($member_id >= '1') {
            $member = ee('Model')
                ->get('Member')
                ->filter('member_id', $member_id);

            $member = $member->first();
            if (!$member instanceof Member) {
                return $session;
            }

            ee('role_expire:RolesService')->processMemberRoleCheck($member);
        }

        return $session;
    }
}
