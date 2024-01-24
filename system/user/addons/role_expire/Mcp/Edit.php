<?php

namespace Mithra62\RoleExpire\Mcp;

use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;
use Mithra62\RoleExpire\Forms\Settings as SettingsForm;

class Edit extends AbstractRoute
{
    protected $base_url = 'addons/settings/role_expire';

    public function process($id = false)
    {
        if ($id == 1) {
            ee('CP/Alert')->makeInline('shared-form')
                ->asIssue()
                ->withTitle(lang('re.error.cannot_edit_super_admin'))
                ->defer();
            ee()->functions->redirect(ee('CP/URL')->make($this->base_url . '/index'));
            exit;
        }

        $role = ee('Model')
            ->get('ee:Role')
            ->filter('role_id', $id);

        if ($role->count() == 0) {
            ee('CP/Alert')->makeInline('shared-form')
                ->asIssue()
                ->withTitle(lang('re.error.role_not_found'))
                ->defer();
            ee()->functions->redirect(ee('CP/URL')->make($this->base_url . '/index'));
            exit;
        }

        $role = $role->first();

        $form = new SettingsForm;
        $form->setRole($role);
        $vars = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = ee('role_expire:RolesService')->getSettings($id);
            $form->setData($_POST);
            $settings->set($_POST);
            $result = $form->validate($_POST);
            if ($result->isValid()) {
                $settings->save();
                ee('CP/Alert')->makeInline('shared-form')
                    ->asSuccess()
                    ->withTitle(lang('re.success.role_edited'))
                    ->defer();
                ee()->functions->redirect(ee('CP/URL')->make($this->base_url . '/index'));
                exit;
            } else {
                //$form->setData($_POST);
                $vars['errors'] = $result;

                ee('CP/Alert')->makeInline('shared-form')
                    ->asIssue()
                    ->withTitle(lang('re.error.update_role_expire'))
                    ->now();
            }
        }

        $vars = $vars + $form->generate();

        $this->addBreadcrumb($this->url('edit'), 're.header.edit_role_expire');
        $this->setBody('edit_role', $vars);
        $this->setHeading('re.header.edit_role_expire');
        return $this;
    }
}