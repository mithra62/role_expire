<?php

namespace RoleExpire\Model;

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
    protected static $_validation_rules = [
        'ttl' => 'whenTtlIs[custom]|required'
    ];

    /**
     * @return bool
     */
    public function enabled(): bool
    {
        return $this->enabled === 1;
    }

    /**
     * @return Validator
     */
    public function getValidator(): Validator
    {
        $validator = ee('Validation')->make(self::$_validation_rules);
        $data = $this->toArray();
        $validator->defineRule('whenTtlIs', function ($key, $value, $parameters, $rule) use ($data) {
            return ($data['ttl'] == $parameters[0]) ? true : $rule->skip();
        });

        return $validator;
    }

}
