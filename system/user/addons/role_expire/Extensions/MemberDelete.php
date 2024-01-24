<?php
namespace Mithra62\RoleExpire\Extensions;

use ExpressionEngine\Model\Member\Member;
use ExpressionEngine\Service\Addon\Controllers\Extension\AbstractRoute;

class MemberDelete extends AbstractRoute
{
    public function process(array $member_ids): array
    {
        foreach($member_ids As $member_id) {
            $member = ee('Model')
                ->get('role_expire:Member')
                ->filter('member_id', $member_id);

            if($member->count()) {
                if ($member instanceof Member) {
                    $member->delete();
                }
            }
        }

        return $member_ids;

    }
}