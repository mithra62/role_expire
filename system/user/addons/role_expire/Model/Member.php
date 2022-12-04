<?php

namespace RoleExpire\Model;

use ExpressionEngine\Service\Model\Model;

class Member extends Model
{
    protected static $_primary_key = 'id';
    protected static $_table_name = 'role_expire_members';

    protected $id;
    protected $member_id;
    protected $date_registered;
    protected $date_activated;
}