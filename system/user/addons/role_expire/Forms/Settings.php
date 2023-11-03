<?php
namespace Mithra62\RoleExpire\Forms;

use ExpressionEngine\Library\CP\Form\AbstractForm;
use ExpressionEngine\Model\Role\Role AS RoleModel;
use ExpressionEngine\Service\Validation\Validator;

class Settings extends AbstractForm
{
    /**
     * @var RoleModel|null
     */
    protected ?RoleModel $role = null;

    protected $rules = [
        'ttl' => 'required',
        'expired_role' => 'required',
        'ttl_custom' => 'whenTtlIs[custom]|required|isNaturalNoZero',
        'notify_subject' => 'whenNotificationIs[1]|required',
        'notify_to' => 'whenNotificationIs[1]|required',
        'notify_body' => 'whenNotificationIs[1]|required',
    ];

    /**
     * @return array
     */
    public function generate(): array
    {
        $form = new \ExpressionEngine\Library\CP\Form;
        //$form->asTab();

        $field_group = $form->getGroup('re.form.header.role_details');

        $field_set = $field_group->getFieldSet('re.form.role_name');
        $field_set->setDesc('re.form.desc.role_name');
        $field = $field_set->getField('role_name', 'html');
        $field->setContent($this->getRole()->name);

        $field_set = $field_group->getFieldSet('re.form.enabled');
        $field_set->setDesc('re.form.desc.enabled');
        $field = $field_set->getField('enabled', 'select');
        $field->setValue($this->get('enabled', 0))
            ->setChoices([
                '1' => 'Yes',
                '0' => 'No',
            ]);

        $field_set = $field_group->getFieldSet('re.form.expired_role');
        $field_set->setDesc('re.form.desc.expired_role');
        $field = $field_set->getField('expired_role', 'select');
        $field->setValue($this->get('expired_role'))
            ->setChoices($this->roleOptions());

        $options = ee('role_expire:RolesService')->getTtlOptions();
        $ttl = $this->get('ttl');
        $ttl_custom = $this->get('ttl_custom');

        $field_set = $field_group->getFieldSet('re.form.ttl');
        $field_set->setDesc('re.form.desc.ttl');
        $field = $field_set->getField('ttl', 'select');
        $field->setValue($ttl)
            ->setChoices($options)
            ->set('group_toggle', ['custom' => 'custom']);

        $field = $field_set->getField('ttl_custom', 'text')
            ->setGroup('custom')
            ->setValue($ttl_custom);
        $field->setNote(lang('re.form.note.ttl_custom'));

        //notifications
        $field_group = $form->getGroup('re.form.header.notification');

        $field_set = $field_group->getFieldSet('re.form.notify_enabled');
        $field_set->setDesc('re.form.desc.notify_enabled');
        $field = $field_set->getField('notify_enabled', 'select');
        $field->setValue($this->get('notify_enabled', 0))
            ->setChoices([
                '1' => 'Yes',
                '0' => 'No',
            ]);

        $options = ee('role_expire:RolesService')->getEmailTimeOptions();
        $field_set = $field_group->getFieldSet('re.form.notify_ttl');
        $field_set->setDesc('re.form.desc.notify_ttl');
        $field = $field_set->getField('notify_ttl', 'select');
        $field->setValue($this->get('notify_ttl', 0))
            ->setChoices($options);

        $field_set = $field_group->getFieldSet('re.form.notify_to');
        $field_set->setDesc('re.form.note.notify_to');
        $field = $field_set->getField('notify_to', 'text')
            ->setValue($this->get('notify_to'));

        $field_set = $field_group->getFieldSet('re.form.notify_subject');
        $field_set->setDesc('re.form.note.notify_subject');
        $field = $field_set->getField('notify_subject', 'text')
            ->setValue($this->get('notify_subject'));

        $options = ee('role_expire:RolesService')->getEmailFormatOptions();
        $field_set = $field_group->getFieldSet('re.form.notify_format');
        $field_set->setDesc('re.form.desc.notify_format');
        $field = $field_set->getField('notify_format', 'select');
        $field->setValue($this->get('notify_format'))
            ->setChoices($options);

        $field_set = $field_group->getFieldSet('re.form.notify_body');
        $field_set->setDesc('re.form.note.notify_body');
        $field = $field_set->getField('notify_body', 'textarea')
            ->setValue($this->get('notify_body'));

        return $form->toArray();
    }

    /**
     * @return RoleModel|null
     */
    public function getRole(): ?RoleModel
    {
        return $this->role;
    }

    /**
     * @param RoleModel|null $role
     * @return $this
     */
    public function setRole(?RoleModel $role): Settings
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string[]
     */
    protected function roleOptions(): array
    {
        $options = ['' => ' '] + parent::roleOptions();
        foreach($options AS $key => $value) {
            if ($key == $this->getRole()->role_id || $key == 1) {
                unset($options[$key]);
            }
        }

        return $options;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed|string
     */
    public function get(string $key = '', $default = '')
    {
        $value = ee()->input->post($key);
        if(!$value) {
            $value = ee('role_expire:RolesService')->getSetting($this->getRole()->role_id, $key);
            if(!$value) {
                $value = parent::get($key, $default);
            }
        }

        return $value;
    }


    /**
     * @return Validator
     */
    public function getValidator(): Validator
    {
        $validator = ee('Validation')->make($this->rules);
        $data = $this->data;
        $validator->defineRule('whenTtlIs', function ($key, $value, $parameters, $rule) use ($data) {
            return ($data['ttl'] == $parameters[0]) ? true : $rule->skip();
        });

        $validator->defineRule('whenNotificationIs', function ($key, $value, $parameters, $rule) use ($data) {
            return ($data['notify_enabled'] == $parameters[0]) ? true : $rule->skip();
        });

        return $validator;
    }
}