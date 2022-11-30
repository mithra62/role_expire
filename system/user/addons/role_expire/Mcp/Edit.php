<?php

namespace RoleExpire\Mcp;

use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;

class Edit extends AbstractRoute
{
    protected $base_url = 'addons/settings/role_expire';

    public function process($id = false)
    {
        if($id == 1) {
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

        if($role->count() == 0) {
            ee('CP/Alert')->makeInline('shared-form')
                ->asIssue()
                ->withTitle(lang('re.error.role_not_found'))
                ->defer();
            ee()->functions->redirect(ee('CP/URL')->make($this->base_url . '/index'));
            exit;
        }

        $role = $role->first();
        //$form = ee('CP/Form');
        $form = new \ExpressionEngine\Library\CP\Form;

        $field_group = $form->getGroup('re.form.header.role_details');

        $field_set = $field_group->getFieldSet('re.form.role_name');
        $field_set->setDesc('re.form.desc.role_name');
        $field = $field_set->getField('role_name', 'html');
        $field->setContent($role->name);

        $field_set = $field_group->getFieldSet('re.form.enabled');
        $field_set->setDesc('re.form.desc.enabled');
        $field = $field_set->getField('enabled', 'select');
        $field->setValue(ee('role_expire:RolesService')->getSetting($id, 'enabled'))
            ->setChoices([
            '1' => 'Yes',
            '0' => 'No',
        ]);

        $ttl_custom = '';
        $ttl_options = ee('role_expire:RolesService')->getTtlOptions();
        $ttl = ee('role_expire:RolesService')->getSetting($id, 'ttl');
        if(!array_key_exists($ttl, $ttl_options)) {
            $ttl_custom = $ttl;
            $ttl = 'custom';
        }

        $field_set = $field_group->getFieldSet('re.form.ttl');
        $field_set->setDesc('re.form.desc.ttl');
        $field = $field_set->getField('ttl', 'select');
        $field->setValue($ttl)
            ->setChoices($ttl_options)
            ->set('group_toggle', ['custom' => 'custom']);

        $field = $field_set->getField('custom_ttl', 'text')
            ->setGroup('custom')
            ->setValue($ttl_custom);
        $field->setNote(lang('re.form.note.custom_ttl'));

        $vars = $form->toArray();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $settings = ee('role_expire:RolesService')->getSettings($id);
            if(ee()->input->post('ttl') == 'custom') {
                $_POST['ttl'] = ee()->input->post('custom_ttl');
            }

            $settings->set($_POST);
            $result = $settings->validate();
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
                    ->withTitle(lang('ct.sub.error.create_subscription'))
                    ->now();
            }
        }

        $this->addBreadcrumb($this->url('edit'), 're.header.edit_role_expire');
        $this->setBody('edit_role', $vars);
        $this->setHeading('re.header.edit_role_expire');
        return $this;
    }
}