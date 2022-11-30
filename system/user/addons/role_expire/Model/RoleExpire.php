<?php

namespace RoleExpire\Model;

use ExpressionEngine\Service\Model\Model;

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

    public function enabled()
    {
        return $this->enabled === 1;
    }

}
