<?php
namespace RoleExpire\Extensions;

use ExpressionEngine\Service\Addon\Controllers\Extension\AbstractRoute;

class MemberDelete extends AbstractRoute
{
    public function process(array $member_ids): array
    {

        print_r($member_ids);
        return $member_ids;

    }
}