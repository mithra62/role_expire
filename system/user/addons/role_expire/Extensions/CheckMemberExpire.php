<?php

namespace RoleExpire\Extensions;

use ExpressionEngine\Service\Addon\Controllers\Extension\AbstractRoute;
use ExpressionEngine\Model\Member\Member;
use ExpressionEngine\Service\Model\Collection;

class CheckMemberExpire extends AbstractRoute
{
    protected $settings;

    /**
     * @param \EE_Session $session
     * @return void
     */
    public function process(\EE_Session $session)
    {
        $session = (ee()->extensions->last_call != '' ? ee()->extensions->last_call : $session);
        $member_id = $session->userdata('member_id');
        if($member_id >= '1') {
            $member = ee('Model')
                ->get('Member')
                ->filter('member_id', $member_id);

            $member = $member->first();
            if (!$member instanceof Member) {
                return $session;
            }

            $roles = $member->Roles;
            if (!$roles instanceof Collection) {
                return $session;
            }

            foreach($roles AS $role)
            {
                $expire_data = ee('Model')
                    ->get('role_expire:Settings')
                    ->filter('role_id', $role->role_id);

                if ($expire_data->count() == 1) {
                    $settings = $expire_data->first();
                    if($settings->enabled()
                        && in_array($group_id, $this->settings['member_expire_member_groups'])
                        && $this->settings['member_expire_ttl'] != '0'
                    ) {
                        $expire_date = $session->userdata('join_date')+$this->settings['member_expire_ttl'];
                        if(time() >= $expire_date) {
                            show_error(lang('member_account_expired_error'));
                            exit;
                        }
                    }
                }
            }

        }

        return $session;
    }
}
