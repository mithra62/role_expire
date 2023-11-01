<?php

namespace Mithra62\RoleExpire\Module\Actions;

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
                    $members = ee('role_expire:RolesService')
                        ->getExpiringMembers($settings->role_id, $ttl, $settings->notify_ttl);

                    $vars = ee()->config->config;
                    $vars['members'] = [0 => ['no_results' => true]];
                    if($members) {
                        $vars['members'] = array_merge($members);
                        $vars['members']['0']['no_results'] = false;
                    }

                    //send email
                    if(!isset(ee()->TMPL)) {
                        ee()->load->library('Template', null, 'TMPL');
                    }

                    ee()->load->library('email');
                    ee()->email->clear();

                    ee()->email->mailtype = $settings->notify_format;
                    ee()->email->from( ee()->config->config['webmaster_email'], ee()->config->config['site_name'] );
                    ee()->email->to( $settings->notify_to );

                    $subject = ee()->TMPL->parse_variables( $settings->notify_subject, array($vars) );
                    ee()->email->subject($subject);

                    $message = ee()->TMPL->parse_variables($settings->notify_body, array($vars));
                    ee()->email->message($message);

                    ee()->email->send();
                    ee()->email->clear();
                }
            }
        }

        exit;
    }
}
