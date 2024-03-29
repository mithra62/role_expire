<?php

namespace Mithra62\RoleExpire\Model;

use ExpressionEngine\Service\Model\Model;
use ExpressionEngine\Service\Validation\Validator;

/**
 * CartthrobTax Model
 */
class RoleExpire extends Model
{
    protected static $_primary_key = 'id';
    protected static $_table_name = 'role_expire';

    protected $id;
    protected $role_id;
    protected $ttl;
    protected $enabled;
    protected $ttl_custom;
    protected $expired_role;
    protected $notify_ttl;
    protected $notify_format;
    protected $notify_to;
    protected $notify_subject;
    protected $notify_body;
    protected $notify_enabled;

    protected static $_validation_rules = [
        'ttl' => 'required',
        'ttl_custom' => 'whenTtlIs[custom]|required|isNaturalNoZero',
        'notify_subject' => 'whenNotificationIs[1]|required',
        'notify_to' => 'whenNotificationIs[1]|required',
        'notify_body' => 'whenNotificationIs[1]|required',
    ];

    /**
     * @return bool
     */
    public function enabled(): bool
    {
        return $this->enabled === 1;
    }

    /**
     * @return bool
     */
    public function notifyEnabled(): bool
    {
        return $this->notify_enabled === 1;
    }

    /**
     * @return Validator
     */
    public function getValidator(): Validator
    {
        $validator = ee('Validation')->make(self::$_validation_rules);
        $data = $_POST;
        $validator->defineRule('whenTtlIs', function ($key, $value, $parameters, $rule) use ($data) {
            return (isset($data['ttl']) && $data['ttl'] == $parameters[0]) ? true : $rule->skip();
        });

        $validator->defineRule('whenNotificationIs', function ($key, $value, $parameters, $rule) use ($data) {
            return (isset($data['notify_enabled']) && $data['notify_enabled'] == $parameters[0]) ? true : $rule->skip();
        });

        return $validator;
    }

}
